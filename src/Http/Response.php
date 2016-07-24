<?php
/**
 * TechTest (https://github.com/juliangut/techtest)
 * Technical Test
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/techtest
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Jgut\TechTest\Http;

use Doctrine\Common\Collections\ArrayCollection;

class Response extends Headers
{
    /**
     * @var array
     */
    protected static $statusReasonPhrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * @var array
     */
    protected static $cookieProperties = [
        //'domain' => '',
        'path' => '/',
        //'expires' => 0,
        'secure' => false,
        'httponly' => true,
    ];

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var mixed
     */
    protected $body;

    /**
     * Response constructor.
     *
     * @param int   $statusCode
     * @param array $headers
     */
    public function __construct($statusCode = 200, array $headers = [], $body = null)
    {
        $this->setStatusCode($statusCode);

        $this->initHeaders();
        foreach ($headers as $header => $value) {
            $this->addHeader($header, $value);
        }

        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $this->filterStatus($statusCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        if (array_key_exists($this->statusCode, static::$statusReasonPhrases)) {
            return static::$statusReasonPhrases[$this->statusCode];
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body ? $this->body : '';
    }

    /**
     * @param mixed $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setRedirect($url)
    {
        $this->setHeader('Location', (string) $url);
        $this->setStatusCode(302);

        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->hasHeader('Content-Type') ? $this->getHeaderLine('Content-Type') : 'text/html; charset=UTF-8';
    }

    /**
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        return $this->setHeader('Content-Type', $contentType);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function addCookie($name, $value, array $properties = [])
    {
        $properties = array_merge(static::$cookieProperties, $properties);

        $cookieParams = $this->composerCookieExpiration([], $properties);

        if (array_key_exists('domain', $properties)) {
            $cookieParams[] = 'domain=' . $properties['domain'];
        }

        if (array_key_exists('path', $properties)) {
            $cookieParams[] = 'path=' . $properties['path'];
        }

        if (array_key_exists('secure', $properties) && $properties['secure']) {
            $cookieParams[] = 'secure';
        }

        if (array_key_exists('httponly', $properties) && $properties['httponly']) {
            $cookieParams[] = 'httponly';
        }

        $this->addHeader(
            'Set-Cookie',
            sprintf('%s=%s; %s', urlencode($name), urlencode($value), implode('; ', $cookieParams))
        );

        return $this;
    }

    /**
     * @param array $cookieParams
     * @param array $properties
     *
     * @return array
     */
    protected function composerCookieExpiration(array $cookieParams, array $properties)
    {
        if (array_key_exists('expires', $properties) && $properties['expires'] !== 0) {
            if (is_string($properties['expires'])) {
                $expiration = new \DateTime($properties['expires']);
            } else {
                $expiration = (new \DateTime('now'))
                    ->add(new \DateInterval(sprintf('PT%sS', (int) $properties['expires'])));
            }

            $cookieParams[] = sprintf(
                'expires=%s; max-age=%s',
                $expiration->format('D, d-M-Y H:i:s e'),
                (int) $expiration->format('U') - (int) (new \DateTime('now'))->format('U')
            );
        }

        return $cookieParams;
    }

    /**
     * @param int $status
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    protected function filterStatus($status)
    {
        if (!is_int($status) || $status < 100 || $status > 599) {
            throw new \InvalidArgumentException('Invalid HTTP status code');
        }

        return $status;
    }
}
