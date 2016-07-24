<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest\Exception;

use Jgut\TechTest\Http\Request;
use Jgut\TechTest\Http\Response;

class Exception extends \Exception
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Exception constructor.
     *
     * @param Request    $request
     * @param Response   $response
     * @param string     $message
     * @param int        $code
     * @param \Exception $exception
     */
    public function __construct(
        Request $request,
        Response $response,
        $message = '',
        $code = 0,
        \Exception $exception = null
    ) {
        $this->request = $request;
        $this->response = $response;

        parent::__construct($message, $code, $exception);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Exception $exception
     *
     * @return Response
     */
    public static function handle(Exception $exception)
    {
        $response = $exception->getResponse();
        $response->setStatusCode(500);

        $error = $exception->getMessage();

        $contentType = static::determineContentType($exception->getRequest(), $response);
        switch ($contentType) {
            case 'application/json':
            case 'application/xml':
                $response->setBody(['error' => $error]);
                break;

            case 'text/html':
            default:
                $body = <<<END
<html>
    <head>
        <title>500. Unexpected error</title>
        <link href="/vendor/bootstrap/dist/css/bootstrap.min.css" media="all" rel="stylesheet" />
    </head>
    <body>
        <div class="container"><div class="row"><div class="col-xs-4 col-xs-offset-4 text-center">
            <h1 class="text-danger">500. Unexpected error</h1>
            <p>$error</p>
        </div></div></div>
    </body>
</html>
END;
                $response->setBody($body);
        }

        $response->setContentType($contentType . '; charset=UTF-8');

        return $response;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return string
     */
    protected static function determineContentType(Request $request, Response $response)
    {
        static $knownContentTypes = [
            'application/json',
            'application/xml',
            'text/html',
        ];

        $contentType = $response->getHeaderLine('Content-Type');
        if (empty($contentType)) {
            $contentType = $request->getHeader('Accept')[0];
        }

        foreach ($knownContentTypes as $availableContentType) {
            if (strpos($contentType, $availableContentType) === 0) {
                return $availableContentType;
            }
        }

        return 'text/html';
    }
}
