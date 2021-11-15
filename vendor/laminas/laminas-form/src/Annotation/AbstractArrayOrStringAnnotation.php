<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Annotation;

use Laminas\Form\Exception;

use function get_class;
use function gettype;
use function is_array;
use function is_string;
use function sprintf;

abstract class AbstractArrayOrStringAnnotation
{
    /**
     * @var array|string
     */
    protected $value;

    /**
     * Receive and process the contents of an annotation
     *
     * @param  array $data
     * @throws Exception\DomainException if a 'value' key is missing, or its value is not an array or string
     */
    public function __construct(array $data)
    {
        if (! isset($data['value']) || (! is_array($data['value']) && ! is_string($data['value']))) {
            throw new Exception\DomainException(sprintf(
                '%s expects the annotation to define an array or string; received "%s"',
                get_class($this),
                isset($data['value']) ? gettype($data['value']) : 'null'
            ));
        }
        $this->value = $data['value'];
    }
}
