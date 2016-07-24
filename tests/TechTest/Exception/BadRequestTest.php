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

use Jgut\TechTest\Exception\BadRequest;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;

class BadRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testByAccess()
    {
        $request = new Request(['HTTP_ACCEPT' => 'application/json']);

        $response = BadRequest::handle(new BadRequest($request, new Response()));

        self::assertEquals(400, $response->getStatusCode());
        self::assertEquals('application/json; charset=UTF-8', $response->getContentType());
        self::assertArrayHasKey('error', $response->getBody());
        self::assertArrayHasKey('reason', $response->getBody());
    }

    public function testByContentType()
    {
        $response = new Response();
        $response->setContentType('text/html');

        $response = BadRequest::handle(new BadRequest(new Request([]), $response));

        self::assertEquals(400, $response->getStatusCode());
        self::assertEquals('text/html; charset=UTF-8', $response->getContentType());
        self::assertContains('400', $response->getBody());
    }
}
