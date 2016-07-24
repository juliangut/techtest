<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest;

use function FastRoute\simpleDispatcher;
use Doctrine\Common\Collections\ArrayCollection;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Jgut\TechTest\Exception\BadRequest;
use Jgut\TechTest\Exception\Exception;
use Jgut\TechTest\Exception\Forbidden;
use Jgut\TechTest\Exception\NotAllowed;
use Jgut\TechTest\Exception\NotFound;
use Jgut\TechTest\Exception\Unauthorized;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;

class App
{
    use MiddlewareTrait;

    /**
     * @var ArrayCollection
     */
    protected $routes;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->routes = new ArrayCollection();
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @throws \Exception
     */
    public function run(Request $request, Response $response)
    {
        try {
            $response = $this->dispatch($request, $response);
        } catch (BadRequest $exception) {
            // 400
            $response = BadRequest::handle($exception);
        } catch (Unauthorized $exception) {
            // 401
            $response = Unauthorized::handle($exception);
        } catch (Forbidden $exception) {
            // 403
            $response = Forbidden::handle($exception);
        } catch (NotFound $exception) {
            // 404
            $response = NotFound::handle($exception);
        } catch (NotAllowed $exception) {
            // 405
            $response = NotAllowed::handle($exception);
        } catch (\Exception $exception) {
            // 500
            $response = Exception::handle(new Exception($request, $response, $exception->getMessage(), 0, $exception));
        }

        $this->respond($response);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @throws NotAllowed
     * @throws NotFound
     *
     * @return Response
     */
    protected function dispatch(Request $request, Response $response)
    {
        $routeCallbacks = function (RouteCollector $collector) {
            $this->routes->map(function (Route $route) use ($collector) {
                $collector->addRoute($route->getMethods(), $route->getPattern(), $route->getHash());
            });
        };

        $uri = '/' . ltrim($request->getUri()->getPath(), '/');

        $dispatcher = (simpleDispatcher($routeCallbacks));
        $routeInfo = $dispatcher->dispatch($request->getMethod(), $uri);

        if ($routeInfo[0] === Dispatcher::FOUND) {
            $routeArguments = [];
            foreach ($routeInfo[2] as $k => $v) {
                $routeArguments[$k] = urldecode($v);
            }

            $routeHash = $routeInfo[1];
            $route = $this->routes->filter(function (Route $route) use ($routeHash) {
                return $route->getHash() === $routeHash;
            });

            /** @var Route $route */
            $route = $route->first();
            $route->setArguments($routeArguments);

            $request->setAttribute('route', $route);

            return $this->traverseMiddleware($request, $response);
        } elseif ($routeInfo[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            $request->setAttribute('allowedMethods', $routeInfo[1]);

            throw new NotAllowed($request, $response);
        }

        throw new NotFound($request, $response);
    }

    /**
     * @param Response $response
     */
    protected function respond(Response $response)
    {
        $body = $response->getBody();
        $contentType = $response->getContentType();

        if (strpos($contentType, 'application/json') === 0) {
            $body = json_encode($body);
        } elseif (strpos($contentType, 'application/xml') === 0) {
            $xml = new \SimpleXMLElement('<?xml version="1.0"?><root></root>');
            $this->arrayToXML(json_decode(json_encode($body), true), $xml);
            $body = $xml->asXML();
        } else {
            $body = (string) $body;
        }

        $response->setHeader('Content-Length', strlen($body));

        // @codeCoverageIgnoreStart
        // Headers already sent on PHPUnit tests
        if (!headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }
        // @codeCoverageIgnoreEnd

        echo $body;
    }

    /**
     * @param array $array
     * @param \SimpleXMLElement $xmlTree
     */
    private function arrayToXML(array $array, \SimpleXMLElement $xmlTree)
    {
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item' . $key;
            }

            if (is_array($value)) {
                $xmlSubTree = $xmlTree->addChild($key);
                $this->arrayToXML($value, $xmlSubTree);
            } else {
                $xmlTree->addChild($key, htmlspecialchars($value));
            }
        }
    }

    /**
     * @param string   $pattern
     * @param callable $callback
     *
     * @return Route
     */
    public function get($pattern, callable  $callback)
    {
        return $this->map(['GET'], $pattern, $callback);
    }

    /**
     * @param string   $pattern
     * @param callable $callback
     *
     * @return Route
     */
    public function post($pattern, callable  $callback)
    {
        return $this->map(['POST'], $pattern, $callback);
    }

    /**
     * @param array    $methods
     * @param string   $pattern
     * @param callable $callback
     *
     * @return Route
     */
    public function map(array $methods, $pattern, callable $callback)
    {
        $route = new Route($methods, $pattern, $callback);

        $this->routes->add($route);

        return $route;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response)
    {
        $route = $request->getAttribute('route');

        $response = $route->run($request, $response);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Invalid return type from middleware');
        }

        return $response;
    }
}
