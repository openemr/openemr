<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\View\Helper;

use Laminas\I18n\Exception;

/**
 * View helper for translating plural messages.
 */
class TranslatePlural extends AbstractTranslatorHelper
{
    /**
     * Translate a plural message
     *
     * @param  string      $singular
     * @param  string      $plural
     * @param  int         $number
     * @param  string|null $textDomain
     * @param  string|null $locale
     * @throws Exception\RuntimeException
     * @return string
     */
    public function __invoke(
        $singular,
        $plural,
        $number,
        $textDomain = null,
        $locale = null
    ) {
        $translator = $this->getTranslator();
        if (null === $translator) {
            throw new Exception\RuntimeException('Translator has not been set');
        }
        if (null === $textDomain) {
            $textDomain = $this->getTranslatorTextDomain();
        }

        return $translator->translatePlural($singular, $plural, $number, $textDomain, $locale);
    }
}
