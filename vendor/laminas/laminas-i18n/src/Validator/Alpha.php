<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Validator;

use Laminas\I18n\Filter\Alpha as AlphaFilter;

class Alpha extends Alnum
{
    const INVALID      = 'alphaInvalid';
    const NOT_ALPHA    = 'notAlpha';
    const STRING_EMPTY = 'alphaStringEmpty';

    /**
     * Alphabetic filter used for validation
     *
     * @var AlphaFilter
     */
    protected static $filter;

    /**
     * Validation failure message template definitions
     *
     * @var string[]
     */
    protected $messageTemplates = [
        self::INVALID      => 'Invalid type given. String expected',
        self::NOT_ALPHA    => 'The input contains non alphabetic characters',
        self::STRING_EMPTY => 'The input is an empty string'
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
     * Returns true if and only if $value contains only alphabetic characters
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (! is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        if ('' === $value) {
            $this->error(self::STRING_EMPTY);
            return false;
        }

        if (null === static::$filter) {
            static::$filter = new AlphaFilter();
        }

        //static::$filter->setAllowWhiteSpace($this->allowWhiteSpace);
        static::$filter->setAllowWhiteSpace($this->options['allowWhiteSpace']);

        if ($value !== static::$filter->filter($value)) {
            $this->error(self::NOT_ALPHA);
            return false;
        }

        return true;
    }
}
