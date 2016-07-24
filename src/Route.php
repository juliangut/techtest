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

use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;

class Route
{
    use MiddlewareTrait;

    /**
     * @var array
     */
    protected $methods;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var callable
     */
    protected $callable;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * Route constructor.
     *
     * @param array    $methods
     * @param string   $pattern
     * @param callable $callable
     */
    public function __construct(array $methods, $pattern, callable $callable)
    {
        $this->methods = $this->filterMethods($methods);
        $this->pattern = $pattern;
        $this->callable = $callable;

        $this->hash = sha1(implode(',', $this->methods) . $pattern);
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param array $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments = [])
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function run(Request $request, Response $response)
    {
        return $this->traverseMiddleware($request, $response);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @throws \RuntimeException
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response)
    {
        $response = call_user_func($this->callable, $request, $response, $this->arguments);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Invalid return type from middleware');
        }

        return $response;
    }

    /**
     * @param array $methods
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function filterMethods(array $methods)
    {
        return array_map(
            function ($method) {
                $method = strtoupper($method);

                if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
                    throw new \InvalidArgumentException('Unsupported HTTP method');
                }

                return $method;
            },
            $methods
        );
    }
}
