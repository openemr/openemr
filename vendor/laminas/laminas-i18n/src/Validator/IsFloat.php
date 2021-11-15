<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Validator;

use IntlException;
use Laminas\I18n\Exception as I18nException;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\StringUtils;
use Laminas\Stdlib\StringWrapper\StringWrapperInterface;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Locale;
use NumberFormatter;
use Traversable;

class IsFloat extends AbstractValidator
{
    const INVALID   = 'floatInvalid';
    const NOT_FLOAT = 'notFloat';

    /**
     * Validation failure message template definitions
     *
     * @var string[]
     */
    protected $messageTemplates = [
        self::INVALID   => 'Invalid type given. String, integer or float expected',
        self::NOT_FLOAT => 'The input does not appear to be a float',
    ];

    /**
     * Optional locale
     *
     * @var string|null
     */
    protected $locale;

    /**
     * UTF-8 compatible wrapper for string functions
     *
     * @var StringWrapperInterface
     */
    protected $wrapper;

    /**
     * Constructor for the integer validator
     *
     * @param array|Traversable $options
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present
     */
    public function __construct($options = [])
    {
        if (! extension_loaded('intl')) {
            throw new I18nException\ExtensionNotLoadedException(
                sprintf('%s component requires the intl PHP extension', __NAMESPACE__)
            );
        }

        $this->wrapper = StringUtils::getWrapper();

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (isset($options['locale'])) {
            $this->setLocale($options['locale']);
        }

        parent::__construct($options);
    }

    /**
     * Returns the set locale
     *
     * @return string
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
     * @param string|null $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns true if and only if $value is a floating-point value. Uses the formal definition of a float as described
     * in the PHP manual: {@link https://www.php.net/float}
     *
     * @param  float|int|string $value
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function isValid($value)
    {
        if (! is_scalar($value) || is_bool($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        if (is_float($value) || is_int($value)) {
            return true;
        }

        // Need to check if this is scientific formatted string. If not, switch to decimal.
        $formatter = new NumberFormatter($this->getLocale(), NumberFormatter::SCIENTIFIC);

        try {
            if (intl_is_failure($formatter->getErrorCode())) {
                throw new Exception\InvalidArgumentException($formatter->getErrorMessage());
            }
        } catch (IntlException $intlException) {
            throw new Exception\InvalidArgumentException($intlException->getMessage(), 0, $intlException);
        }

        if (StringUtils::hasPcreUnicodeSupport()) {
            $exponentialSymbols = '[Ee' . $formatter->getSymbol(NumberFormatter::EXPONENTIAL_SYMBOL) . ']+';
            $search = '/' . $exponentialSymbols . '/u';
        } else {
            $exponentialSymbols = '[Ee]';
            $search = '/' . $exponentialSymbols . '/';
        }

        if (! preg_match($search, $value)) {
            $formatter = new NumberFormatter($this->getLocale(), NumberFormatter::DECIMAL);
        }

        /**
         * @desc There are separator "look-alikes" for decimal and group separators that are more commonly used than the
         *       official unicode character. We need to replace those with the real thing - or remove it.
         */
        $groupSeparator = $formatter->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
        $decSeparator   = $formatter->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        //NO-BREAK SPACE and ARABIC THOUSANDS SEPARATOR
        if ($groupSeparator === "\xC2\xA0") {
            $value = str_replace(' ', $groupSeparator, $value);
        } elseif ($groupSeparator === "\xD9\xAC") {
            //NumberFormatter doesn't have grouping at all for Arabic-Indic
            $value = str_replace(['\'', $groupSeparator], '', $value);
        }

        //ARABIC DECIMAL SEPARATOR
        if ($decSeparator === "\xD9\xAB") {
            $value = str_replace(',', $decSeparator, $value);
        }

        $groupSeparatorPosition = $this->wrapper->strpos($value, $groupSeparator);
        $decSeparatorPosition   = $this->wrapper->strpos($value, $decSeparator);

        //We have separators, and they are flipped. i.e. 2.000,000 for en-US
        if ($groupSeparatorPosition && $decSeparatorPosition && $groupSeparatorPosition > $decSeparatorPosition) {
            $this->error(self::NOT_FLOAT);

            return false;
        }

        //If we have Unicode support, we can use the real graphemes, otherwise, just the ASCII characters
        $decimal     = '['. preg_quote($decSeparator, '/') . ']';
        $prefix      = '[+-]';
        $exp         = $exponentialSymbols;
        $numberRange = '0-9';
        $useUnicode  = '';
        $suffix      = '';

        if (StringUtils::hasPcreUnicodeSupport()) {
            $prefix = '['
                .  preg_quote(
                    $formatter->getTextAttribute(NumberFormatter::POSITIVE_PREFIX)
                    .  $formatter->getTextAttribute(NumberFormatter::NEGATIVE_PREFIX)
                    .  $formatter->getSymbol(NumberFormatter::PLUS_SIGN_SYMBOL)
                    .  $formatter->getSymbol(NumberFormatter::MINUS_SIGN_SYMBOL),
                    '/'
                )
                . ']{0,3}';
            $suffix = ($formatter->getTextAttribute(NumberFormatter::NEGATIVE_SUFFIX))
                ? '['
                    .  preg_quote(
                        $formatter->getTextAttribute(NumberFormatter::POSITIVE_SUFFIX)
                        .  $formatter->getTextAttribute(NumberFormatter::NEGATIVE_SUFFIX)
                        .  $formatter->getSymbol(NumberFormatter::PLUS_SIGN_SYMBOL)
                        .  $formatter->getSymbol(NumberFormatter::MINUS_SIGN_SYMBOL),
                        '/'
                    )
                    . ']{0,3}'
                : '';
            $numberRange = '\p{N}';
            $useUnicode = 'u';
        }

        /**
         * @desc Match against the formal definition of a float. The
         *       exponential number check is modified for RTL non-Latin number
         *       systems (Arabic-Indic numbering). I'm also switching out the period
         *       for the decimal separator. The formal definition leaves out +- from
         *       the integer and decimal notations so add that.  This also checks
         *       that a grouping sperator is not in the last GROUPING_SIZE graphemes
         *       of the string - i.e. 10,6 is not valid for en-US.
         * @see https://www.php.net/float
         */

        $lnum    = '[' . $numberRange . ']+';
        $dnum    = '(([' . $numberRange . ']*' . $decimal . $lnum . ')|('
            . $lnum . $decimal . '[' . $numberRange . ']*))';
        $expDnum = '((' . $prefix . '((' . $lnum . '|' . $dnum . ')' . $exp . $prefix . $lnum . ')' . $suffix . ')|'
            . '(' . $suffix . '(' . $lnum . $prefix . $exp . '(' . $dnum . '|' . $lnum . '))' . $prefix . '))';

        // LEFT-TO-RIGHT MARK (U+200E) is messing up everything for the handful
        // of locales that have it
        $lnumSearch     = str_replace("\xE2\x80\x8E", '', '/^' .$prefix . $lnum . $suffix . '$/' . $useUnicode);
        $dnumSearch     = str_replace("\xE2\x80\x8E", '', '/^' .$prefix . $dnum . $suffix . '$/' . $useUnicode);
        $expDnumSearch  = str_replace("\xE2\x80\x8E", '', '/^' . $expDnum . '$/' . $useUnicode);
        $value          = str_replace("\xE2\x80\x8E", '', $value);
        $unGroupedValue = str_replace($groupSeparator, '', $value);

        // No strrpos() in wrappers yet. ICU 4.x doesn't have grouping size for
        // everything. ICU 52 has 3 for ALL locales.
        $groupSize = $formatter->getAttribute(NumberFormatter::GROUPING_SIZE) ?: 3;
        $lastStringGroup = $this->wrapper->substr($value, -$groupSize);

        if ((preg_match($lnumSearch, $unGroupedValue)
            || preg_match($dnumSearch, $unGroupedValue)
            || preg_match($expDnumSearch, $unGroupedValue))
            && false === $this->wrapper->strpos($lastStringGroup, $groupSeparator)
        ) {
            return true;
        }

        $this->error(self::NOT_FLOAT);

        return false;
    }
}
