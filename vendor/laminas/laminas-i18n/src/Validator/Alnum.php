<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Validator;

use Laminas\I18n\Filter\Alnum as AlnumFilter;
use Laminas\Validator\AbstractValidator;

class Alnum extends AbstractValidator
{
    const INVALID      = 'alnumInvalid';
    const NOT_ALNUM    = 'notAlnum';
    const STRING_EMPTY = 'alnumStringEmpty';

    /**
     * Alphanumeric filter used for validation
     *
     * @var AlnumFilter
     */
    protected static $filter;

    /**
     * Validation failure message template definitions
     *
     * @var string[]
     */
    protected $messageTemplates = [
        self::INVALID      => 'Invalid type given. String, integer or float expected',
        self::NOT_ALNUM    => 'The input contains characters which are non alphabetic and no digits',
        self::STRING_EMPTY => 'The input is an empty string',
    ];

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = [
        'allowWhiteSpace' => false,  // Whether to allow white space characters; off by default
    ];

    /**
     * Sets default option values for this instance
     *
     * @param array|bool $allowWhiteSpace
     */
    public function __construct($allowWhiteSpace = false)
    {
        $options = is_array($allowWhiteSpace) ? $allowWhiteSpace : null;
        parent::__construct($options);

        if (is_scalar($allowWhiteSpace)) {
            $this->options['allowWhiteSpace'] = (bool) $allowWhiteSpace;
        }
    }

    /**
     * Returns the allowWhiteSpace option
     *
     * @return bool
     */
    public function getAllowWhiteSpace()
    {
        return $this->options['allowWhiteSpace'];
    }

    /**
     * Sets the allowWhiteSpace option
     *
     * @param  bool $allowWhiteSpace
     * @return $this
     */
    public function setAllowWhiteSpace($allowWhiteSpace)
    {
        $this->options['allowWhiteSpace'] = (bool) $allowWhiteSpace;
        return $this;
    }

    /**
     * Returns true if and only if $value contains only alphabetic and digit characters
     *
     * @param  int|float|string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);
        if ('' === $value) {
            $this->error(self::STRING_EMPTY);
            return false;
        }

        if (null === static::$filter) {
            static::$filter = new AlnumFilter();
        }

        static::$filter->setAllowWhiteSpace($this->options['allowWhiteSpace']);

        if ($value != static::$filter->filter($value)) {
            $this->error(self::NOT_ALNUM);
            return false;
        }

        return true;
    }
}
