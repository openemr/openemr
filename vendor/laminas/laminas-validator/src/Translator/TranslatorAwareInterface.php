<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\Translator;

interface TranslatorAwareInterface
{
    /**
     * Sets translator to use in helper
     *
     * @param  TranslatorInterface $translator  [optional] translator.
     *             Default is null, which sets no translator.
     * @param  string $textDomain  [optional] text domain
     *             Default is null, which skips setTranslatorTextDomain
     * @return self
     */
    public function setTranslator(TranslatorInterface $translator = null, $textDomain = null);

    /**
     * Returns translator used in object
     *
     * @return TranslatorInterface|null
     */
    public function getTranslator();

    /**
     * Checks if the object has a translator
     *
     * @return bool
     */
    public function hasTranslator();

    /**
     * Sets whether translator is enabled and should be used
     *
     * @param  bool $enabled [optional] whether translator should be used.
     *                  Default is true.
     * @return self
     */
    public function setTranslatorEnabled($enabled = true);

    /**
     * Returns whether translator is enabled and should be used
     *
     * @return bool
     */
    public function isTranslatorEnabled();

    /**
     * Set translation text domain
     *
     * @param  string $textDomain
     * @return TranslatorAwareInterface
     */
    public function setTranslatorTextDomain($textDomain = 'default');

    /**
     * Return the translation text domain
     *
     * @return string
     */
    public function getTranslatorTextDomain();
}
