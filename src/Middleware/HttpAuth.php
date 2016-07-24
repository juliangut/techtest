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

use Jgut\TechTest\Exception\Forbidden;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;
use Jgut\TechTest\Model\User;
use Jgut\TechTest\Model\UserRepository;

class HttpAuth
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * SessionAuth constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param callable $next
     *
     * @throws Forbidden
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $username = $request->getUri()->getUsername();
        $password = $request->getUri()->getPassword();

        if (empty($username) || empty($password)) {
            throw new Forbidden($request, $response);
        }

        $user = $this->userRepository->findByCredentials($username, $password);

        if (!$user instanceof User) {
            throw new Forbidden($request, $response);
        }

        $request->setAttribute('user', $user);

        return $next($request, $response);
    }
}
