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

use Jgut\TechTest\Exception\NotFound;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;

class NotFoundTest extends \PHPUnit_Framework_TestCase
{
    public function testByAccess()
    {
        $request = new Request(['HTTP_ACCEPT' => 'application/json']);

        $response = NotFound::handle(new NotFound($request, new Response()));

        self::assertEquals(404, $response->getStatusCode());
        self::assertEquals('application/json; charset=UTF-8', $response->getContentType());
        self::assertArrayHasKey('error', $response->getBody());
    }

    public function testByContentType()
    {
        $response = new Response();
        $response->setContentType('text/html');

        $response = NotFound::handle(new NotFound(new Request([]), $response));

        self::assertEquals(404, $response->getStatusCode());
        self::assertEquals('text/html; charset=UTF-8', $response->getContentType());
        self::assertContains('404', $response->getBody());
    }
}
