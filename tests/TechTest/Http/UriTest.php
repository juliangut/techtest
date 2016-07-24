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

use Doctrine\Common\Collections\ArrayCollection;
use Jgut\TechTest\Http\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $params = [
            'HTTPS' => 'on',
            'HTTP_AUTHORIZATION' => 'Basic UmljaGFyZDpTdGFsbG1hbg==',
            'HTTP_HOST' => 'localhost:9000',
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/page1',
        ];
        $uri = new Uri(new ArrayCollection($params));

        self::assertEquals('Richard', $uri->getUsername());
        self::assertEquals('Stallman', $uri->getPassword());
        self::assertEquals('/page1', $uri->getPath());
        self::assertEquals('https://localhost:9000/page1', (string) $uri);
    }
}
