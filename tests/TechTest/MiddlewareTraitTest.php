<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest\Tests;

use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;
use Jgut\TechTest\MiddlewareTrait;

class MiddlewareTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MiddlewareTrait
     */
    protected $middleware;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->middleware = $this->getMockForTrait(MiddlewareTrait::class);
    }

    public function testNoMiddleware()
    {
        $response = $this->middleware->traverseMiddleware(new Request([]), new Response());

        self::assertEmpty($response->getBody());
    }

    public function testMiddleware()
    {
        $this->middleware->add(function (Request $request, Response $response) {
            $response->setBody('Mock');

            return $response;
        });

        $response = $this->middleware->traverseMiddleware(new Request([]), new Response());

        self::assertEquals('Mock', $response->getBody());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testBadMiddleware()
    {
        $this->middleware->add(function () {
        });

        $this->middleware->traverseMiddleware(new Request([]), new Response());
    }
}
