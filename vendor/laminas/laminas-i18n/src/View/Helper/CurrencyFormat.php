<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\View\Helper;

use Laminas\I18n\Exception;
use Laminas\View\Helper\AbstractHelper;
use Locale;
use NumberFormatter;

/**
 * View helper for formatting currency.
 */
class CurrencyFormat extends AbstractHelper
{
    /**
     * The 3-letter ISO 4217 currency code indicating the currency to use
     *
     * @var string
     */
    protected $currencyCode;

    /**
     * Formatter instances
     *
     * @var array
     */
    protected $formatters = [];

    /**
     * Locale to use instead of the default
     *
     * @var string
     */
    protected $locale;

    /**
     * Currency pattern
     *
     * @var string
     */
    protected $currencyPattern;

    /**
     * If set to true, the currency will be returned with two decimals
     *
     * @var bool
     */
    protected $showDecimals = true;

    /**
     * Special condition to be checked due to ICU bug:
     * http://bugs.icu-project.org/trac/ticket/10997
     *
     * @var bool
     */
    protected $correctionNeeded = false;

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
     * @param  float       $number
     * @param  string|null $currencyCode
     * @param  bool|null   $showDecimals
     * @param  string|null $locale
     * @param  string|null $pattern
     * @return string
     */
    public function __invoke(
        $number,
        $currencyCode = null,
        $showDecimals = null,
        $locale = null,
        $pattern = null
    ) {
        if (null === $locale) {
            $locale = $this->getLocale();
        }
        if (null === $currencyCode) {
            $currencyCode = $this->getCurrencyCode();
        }
        if (null === $showDecimals) {
            $showDecimals = $this->shouldShowDecimals();
        }
        if (null === $pattern) {
            $pattern = $this->getCurrencyPattern();
        }

        return $this->formatCurrency($number, $currencyCode, $showDecimals, $locale, $pattern);
    }

    /**
     * Format a number
     *
     * @param  float  $number
     * @param  string $currencyCode
     * @param  bool   $showDecimals
     * @param  string $locale
     * @param  string $pattern
     * @return string
     */
    protected function formatCurrency(
        $number,
        $currencyCode,
        $showDecimals,
        $locale,
        $pattern
    ) {
        $formatterId = md5($locale);

        if (! isset($this->formatters[$formatterId])) {
            $this->formatters[$formatterId] = new NumberFormatter(
                $locale,
                NumberFormatter::CURRENCY
            );
        }

        if ($pattern !== null) {
            $this->formatters[$formatterId]->setPattern($pattern);
        }

        if ($showDecimals) {
            $this->formatters[$formatterId]->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);
            $this->correctionNeeded = false;
        } else {
            $this->formatters[$formatterId]->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
            $defaultCurrencyCode = $this->formatters[$formatterId]->getTextAttribute(NumberFormatter::CURRENCY_CODE);
            $this->correctionNeeded = $defaultCurrencyCode !== $currencyCode;
        }

        $formattedNumber = $this->formatters[$formatterId]->formatCurrency($number, $currencyCode);

        if ($this->correctionNeeded) {
            $formattedNumber = $this->fixICUBugForNoDecimals(
                $formattedNumber,
                $this->formatters[$formatterId],
                $locale,
                $currencyCode
            );
        }

        return $formattedNumber;
    }

    /**
     * The 3-letter ISO 4217 currency code indicating the currency to use
     *
     * @param  string $currencyCode
     * @return $this
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * Get the 3-letter ISO 4217 currency code indicating the currency to use
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * Set the currency pattern
     *
     * @param  string $currencyPattern
     * @return $this
     */
    public function setCurrencyPattern($currencyPattern)
    {
        $this->currencyPattern = $currencyPattern;
        return $this;
    }

    /**
     * Get the currency pattern
     *
     * @return string
     */
    public function getCurrencyPattern()
    {
        return $this->currencyPattern;
    }

    /**
     * Set locale to use instead of the default
     *
     * @param  string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = (string) $locale;
        return $this;
    }

    /**
     * Get the locale to use
     *
     * @return string
     */
    public function getLocale()
    {
        if ($this->locale === null) {
            $this->locale = Locale::getDefault();
        }

        return $this->locale;
    }

    /**
     * Set if the view helper should show two decimals
     *
     * @param  bool $showDecimals
     * @return $this
     */
    public function setShouldShowDecimals($showDecimals)
    {
        $this->showDecimals = (bool) $showDecimals;
        return $this;
    }

    /**
     * Get if the view helper should show two decimals
     *
     * @return bool
     */
    public function shouldShowDecimals()
    {
        return $this->showDecimals;
    }

    /**
     * @param string          $formattedNumber
     * @param NumberFormatter $formatter
     * @param string          $locale
     * @param string          $currencyCode
     * @return string
     */
    private function fixICUBugForNoDecimals($formattedNumber, NumberFormatter $formatter, $locale, $currencyCode)
    {
        $pattern = sprintf(
            '/\%s\d+(\s?%s)?$/u',
            $formatter->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL),
            preg_quote($this->getCurrencySymbol($locale, $currencyCode), '/')
        );

        return preg_replace($pattern, '$1', $formattedNumber);
    }

    /**
     * @param string $locale
     * @param string $currencyCode
     * @return string
     */
    private function getCurrencySymbol($locale, $currencyCode)
    {
        $numberFormatter = new NumberFormatter($locale . '@currency=' . $currencyCode, NumberFormatter::CURRENCY);

        return $numberFormatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    }
}
