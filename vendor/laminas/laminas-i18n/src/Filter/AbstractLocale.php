<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\I18n\Exception;
use Locale;

abstract class AbstractLocale extends AbstractFilter
{
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
     * Sets the locale option
     *
     * @param  string|null $locale
     * @return $this
     */
    public function setLocale($locale = null)
    {
        $this->options['locale'] = $locale;
        return $this;
    }

    /**
     * Returns the locale option
     *
     * @return string
     */
    public function getLocale()
    {
        if (! isset($this->options['locale'])) {
            $this->options['locale'] = Locale::getDefault();
        }
        return $this->options['locale'];
    }
}
