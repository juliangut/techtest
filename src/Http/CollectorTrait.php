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

trait CollectorTrait
{
    /**
     * @param ArrayCollection $collection
     * @param string          $element
     * @param null|mixed      $default
     *
     * @return mixed|null
     */
    protected function getCollectionElement(ArrayCollection $collection, $element, $default = null)
    {
        return $collection->containsKey($element) ? $collection->get($element) : $default;
    }

    /**
     * @param ArrayCollection $collection
     * @param string          $element
     * @param mixed           $value
     */
    protected function setCollectionElement(ArrayCollection $collection, $element, $value)
    {
        $collection->set($element, $value);
    }
}
