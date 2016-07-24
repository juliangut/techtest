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

trait MiddlewareTrait
{
    /**
     * @var \SplStack
     */
    protected $middlewareStack;

    /**
     * @param callable $callable
     *
     * @return $this
     */
    public function add(callable $callable)
    {
        $this->initializeStack();

        $top = $this->middlewareStack->top();

        $this->middlewareStack->push(function (Request $request, Response $response) use ($callable, $top) {
            $response = call_user_func($callable, $request, $response, $top);

            if (!$response instanceof Response) {
                throw new \RuntimeException('Invalid return type from middleware');
            }

            return $response;
        });

        return $this;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function traverseMiddleware(Request $request, Response $response)
    {
        $this->initializeStack();

        $initial = $this->middlewareStack->top();

        return call_user_func($initial, $request, $response);
    }

    /**
     * Initiate stack
     */
    protected function initializeStack()
    {
        if ($this->middlewareStack instanceof \SplStack) {
            return;
        }

        $this->middlewareStack = new \SplStack;
        $this->middlewareStack->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO);

        $this->middlewareStack->push($this);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response)
    {
        $response->setContentType($request->getHeaderLine('Content-Type'));

        return $response;
    }
}
