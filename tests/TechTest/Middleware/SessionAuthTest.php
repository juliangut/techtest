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
use Jgut\TechTest\Middleware\SessionAuth;
use Jgut\TechTest\Model\User;
use Jgut\TechTest\Model\UserRepository;

class SessionAuthTest extends \PHPUnit_Framework_TestCase
{
    public function testNoCredentials()
    {
        $repository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();

        $sessionAuth = new SessionAuth($repository);

        /** @var Response $response */
        $response = call_user_func(
            $sessionAuth,
            new Request([]),
            new Response(),
            function () {
            }
        );

        self::assertTrue($response->hasHeader('Location'));
        self::assertTrue(isset($_SESSION['redirect']));
    }

    public function testNoAccess()
    {
        $_SESSION['user'] = 'UserName';

        $repository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects(self::any())->method('findByUsername')->will(self::returnValue([]));

        $sessionAuth = new SessionAuth($repository);

        /** @var Response $response */
        $response = call_user_func(
            $sessionAuth,
            new Request([]),
            new Response(),
            function () {
            }
        );

        self::assertTrue($response->hasHeader('Location'));
        self::assertTrue(isset($_SESSION['redirect']));
        self::assertFalse(isset($_SESSION['user']));
    }

    public function testRedirect()
    {
        $_SESSION['user'] = 'UserName';
        $_SESSION['redirect'] = '/home';

        $repository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects(self::any())->method('findByUsername')->will(self::returnValue(new User));

        $sessionAuth = new SessionAuth($repository);

        /** @var Response $response */
        $response = call_user_func(
            $sessionAuth,
            new Request([]),
            new Response(),
            function () {
            }
        );

        self::assertTrue($response->hasHeader('Location'));
        self::assertEquals('/home', $response->getHeaderLine('Location'));
        self::assertFalse(isset($_SESSION['redirect']));
    }

    public function testAccess()
    {
        $_SESSION['user'] = 'UserName';

        $repository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects(self::any())->method('findByUsername')->will(self::returnValue(new User));

        $sessionAuth = new SessionAuth($repository);

        $unit = $this;

        call_user_func(
            $sessionAuth,
            new Request([]),
            new Response(),
            function ($req, $resp) use ($unit) {
                $unit::assertInstanceOf(User::class, $req->getAttribute('user'));
            }
        );
    }
}
