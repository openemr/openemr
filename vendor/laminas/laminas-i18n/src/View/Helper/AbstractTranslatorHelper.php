<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\View\Helper;

use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\View\Helper\AbstractHelper;

abstract class AbstractTranslatorHelper extends AbstractHelper implements
    TranslatorAwareInterface
{
    /**
     * Translator (optional)
     *
     * @var Translator|null
     */
    protected $translator;

    /**
     * Translator text domain (optional)
     *
     * @var string
     */
    protected $translatorTextDomain = 'default';

    /**
     * Whether translator should be used
     *
     * @var bool
     */
    protected $translatorEnabled = true;

    /**
     * Sets translator to use in helper
     *
     * @param  Translator|null $translator Default is null, which sets no translator.
     * @param  string|null     $textDomain Default is null, which skips setTranslatorTextDomain
     * @return $this
     */
    public function setTranslator(Translator $translator = null, $textDomain = null)
    {
        $this->translator = $translator;
        if (null !== $textDomain) {
            $this->setTranslatorTextDomain($textDomain);
        }

        return $this;
    }

    /**
     * Returns translator used in helper
     *
     * @return Translator|null
     */
    public function getTranslator()
    {
        if (! $this->isTranslatorEnabled()) {
            return null;
        }

        return $this->translator;
    }

    /**
     * Checks if the helper has a translator
     *
     * @return bool
     */
    public function hasTranslator()
    {
        return (bool) $this->getTranslator();
    }

    /**
     * Sets whether translator is enabled and should be used
     *
     * @param  bool $enabled
     * @return $this
     */
    public function setTranslatorEnabled($enabled = true)
    {
        $this->translatorEnabled = (bool) $enabled;
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
     * @param  string $textDomain
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
