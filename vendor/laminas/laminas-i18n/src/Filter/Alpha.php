<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Filter;

use Laminas\Stdlib\StringUtils;
use Locale;

class Alpha extends Alnum
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns the string $value, removing all but alphabetic characters
     *
     * @param  string|array $value
     * @return string|array
     */
    public function filter($value)
    {
        if (! is_scalar($value) && ! is_array($value)) {
            return $value;
        }

        $whiteSpace = $this->options['allow_white_space'] ? '\s' : '';
        $language   = Locale::getPrimaryLanguage($this->getLocale());

        if (! StringUtils::hasPcreUnicodeSupport()) {
            // POSIX named classes are not supported, use alternative [a-zA-Z] match
            $pattern = '/[^a-zA-Z' . $whiteSpace . ']/';
        } elseif (in_array($language, ['ja', 'ko', 'zh'], true)) {
            // Use english alphabet
            $pattern = '/[^a-zA-Z'  . $whiteSpace . ']/u';
        } else {
            // Use native language alphabet
            $pattern = '/[^\p{L}' . $whiteSpace . ']/u';
        }

        return preg_replace($pattern, '', $value);
    }
}
