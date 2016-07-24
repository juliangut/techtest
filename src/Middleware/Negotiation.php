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

use Jgut\TechTest\Exception\BadRequest;
use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;
use Negotiation\AcceptHeader;
use Negotiation\Negotiator;

class Negotiation
{
    /**
     * @var Negotiator
     */
    protected $negotiator;

    /**
     * Negotiation constructor.
     */
    public function __construct()
    {
        $this->negotiator = new Negotiator;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param callable $next
     *
     * @throws BadRequest
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $acceptHeader = $request->getHeaderLine('Accept');

        if ($acceptHeader === '') {
            $acceptHeader = 'application/json; charset=UTF-8';
        }

        /** @var AcceptHeader $contentType */
        $contentType = $this->negotiator->getBest(
            $acceptHeader,
            [
                'application/json; charset=UTF-8',
                'application/xml; charset=UTF-8',
                'text/html; charset=UTF-8'
            ]
        );

        $response->setContentType($contentType ? $contentType->getValue() : 'application/json; charset=UTF-8');

        return $next($request, $response);
    }
}
