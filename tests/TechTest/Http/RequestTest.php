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

use Jgut\TechTest\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testBare()
    {
        $request = new Request();

        $request->setAttribute('attr', 'value');

        self::assertEquals('value', $request->getAttribute('attr'));
    }

    public function testPost()
    {
        $_POST = [
            'attr' => 'val',
        ];

        $params = [
            'SERVER_PROTOCOL' => '1.0',
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'text/html',
        ];
        $request = new Request($params);

        self::assertEquals('1.0', $request->getProtocolVersion());
        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('val', $request->getParam('attr'));
    }
}
