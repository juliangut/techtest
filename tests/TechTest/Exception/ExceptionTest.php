<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest\Tests\Exception;

use Jgut\TechTest\Exception\Exception;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testByAccess()
    {
        $request = new Request(['HTTP_ACCEPT' => 'application/xml']);

        $response = Exception::handle(new Exception($request, new Response()));

        self::assertEquals(500, $response->getStatusCode());
        self::assertEquals('application/xml; charset=UTF-8', $response->getContentType());
        self::assertArrayHasKey('error', $response->getBody());
    }

    public function testByContentType()
    {
        $response = new Response();
        $response->setContentType('text/html');

        $response = Exception::handle(new Exception(new Request([]), $response));

        self::assertEquals(500, $response->getStatusCode());
        self::assertEquals('text/html; charset=UTF-8', $response->getContentType());
        self::assertContains('500', $response->getBody());
    }

    public function testByDefault()
    {
        $response = Exception::handle(new Exception(new Request([]), new Response));

        self::assertEquals(500, $response->getStatusCode());
        self::assertEquals('text/html; charset=UTF-8', $response->getContentType());
        self::assertContains('500', $response->getBody());
    }
}
