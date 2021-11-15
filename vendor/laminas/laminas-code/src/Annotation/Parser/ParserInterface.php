<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Annotation\Parser;

use Laminas\EventManager\EventInterface;

interface ParserInterface
{
    /**
     * Respond to the "createAnnotation" event
     *
     * @param  EventInterface  $e
     * @return false|\stdClass
     */
    public function onCreateAnnotation(EventInterface $e);

    /**
     * Register an annotation this parser will accept
     *
     * @param  mixed $annotation
     * @return void
     */
    public function registerAnnotation($annotation);

    /**
     * Register multiple annotations this parser will accept
     *
     * @param  array|\Traversable $annotations
     * @return void
     */
    public function registerAnnotations($annotations);
}
