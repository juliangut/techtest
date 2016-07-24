<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest\Tests\Headers;

use Jgut\TechTest\Http\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->response = new Response(200, ['Content-Type' => 'text/html']);
    }

    public function testAccessorMutator()
    {
        self::assertEquals(200, $this->response->getStatusCode());
        self::assertEquals('OK', $this->response->getReasonPhrase());
        self::assertEmpty($this->response->getBody());

        $this->response->setStatusCode(480);
        self::assertEquals(480, $this->response->getStatusCode());
        self::assertEquals('', $this->response->getReasonPhrase());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadStatusCode()
    {
        $this->response->setStatusCode(2000);
    }

    public function testCookies()
    {
        $expiration = new \DateTime('tomorrow');
        $now = new \DateTime('now');
        $diff = $expiration->format('U') - $now->format('U');

        $properties = [
            'domain' => 'http://example.com',
            'path' => '/',
            'expires' => $expiration->format('c'),
            'secure' => true,
            'httponly' => true,
        ];

        $this->response->addCookie('Cookie-One', 'My-Value');
        $this->response->addCookie('Cookie-Two', 'My-Value', $properties);

        self::assertTrue($this->response->hasHeader('Set-Cookie'));

        $cookies = $this->response->getHeader('Set-Cookie');

        self::assertNotContains('expires', $cookies[0]);
        self::assertContains('max-age=' . $diff, $cookies[1]);
    }
}
