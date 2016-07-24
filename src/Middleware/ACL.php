<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest\Middleware;

use Jgut\TechTest\Exception\Unauthorized;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;
use Jgut\TechTest\Model\User;
use Jgut\TechTest\Route;
use Zend\Permissions\Acl\Acl as Control;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class ACL
{
    /**
     * @var Control
     */
    protected $acl;

    /**
     * ACL constructor.
     *
     * @param array $roles
     * @param array $resources
     * @param array $rules
     */
    public function __construct(array $roles = [], array $resources = [], array $rules = [])
    {
        $this->acl = new Control();

        foreach ($roles as $role) {
            $this->acl->addRole(new Role($role));
        }

        foreach ($resources as $resource) {
            $this->acl->addResource(new Resource($resource));
        }

        foreach ($rules as $rule) {
            $this->acl->allow(
                $rule['role'],
                $rule['resource'],
                array_key_exists('privilege', $rule) ? $rule['privilege'] : null
            );
        }
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param callable $next
     *
     * @throws Unauthorized
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        /** @var Route $route */
        $route = $request->getAttribute('route');

        /** @var User $user */
        $user = $request->getAttribute('user');

        $allowed = false;
        foreach ($user->getRoles() as $role) {
            if ($this->acl->isAllowed($role, $route->getPattern(), strtolower($request->getMethod()))) {
                $allowed = true;

                break;
            }
        }

        if (!$allowed) {
            throw new Unauthorized($request, $response);
        }

        return $next($request, $response);
    }
}
