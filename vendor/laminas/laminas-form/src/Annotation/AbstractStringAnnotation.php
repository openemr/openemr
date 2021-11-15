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
use function is_string;
use function sprintf;

abstract class AbstractStringAnnotation
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Receive and process the contents of an annotation
     *
     * @param  array $data
     * @throws Exception\DomainException if a 'value' key is missing, or its value is not a string
     */
    public function __construct(array $data)
    {
        if (! isset($data['value']) || ! is_string($data['value'])) {
            throw new Exception\DomainException(sprintf(
                '%s expects the annotation to define a string; received "%s"',
                get_class($this),
                gettype($data['value'])
            ));
        }
        $this->value = $data['value'];
    }
}
