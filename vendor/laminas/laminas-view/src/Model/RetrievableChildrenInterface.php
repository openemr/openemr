<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */
namespace Laminas\View\Model;

/**
 * Interface describing a Retrievable Child Model
 *
 * Models implementing this interface provide a way to get there children by capture
 */
interface RetrievableChildrenInterface
{
    /**
     * Returns an array of Viewmodels with captureTo value $capture
     *
     * @param string $capture
     * @param bool $recursive search recursive through children, default true
     * @return array
     */
    public function getChildrenByCaptureTo($capture, $recursive = true);
}
