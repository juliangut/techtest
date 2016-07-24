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

class Request extends Headers
{
    use CollectorTrait;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var ArrayCollection
     */
    protected $params;

    /**
     * @var ArrayCollection
     */
    protected $attributes;

    /**
     * Request constructor.
     *
     * @param array $requestData
     */
    public function __construct(array $requestData = [])
    {
        $requestData = new ArrayCollection($requestData);

        if ($requestData->get('SERVER_PROTOCOL')) {
            $this->protocolVersion = str_replace('HTTP/', '', $requestData->get('SERVER_PROTOCOL'));
        }

        $this->method = $requestData->get('REQUEST_METHOD');
        $this->uri = new Uri($requestData);

        $this->loadHeaders($requestData);
        $this->loadPayload();

        $this->attributes = new ArrayCollection();
    }

    /**
     * @param ArrayCollection $requestData
     */
    private function loadHeaders(ArrayCollection $requestData)
    {
        static $allowedHeaders = [
            'CONTENT_TYPE',
            'CONTENT_LENGTH',
            'PHP_AUTH_USER',
            'PHP_AUTH_PW',
            'PHP_AUTH_DIGEST',
            'AUTH_TYPE',
        ];

        static $headersTransforms = [
            'CONTENT_TYPE' => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'HTTP_ACCEPT' => 'Accept',
            'HTTP_ACCEPT_ENCODING' => 'Accept-Encoding',
            'HTTP_ACCEPT_LANGUAGE' => 'Accept-Language',
        ];

        $this->initHeaders();
        foreach ($requestData->toArray() as $header => $value) {
            if (in_array($header, $allowedHeaders, true) || strpos($header, 'HTTP_') === 0) {
                if (array_key_exists($header, $headersTransforms)) {
                    $header = $headersTransforms[$header];
                }

                $this->setHeader($header, $value);
            }
        }
    }

    private function loadPayload()
    {
        $payload = stream_get_contents(fopen('php://input', 'r'));
        $contentType = $this->getHeaderLine('Content-Type');

        $params = [];
        if (strpos($contentType, 'application/json') === 0 && !empty($payload)) {
            // @codeCoverageIgnoreStart
            $params = json_decode($payload, true);
            // @codeCoverageIgnoreEnd
        } elseif (strpos($contentType, 'application/x-www-form-urlencoded') === 0 && !empty($payload)) {
            // @codeCoverageIgnoreStart
            parse_str($payload, $params);
            // @codeCoverageIgnoreEnd
        } elseif ($this->method === 'POST' && (is_object($_POST) || is_array($_POST))) {
            $params = (array) $_POST;
        }
        $this->params = new ArrayCollection($params);
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return Uri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string     $param
     * @param null|mixed $default
     *
     * @return mixed|null
     */
    public function getParam($param, $default = null)
    {
        return $this->getCollectionElement($this->params, $param, $default);
    }

    /**
     * @param string     $attribute
     * @param null|mixed $default
     *
     * @return mixed|null
     */
    public function getAttribute($attribute, $default = null)
    {
        return $this->getCollectionElement($this->attributes, $attribute, $default);
    }

    /**
     * @param string $attribute
     * @param mixed  $value
     *
     * @return $this
     */
    public function setAttribute($attribute, $value)
    {
        $this->setCollectionElement($this->attributes, $attribute, $value);

        return $this;
    }
}
