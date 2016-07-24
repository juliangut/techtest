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
use Jgut\TechTest\Route;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadMethod()
    {
        new Route(
            ['options'],
            '/path1',
            function () {
            }
        );
    }

    public function testCreation()
    {
        $route = new Route(
            ['get', 'post'],
            '/path1',
            function () {
            }
        );

        self::assertEquals(['GET', 'POST'], $route->getMethods());
        self::assertEquals('/path1', $route->getPattern());
        self::assertNotEmpty($route->getHash());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testBadReturn()
    {
        $route = new Route(
            ['get', 'post'],
            '/path1',
            function () {
            }
        );

        $route->run(new Request([]), new Response());
    }

    public function testRun()
    {
        $unit = $this;

        $route = new Route(
            ['get', 'post'],
            '/path1',
            function ($req, $resp, $args) use ($unit) {
                $unit::assertEquals(['arg1' => 'val1'], $args);

                return $resp;
            }
        );
        $route->setArguments(['arg1' => 'val1']);

        $route->run(new Request([]), new Response());
    }
}
