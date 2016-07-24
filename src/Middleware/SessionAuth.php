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

use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;
use Jgut\TechTest\Model\User;
use Jgut\TechTest\Model\UserRepository;

class SessionAuth
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
     * @throws \RuntimeException
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        if (!isset($_SESSION['user'])) {
            $response->setRedirect('/login');
            $_SESSION['redirect'] = (string) $request->getUri();

            return $response;
        } else {
            $user = $this->userRepository->findByUsername($_SESSION['user']);

            if (!$user instanceof User) {
                $response->setRedirect('/login');
                unset($_SESSION['user']);
                $_SESSION['redirect'] = (string) $request->getUri();

                return $response;
            }
        }

        if (isset($_SESSION['redirect'])) {
            $response->setRedirect($_SESSION['redirect']);
            unset($_SESSION['redirect']);

            return $response;
        }

        $request->setAttribute('user', $user);

        return $next($request, $response);
    }
}
