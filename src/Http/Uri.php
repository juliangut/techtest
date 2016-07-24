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

class Uri
{
    /**
     * @var string
     */
    protected $schema;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $path;

    /**
     * Uri constructor.
     *
     * @param ArrayCollection $requestData
     */
    public function __construct(ArrayCollection $requestData)
    {
        $this->loadSchema($requestData);
        $this->loadUserAuth($requestData);
        $this->loadHostPort($requestData);
        $this->loadPath($requestData);
    }

    /**
     * @param ArrayCollection $requestData
     */
    protected function loadSchema(ArrayCollection $requestData)
    {
        $isSecure = $requestData->get('HTTPS');
        $this->schema = (empty($isSecure) || $isSecure === 'off') ? 'http' : 'https';
    }

    /**
     * @param ArrayCollection $requestData
     */
    protected function loadUserAuth(ArrayCollection $requestData)
    {
        $this->username = $requestData->containsKey('PHP_AUTH_USER') ? $requestData->get('PHP_AUTH_USER') : '';
        $this->password = $requestData->containsKey('PHP_AUTH_PW') ? $requestData->get('PHP_AUTH_PW') : '';

        if (empty($this->username)
            && $requestData->containsKey('HTTP_AUTHORIZATION')
            && (strpos(strtolower($requestData->get('HTTP_AUTHORIZATION')), 'basic') === 0)
        ) {
            list($this->username, $this->password) =
                explode(':', base64_decode(substr($requestData->get('HTTP_AUTHORIZATION'), 6)));
        }
    }

    /**
     * @param ArrayCollection $requestData
     */
    protected function loadHostPort(ArrayCollection $requestData)
    {
        $this->host =
            $requestData->containsKey('HTTP_HOST') ? $requestData->get('HTTP_HOST') : $requestData->get('SERVER_NAME');

        $this->port = $requestData->containsKey('SERVER_PORT') ? (int) $requestData->get('SERVER_PORT') : 80;

        $pos = strpos($this->host, ':');
        if ($pos !== false) {
            $this->port = (int) substr($this->host, $pos + 1);
            $this->host = strstr($this->host, ':', true);
        }
    }

    /**
     * @param ArrayCollection $requestData
     */
    protected function loadPath(ArrayCollection $requestData)
    {
        $requestScriptName = parse_url($requestData->get('SCRIPT_NAME'), PHP_URL_PATH);
        $requestScriptDir = dirname($requestScriptName);

        $requestUri = parse_url(
            sprintf('%s://%s%s', $this->schema, $this->host, $requestData->get('REQUEST_URI')),
            PHP_URL_PATH
        );

        $basePath = '';
        $this->path = $requestUri;

        // @codeCoverageIgnoreStart
        // Shouldn't be needed using PHP built-in server
        if (stripos($requestUri, $requestScriptName) === 0) {
            $basePath = $requestScriptName;
        } elseif ($requestScriptDir !== '/' && stripos($requestUri, $requestScriptDir) === 0) {
            $basePath = $requestScriptDir;
        }

        if (!empty($basePath)) {
            $this->path = ltrim(substr($this->path, strlen($basePath)), '/');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        $url = sprintf('%s://%s', $this->schema, $this->host);

        if (!empty($this->port) && $this->port !== 80) {
            $url .= ':' . $this->port;
        }

        if (!empty($this->path)) {
            $url .= '/' . ltrim($this->path, '/');
        }

        return $url;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getBaseUrl();
    }
}
