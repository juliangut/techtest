<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest\Tests\Middleware;

use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;
use Jgut\TechTest\Middleware\Negotiation;

class NegotiationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefault()
    {
        $unit = $this;

        call_user_func(
            new Negotiation,
            new Request,
            new Response,
            function ($req, $resp) use ($unit) {
                $unit::assertEquals('application/json; charset=UTF-8', $resp->getContentType());
            }
        );
    }

    public function testUnknown()
    {
        $request = new Request(['HTTP_ACCEPT' => 'made/up']);

        $unit = $this;

        call_user_func(
            new Negotiation,
            $request,
            new Response(),
            function ($req, $resp) use ($unit) {
                $unit::assertEquals('application/json; charset=UTF-8', $resp->getContentType());
            }
        );
    }

    public function testBestFit()
    {
        $request = new Request(['HTTP_ACCEPT' => 'application/xml']);

        $unit = $this;

        call_user_func(
            new Negotiation,
            $request,
            new Response(),
            function ($req, $resp) use ($unit) {
                $unit::assertEquals('application/xml; charset=UTF-8', $resp->getContentType());
            }
        );
    }
}
