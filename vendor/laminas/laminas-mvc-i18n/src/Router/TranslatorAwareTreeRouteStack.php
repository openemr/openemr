<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\I18n\Router;

use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\Router\Exception;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RouteInterface;
use Laminas\Stdlib\RequestInterface as Request;
use Traversable;

/**
 * Translator aware tree route stack.
 */
class TranslatorAwareTreeRouteStack extends TreeRouteStack implements TranslatorAwareInterface
{
    /**
     * Translator used for translatable segments.
     *
     * @var Translator
     */
    protected $translator;

    /**
     * Whether the translator is enabled.
     *
     * @var bool
     */
    protected $translatorEnabled = true;

    /**
     * Translator text domain to use.
     *
     * @var string
     */
    protected $translatorTextDomain = 'default';

    /**
     * Override TreeRouteStack::factory()
     *
     * Overrides TreeRouteStack::factory() in order to inject the configured
     * translator_text_domain, if present, prior to returning the instance.
     *
     * @param array $options
     * @return self
     */
    public static function factory($options = [])
    {
        $instance = parent::factory($options);

        $flatOptions = $options;
        if ($flatOptions instanceof Traversable) {
            $flatOptions = iterator_to_array($flatOptions);
        }

        if (isset($flatOptions['translator_text_domain'])) {
            $instance->setTranslatorTextDomain($flatOptions['translator_text_domain']);
        }

        return $instance;
    }

    /**
     * match(): defined by RouteInterface
     *
     * @see    RouteInterface::match()
     * @param  Request      $request
     * @param  integer|null $pathOffset
     * @param  array        $options
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null, array $options = [])
    {
        if ($this->hasTranslator() && $this->isTranslatorEnabled() && ! isset($options['translator'])) {
            $options['translator'] = $this->getTranslator();
        }

        if (! isset($options['text_domain'])) {
            $options['text_domain'] = $this->getTranslatorTextDomain();
        }

        return parent::match($request, $pathOffset, $options);
    }

    /**
     * assemble(): defined by RouteInterface interface.
     *
     * @see    RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function assemble(array $params = [], array $options = [])
    {
        if ($this->hasTranslator() && $this->isTranslatorEnabled() && ! isset($options['translator'])) {
            $options['translator'] = $this->getTranslator();
        }

        if (! isset($options['text_domain'])) {
            $options['text_domain'] = $this->getTranslatorTextDomain();
        }

        return parent::assemble($params, $options);
    }

    /**
     * setTranslator(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::setTranslator()
     * @param  Translator $translator
     * @param  string     $textDomain
     * @return TreeRouteStack
     */
    public function setTranslator(Translator $translator = null, $textDomain = null)
    {
        $this->translator = $translator;

        if ($textDomain !== null) {
            $this->setTranslatorTextDomain($textDomain);
        }

        return $this;
    }

    /**
     * getTranslator(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::getTranslator()
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * hasTranslator(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::hasTranslator()
     * @return bool
     */
    public function hasTranslator()
    {
        return $this->translator !== null;
    }

    /**
     * setTranslatorEnabled(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::setTranslatorEnabled()
     * @param  bool $enabled
     * @return TreeRouteStack
     */
    public function setTranslatorEnabled($enabled = true)
    {
        $this->translatorEnabled = (bool) $enabled;
        return $this;
    }

    /**
     * isTranslatorEnabled(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::isTranslatorEnabled()
     * @return bool
     */
    public function isTranslatorEnabled()
    {
        return $this->translatorEnabled;
    }

    /**
     * setTranslatorTextDomain(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::setTranslatorTextDomain()
     * @param  string $textDomain
     * @return self
     */
    public function setTranslatorTextDomain($textDomain = 'default')
    {
        $this->translatorTextDomain = (string) $textDomain;
        return $this;
    }

    /**
     * getTranslatorTextDomain(): defined by TranslatorAwareInterface.
     *
     * @see    TranslatorAwareInterface::getTranslatorTextDomain()
     * @return string
     */
    public function getTranslatorTextDomain()
    {
        return $this->translatorTextDomain;
    }
}
