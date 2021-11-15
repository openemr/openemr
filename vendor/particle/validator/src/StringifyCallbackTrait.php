<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator;

/**
 * This Trait is for rendering callable objects to a string, if that's possible.
 *
 * @package Particle\Validator
 */
trait StringifyCallbackTrait
{
    /**
     * Returns a string representation of a callback, if it implements the __toString method.
     *
     * @param callable|null $callback
     * @return string
     */
    protected function getCallbackAsString($callback)
    {
        if (is_object($callback) && method_exists($callback, '__toString')) {
            return (string) $callback;
        }
        return '';
    }
}
