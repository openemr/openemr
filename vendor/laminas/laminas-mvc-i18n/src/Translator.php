<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\I18n;

use Laminas\I18n\Translator\TranslatorInterface as I18nTranslatorInterface;
use Laminas\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;

class Translator implements
    I18nTranslatorInterface,
    ValidatorTranslatorInterface
{
    /**
     * @var I18nTranslatorInterface
     */
    protected $translator;

    /**
     * @param I18nTranslatorInterface $translator
     */
    public function __construct(I18nTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Proxy unknown method calls to underlying translator instance
     *
     * Note: this method is only implemented to keep backwards compatibility
     * with pre-2.3.0 code.
     *
     * @deprecated
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, array $args)
    {
        if (! method_exists($this->translator, $method)) {
            throw new Exception\BadMethodCallException(sprintf(
                'Unable to call method "%s"; does not exist in translator',
                $method
            ));
        }
        return call_user_func_array([$this->translator, $method], $args);
    }

    /**
     * @return I18nTranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Translate a message using the given text domain and locale
     *
     * @param string $message
     * @param string $textDomain
     * @param string $locale
     * @return string
     */
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        return $this->translator->translate($message, $textDomain, $locale);
    }

    /**
     * Provide a pluralized translation of the given string using the given text domain and locale
     *
     * @param string $singular
     * @param string $plural
     * @param string $number
     * @param string $textDomain
     * @param string $locale
     * @return string
     */
    public function translatePlural($singular, $plural, $number, $textDomain = 'default', $locale = null)
    {
        return $this->translator->translatePlural($singular, $plural, $number, $textDomain, $locale);
    }
}
