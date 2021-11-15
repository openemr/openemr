<?php

/**
 * @see       https://github.com/laminas/laminas-stdlib for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stdlib/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stdlib/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Stdlib;

interface MessageInterface
{
    /**
     * Set metadata
     *
     * @param  string|int|array|\Traversable $spec
     * @param  mixed $value
     */
    public function setMetadata($spec, $value = null);

    /**
     * Get metadata
     *
     * @param  null|string|int $key
     * @return mixed
     */
    public function getMetadata($key = null);

    /**
     * Set content
     *
     * @param  mixed $content
     * @return mixed
     */
    public function setContent($content);

    /**
     * Get content
     *
     * @return mixed
     */
    public function getContent();
}
