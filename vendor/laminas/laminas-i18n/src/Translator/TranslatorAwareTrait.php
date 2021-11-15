<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Translator;

trait TranslatorAwareTrait
{
    /**
     * @var TranslatorInterface|null
     */
    protected $translator;

    /**
     * @var bool
     */
    protected $translatorEnabled = true;

    /**
     * @var string
     */
    protected $translatorTextDomain = 'default';

    /**
     * Sets translator to use in helper
     *
     * @param TranslatorInterface|null $translator
     * @param string|null              $textDomain
     * @return $this
     */
    public function setTranslator(TranslatorInterface $translator = null, $textDomain = null)
    {
        $this->translator = $translator;

        if (null !== $textDomain) {
            $this->setTranslatorTextDomain($textDomain);
        }

        return $this;
    }

    /**
     * Returns translator used in object
     *
     * @return TranslatorInterface|null
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Checks if the object has a translator
     *
     * @return bool
     */
    public function hasTranslator()
    {
        return (null !== $this->translator);
    }

    /**
     * Sets whether translator is enabled and should be used
     *
     * @param bool $enabled
     * @return $this
     */
    public function setTranslatorEnabled($enabled = true)
    {
        $this->translatorEnabled = $enabled;

        return $this;
    }

    /**
     * Returns whether translator is enabled and should be used
     *
     * @return bool
     */
    public function isTranslatorEnabled()
    {
        return $this->translatorEnabled;
    }

    /**
     * Set translation text domain
     *
     * @param string $textDomain
     * @return $this
     */
    public function setTranslatorTextDomain($textDomain = 'default')
    {
        $this->translatorTextDomain = $textDomain;

        return $this;
    }

    /**
     * Return the translation text domain
     *
     * @return string
     */
    public function getTranslatorTextDomain()
    {
        return $this->translatorTextDomain;
    }
}
