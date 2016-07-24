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
use Jgut\TechTest\Middleware\Session;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testDefault()
    {
        /** @var Response $response */
        $response = call_user_func(
            new Session(),
            new Request,
            new Response,
            function ($req, $resp) {
                return $resp;
            }
        );

        self::assertTrue($response->hasHeader('Set-Cookie'));
        self::assertContains('max-age=300', $response->getHeaderLine('Set-Cookie'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCustomLifetime()
    {
        /** @var Response $response */
        $response = call_user_func(
            new Session(['lifetime' => 3600]),
            new Request,
            new Response,
            function ($req, $resp) {
                return $resp;
            }
        );

        self::assertTrue($response->hasHeader('Set-Cookie'));
        self::assertContains('max-age=3600', $response->getHeaderLine('Set-Cookie'));
    }
}
