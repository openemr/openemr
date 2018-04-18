<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\View\Helper;

use Locale;
use NumberFormatter;
use Zend\I18n\Exception;
use Zend\View\Helper\AbstractHelper;

/**
 * View helper for formatting dates.
 */
class NumberFormat extends AbstractHelper
{
    /**
     * number of decimals to use.
     *
     * @var int
     */
    protected $decimals;

    /**
     * NumberFormat style to use
     *
     * @var int
     */
    protected $formatStyle;

    /**
     * NumberFormat type to use
     *
     * @var int
     */
    protected $formatType;

    /**
     * Formatter instances
     *
     * @var array
     */
    protected $formatters = [];

    /**
     * Text attributes.
     *
     * @var array
     */
    protected $textAttributes = [];

    /**
     * Locale to use instead of the default
     *
     * @var string
     */
    protected $locale;

    /**
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present
     */
    public function __construct()
    {
        if (! extension_loaded('intl')) {
            throw new Exception\ExtensionNotLoadedException(sprintf(
                '%s component requires the intl PHP extension',
                __NAMESPACE__
            ));
        }
    }

    /**
     * Format a number
     *
     * @param  int|float $number
     * @param  int       $formatStyle
     * @param  int       $formatType
     * @param  string    $locale
     * @param  int       $decimals
     * @param  array|null $textAttributes
     * @return string
     */
    public function __invoke(
        $number,
        $formatStyle = null,
        $formatType = null,
        $locale = null,
        $decimals = null,
        array $textAttributes = null
    ) {
        if (null === $locale) {
            $locale = $this->getLocale();
        }
        if (null === $formatStyle) {
            $formatStyle = $this->getFormatStyle();
        }
        if (null === $formatType) {
            $formatType = $this->getFormatType();
        }
        if (! is_int($decimals) || $decimals < 0) {
            $decimals = $this->getDecimals();
        }
        if (! is_array($textAttributes)) {
            $textAttributes = $this->getTextAttributes();
        }

        $formatterId = md5(
            $formatStyle . "\0" . $locale . "\0" . $decimals . "\0"
            . md5(serialize($textAttributes))
        );

        if (isset($this->formatters[$formatterId])) {
            $formatter = $this->formatters[$formatterId];
        } else {
            $formatter = new NumberFormatter($locale, $formatStyle);

            if ($decimals !== null) {
                $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
                $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
            }

            foreach ($textAttributes as $textAttribute => $value) {
                $formatter->setTextAttribute($textAttribute, $value);
            }

            $this->formatters[$formatterId] = $formatter;
        }

        return $formatter->format($number, $formatType);
    }

    /**
     * Set format style to use instead of the default
     *
     * @param  int $formatStyle
     * @return NumberFormat
     */
    public function setFormatStyle($formatStyle)
    {
        $this->formatStyle = (int) $formatStyle;
        return $this;
    }

    /**
     * Get the format style to use
     *
     * @return int
     */
    public function getFormatStyle()
    {
        if (null === $this->formatStyle) {
            $this->formatStyle = NumberFormatter::DECIMAL;
        }

        return $this->formatStyle;
    }

    /**
     * Set format type to use instead of the default
     *
     * @param  int $formatType
     * @return NumberFormat
     */
    public function setFormatType($formatType)
    {
        $this->formatType = (int) $formatType;
        return $this;
    }

    /**
     * Get the format type to use
     *
     * @return int
     */
    public function getFormatType()
    {
        if (null === $this->formatType) {
            $this->formatType = NumberFormatter::TYPE_DEFAULT;
        }
        return $this->formatType;
    }

    /**
     * Set number of decimals to use instead of the default.
     *
     * @param  int $decimals
     * @return NumberFormat
     */
    public function setDecimals($decimals)
    {
        $this->decimals = $decimals;
        return $this;
    }

    /**
     * Get number of decimals.
     *
     * @return int
     */
    public function getDecimals()
    {
        return $this->decimals;
    }

    /**
     * Set locale to use instead of the default.
     *
     * @param  string $locale
     * @return NumberFormat
     */
    public function setLocale($locale)
    {
        $this->locale = (string) $locale;
        return $this;
    }

    /**
     * Get the locale to use
     *
     * @return string|null
     */
    public function getLocale()
    {
        if ($this->locale === null) {
            $this->locale = Locale::getDefault();
        }

        return $this->locale;
    }

    /**
     * @return array
     */
    public function getTextAttributes()
    {
        return $this->textAttributes;
    }

    /**
     * @param array $textAttributes
     * @return NumberFormat
     */
    public function setTextAttributes(array $textAttributes)
    {
        $this->textAttributes = $textAttributes;
        return $this;
    }
}
