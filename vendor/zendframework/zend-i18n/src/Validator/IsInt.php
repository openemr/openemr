<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Validator;

use Locale;
use NumberFormatter;
use Traversable;
use IntlException;
use Zend\I18n\Exception as I18nException;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class IsInt extends AbstractValidator
{
    const INVALID = 'intInvalid';
    const NOT_INT = 'notInt';
    const NOT_INT_STRICT = 'notIntStrict';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID => "Invalid type given. String or integer expected",
        self::NOT_INT => "The input does not appear to be an integer",
        self::NOT_INT_STRICT => "The input is not strictly an integer",
    ];

    /**
     * Optional locale
     *
     * @var string|null
     */
    protected $locale;

    /**
     * Data type is not enforced by default, so the string '123' is considered an integer.
     * Setting strict to true will enforce the integer data type.
     *
     * @var bool
     */
    protected $strict = false;

    /**
     * Constructor for the integer validator
     *
     * @param  array|Traversable $options
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present
     */
    public function __construct($options = [])
    {
        if (! extension_loaded('intl')) {
            throw new I18nException\ExtensionNotLoadedException(sprintf(
                '%s component requires the intl PHP extension',
                __NAMESPACE__
            ));
        }

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (array_key_exists('locale', $options)) {
            $this->setLocale($options['locale']);
        }

        if (array_key_exists('strict', $options)) {
            $this->setStrict($options['strict']);
        }

        parent::__construct($options);
    }

    /**
     * Returns the set locale
     */
    public function getLocale()
    {
        if (null === $this->locale) {
            $this->locale = Locale::getDefault();
        }
        return $this->locale;
    }

    /**
     * Sets the locale to use
     *
     * @param  string $locale
     * @return Int
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns the strict option
     *
     * @return bool
     */
    public function getStrict()
    {
        return $this->strict;
    }

    /**
     * Sets the strict option mode
     *
     * @param bool $strict
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setStrict($strict)
    {
        if (! is_bool($strict)) {
            throw new Exception\InvalidArgumentException('Strict option must be a boolean');
        }

        $this->strict = $strict;
        return $this;
    }

    /**
     * Returns true if and only if $value is a valid integer
     *
     * @param  string|int $value
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function isValid($value)
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            $this->error(self::INVALID);
            return false;
        }

        if (is_int($value)) {
            return true;
        }

        if ($this->strict) {
            $this->error(self::NOT_INT_STRICT);
            return false;
        }

        $this->setValue($value);

        $locale = $this->getLocale();
        try {
            $format = new NumberFormatter($locale, NumberFormatter::DECIMAL);
            if (intl_is_failure($format->getErrorCode())) {
                throw new Exception\InvalidArgumentException("Invalid locale string given");
            }
        } catch (IntlException $intlException) {
            throw new Exception\InvalidArgumentException("Invalid locale string given", 0, $intlException);
        }

        try {
            $parsedInt = $format->parse($value, NumberFormatter::TYPE_INT64);
            if (intl_is_failure($format->getErrorCode())) {
                $this->error(self::NOT_INT);
                return false;
            }
        } catch (IntlException $intlException) {
            $this->error(self::NOT_INT);
            return false;
        }

        $decimalSep  = $format->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        $groupingSep = $format->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL);

        $valueFiltered = str_replace($groupingSep, '', $value);
        $valueFiltered = str_replace($decimalSep, '.', $valueFiltered);

        if (strval($parsedInt) !== $valueFiltered) {
            $this->error(self::NOT_INT);
            return false;
        }

        return true;
    }
}
