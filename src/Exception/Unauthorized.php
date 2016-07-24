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

class Unauthorized extends Exception
{
    /**
     * {@inheritdoc}
     */
    public static function handle(Exception $exception)
    {
        $response = $exception->getResponse();
        $response->setStatusCode(401);

        $contentType = static::determineContentType($exception->getRequest(), $response);
        switch ($contentType) {
            case 'application/json':
            case 'application/xml':
                $response->setBody(['error' => 'Unauthorized']);
                break;

            case 'text/html':
            default:
                $body = <<<END
<html>
    <head>
        <title>401. Unauthorized</title>
        <link href="/vendor/bootstrap/dist/css/bootstrap.min.css" media="all" rel="stylesheet" />
    </head>
    <body>
        <div class="container"><div class="row"><div class="col-xs-4 col-xs-offset-4 text-center">
            <h1 class="text-danger">401. Unauthorized</h1>
        </div></div></div>
    </body>
</html>
END;
                $response->setBody($body);
        }

        $response->setContentType($contentType . '; charset=UTF-8');

        return $response;
    }
}
