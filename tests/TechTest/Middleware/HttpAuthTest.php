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
use Jgut\TechTest\Middleware\HttpAuth;
use Jgut\TechTest\Model\User;
use Jgut\TechTest\Model\UserRepository;

class HttpAuthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Jgut\TechTest\Exception\Forbidden
     */
    public function testNoCredentials()
    {
        $repository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();

        $httpAuth = new HttpAuth($repository);

        call_user_func(
            $httpAuth,
            new Request([]),
            new Response(),
            function () {
            }
        );
    }

    /**
     * @expectedException \Jgut\TechTest\Exception\Forbidden
     */
    public function testNoAccess()
    {
        $repository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects(self::any())->method('findByCredentials')->will(self::returnValue([]));

        $httpAuth = new HttpAuth($repository);

        $request = new Request(['PHP_AUTH_USER' => 'UserName', 'PHP_AUTH_PW' => 'UserPassword']);

        call_user_func(
            $httpAuth,
            $request,
            new Response(),
            function () {
            }
        );
    }

    public function testAccess()
    {
        $repository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects(self::any())->method('findByCredentials')->will(self::returnValue(new User));

        $httpAuth = new HttpAuth($repository);

        $request = new Request(['PHP_AUTH_USER' => 'UserName', 'PHP_AUTH_PW' => 'UserPassword']);

        $unit = $this;

        call_user_func(
            $httpAuth,
            $request,
            new Response(),
            function ($req, $resp) use ($unit) {
                $unit::assertInstanceOf(User::class, $req->getAttribute('user'));
            }
        );
    }
}
