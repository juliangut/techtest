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

use Jgut\TechTest\Http\Headers;

class HeadersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Headers
     */
    protected $headers;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->headers = $this->getMockForAbstractClass(Headers::class);

        $reflection = new \ReflectionClass(get_class($this->headers));
        $method = $reflection->getMethod('initHeaders');
        $method->setAccessible(true);

        $method->invoke($this->headers);
    }

    public function testAccessorMutator()
    {
        self::assertEmpty($this->headers->getHeaders());

        $this->headers->setHeader('Content-Type', 'a');
        self::assertCount(1, $this->headers->getHeaders());
        self::assertEquals(['a'], $this->headers->getHeader('Content-Type'));
        self::assertEquals('a', $this->headers->getHeaderLine('Content-Type'));

        $this->headers->addHeader('Content-Type', 'b');
        self::assertEquals(['a', 'b'], $this->headers->getHeader('Content-Type'));
        self::assertEquals('a,b', $this->headers->getHeaderLine('Content-Type'));
    }

    public function testProtocolVersion()
    {
        self::assertEquals('1.1', $this->headers->getProtocolVersion());

        $this->headers->setProtocolVersion('1.0');
        self::assertEquals('1.0', $this->headers->getProtocolVersion());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongProtocolVersion()
    {
        $this->headers->setProtocolVersion('A.B');
    }
}
