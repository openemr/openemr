<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\I18n;

use Laminas\I18n\Translator\TranslatorInterface as I18nTranslatorInterface;

class DummyTranslator implements I18nTranslatorInterface
{
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        return $message;
    }

    public function translatePlural($singular, $plural, $number, $textDomain = 'default', $locale = null)
    {
        return ($number == 1 ? $singular : $plural);
    }
}
