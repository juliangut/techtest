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

use Jgut\TechTest\Exception\NotAllowed;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;

class NotAllowedTest extends \PHPUnit_Framework_TestCase
{
    public function testByAccess()
    {
        $request = new Request(['HTTP_ACCEPT' => 'application/json']);
        $request->setAttribute('allowedMethods', ['GET']);

        $response = NotAllowed::handle(new NotAllowed($request, new Response()));

        self::assertEquals(405, $response->getStatusCode());
        self::assertEquals('application/json; charset=UTF-8', $response->getContentType());
        self::assertArrayHasKey('error', $response->getBody());
        self::assertContains('GET', $response->getBody()['error']);
    }

    public function testByContentType()
    {
        $request = new Request([]);
        $request->setAttribute('allowedMethods', ['GET']);

        $response = new Response();
        $response->setContentType('text/html');

        $response = NotAllowed::handle(new NotAllowed($request, $response));

        self::assertEquals(405, $response->getStatusCode());
        self::assertEquals('text/html; charset=UTF-8', $response->getContentType());
        self::assertContains('405', $response->getBody());
        self::assertContains('GET', $response->getBody());
    }
}
