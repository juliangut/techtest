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

class Session
{
    /**
     * @var array
     */
    protected $sessionSettings;

    /**
     * SessionAuth constructor.
     *
     * @param array $sessionSettings
     */
    public function __construct(array $sessionSettings = [])
    {
        $this->sessionSettings = array_merge(
            [
                'lifetime' => 300,
                'httponly' => true,
            ],
            $sessionSettings
        );
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
        if (session_status() === PHP_SESSION_DISABLED) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('PHP sessions are disabled');
            // @codeCoverageIgnoreEnd
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Session is already started');
            // @codeCoverageIgnoreEnd
        }

        ini_set('session.gc_maxlifetime', $this->sessionSettings['lifetime']);

        // Prevent headers from being automatically sent to client
        ini_set('session.use_trans_sid', false);
        ini_set('session.use_cookies', true);
        ini_set('session.use_only_cookies', true);
        ini_set('session.use_strict_mode', false);
        ini_set('session.cache_limiter', '');

        session_start();

        /** @var Response $response */
        $response = $next($request, $response);

        if (session_status() === PHP_SESSION_ACTIVE) {
            $response->addCookie(session_name(), session_id(), ['expires' => $this->sessionSettings['lifetime']]);
        }

        return $response;
    }
}
