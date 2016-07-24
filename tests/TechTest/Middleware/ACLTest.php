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
use Jgut\TechTest\Middleware\ACL;
use Jgut\TechTest\Model\User;
use Jgut\TechTest\Route;

class ACLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Acl
     */
    protected $acl;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->acl = new ACL(
            ['ROLE1', 'ROLE2'],
            ['/path1', '/path2'],
            [
                ['role' => 'ROLE1', 'resource' => '/path1'],
                ['role' => 'ROLE2', 'resource' => '/path1'],
                ['role' => 'ROLE2', 'resource' => '/path2'],
            ]
        );
    }

    public function testAccess()
    {
        $user = new User;
        $user->addRole('ROLE2');

        $route = new Route(
            ['get'],
            '/path2',
            function () {
            }
        );

        $request = new Request([]);
        $request->setAttribute('route', $route);
        $request->setAttribute('user', $user);

        $unit = $this;
        $callback = function ($req, $res) use ($unit) {
            $unit::assertTrue(true);

            return $res;
        };

        call_user_func($this->acl, $request, new Response, $callback);
    }

    /**
     * @expectedException \Jgut\TechTest\Exception\Unauthorized
     */
    public function testNoAccess()
    {
        $user = new User;
        $user->addRole('ROLE1');

        $route = new Route(
            ['get'],
            '/path2',
            function () {
            }
        );

        $request = new Request([]);
        $request->setAttribute('route', $route);
        $request->setAttribute('user', $user);

        call_user_func(
            $this->acl,
            $request,
            new Response,
            function () {
            }
        );
    }
}
