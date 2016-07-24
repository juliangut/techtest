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

abstract class Headers
{
    /**
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * @var ArrayCollection
     */
    protected $headers;

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @param $version
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setProtocolVersion($version)
    {
        if (!preg_match('/^1\.(0|1)$/', $version)) {
            throw new \InvalidArgumentException('Invalid HTTP version');
        }

        $this->protocolVersion = $version;

        return $this;
    }

    /**
     * Initialize headers collection
     */
    protected function initHeaders()
    {
        $this->headers = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers->toArray();
    }

    /**
     * @param $header
     *
     * @return bool
     */
    public function hasHeader($header)
    {
        return $this->headers->containsKey($header);
    }

    /**
     * @param string     $header
     * @param null|mixed $default
     *
     * @return mixed|null
     */
    public function getHeader($header, $default = null)
    {
        return $this->headers->containsKey($header) ? $this->headers->get($header) : $default;
    }

    /**
     * @param $header
     *
     * @return string
     */
    public function getHeaderLine($header)
    {
        return implode(',', $this->headers->containsKey($header) ? $this->headers->get($header) : ['']);
    }

    /**
     * @param string $header
     * @param mixed  $value
     *
     * @return $this
     */
    public function addHeader($header, $value)
    {
        if (!$this->headers->containsKey($header)) {
            $this->setHeader($header, $value);
        } else {
            $this->headers->set($header, array_merge($this->headers->get($header), [(string) $value]));
        }

        return $this;
    }

    /**
     * @param string $header
     * @param mixed  $value
     *
     * @return $this
     */
    public function setHeader($header, $value)
    {
        $this->headers->set($header, [(string) $value]);

        return $this;
    }
}
