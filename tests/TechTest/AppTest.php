<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest\Tests;

use Jgut\TechTest\Exception\BadRequest;
use Jgut\TechTest\Exception\Exception;
use Jgut\TechTest\Exception\Forbidden;
use Jgut\TechTest\Exception\Unauthorized;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;
use Jgut\TechTest\App;

class AppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var App
     */
    protected $app;

    protected $request;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->app = new App;

        $params = [
            'REQUEST_METHOD' => 'GET',
            'CONTENT_TYPE' => 'text/html',
            'HTTP_HOST' => 'localhost:9000',
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/name/Julian',
        ];
        $this->request = new Request($params);
    }

    public function testNotAllowed()
    {
        $this->app->post(
            '/name/{name}',
            function (Request $request, Response $response) {
            }
        );

        ob_start();
        $this->app->run($this->request, new Response);
        $body = ob_get_contents();
        ob_end_clean();

        self::assertContains('Method not allowed', $body);
    }

    public function testNotFound()
    {
        ob_start();
        $this->app->run($this->request, new Response);
        $body = ob_get_contents();
        ob_end_clean();

        self::assertContains('Not found', $body);
    }

    public function testFound()
    {
        $unit = $this;

        $this->app->get(
            '/name/{name}',
            function (Request $request, Response $response, array $arguments = []) use ($unit) {
                $response->setContentType('application/xml');

                $response->setBody([
                    [
                        'name' => $arguments['name'],
                        'dates' => ['2016-07-22T19:58:02+02:00', '2016-07-22T19:58:02+02:00']
                    ]
                ]);

                return $response;
            }
        );

        ob_start();
        $this->app->run($this->request, new Response);
        $body = ob_get_contents();
        ob_end_clean();

        self::assertContains('<name>Julian</name>', $body);
    }

    public function testBadRequest()
    {
        $this->app->get(
            '/name/{name}',
            function (Request $request, Response $response) {
                $response->setContentType('application/json');

                throw new BadRequest($request, $response);
            }
        );

        ob_start();
        $this->app->run($this->request, new Response);
        $body = ob_get_contents();
        ob_end_clean();

        self::assertContains('Bad Request', $body);
    }

    public function testUnauthorized()
    {
        $this->app->get(
            '/name/{name}',
            function (Request $request, Response $response) {
                $response->setContentType('application/xml');

                throw new Unauthorized($request, $response);
            }
        );

        ob_start();
        $this->app->run($this->request, new Response);
        $body = ob_get_contents();
        ob_end_clean();

        self::assertContains('Unauthorized', $body);
    }

    public function testForbidden()
    {
        $this->app->get(
            '/name/{name}',
            function (Request $request, Response $response) {
                throw new Forbidden($request, $response);
            }
        );

        ob_start();
        $this->app->run($this->request, new Response);
        $body = ob_get_contents();
        ob_end_clean();

        self::assertContains('Forbidden', $body);
    }

    public function testException()
    {
        $this->app->get(
            '/name/{name}',
            function (Request $request, Response $response) {
                throw new Exception($request, $response);
            }
        );

        ob_start();
        $this->app->run($this->request, new Response);
        $body = ob_get_contents();
        ob_end_clean();

        self::assertContains('Unexpected error', $body);
    }
}
