<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Translator;

use Laminas\Cache;
use Laminas\Cache\Storage\StorageInterface as CacheStorage;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\I18n\Exception;
use Laminas\I18n\Translator\Loader\FileLoaderInterface;
use Laminas\I18n\Translator\Loader\RemoteLoaderInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Locale;
use Traversable;

/**
 * Translator.
 */
class Translator implements TranslatorInterface
{
    /**
     * Event fired when the translation for a message is missing.
     */
    const EVENT_MISSING_TRANSLATION = 'missingTranslation';

    /**
     * Event fired when no messages were loaded for a locale/text-domain combination.
     */
    const EVENT_NO_MESSAGES_LOADED = 'noMessagesLoaded';

    /**
     * Messages loaded by the translator.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Files used for loading messages.
     *
     * @var array
     */
    protected $files = [];

    /**
     * Patterns used for loading messages.
     *
     * @var array
     */
    protected $patterns = [];

    /**
     * Remote locations for loading messages.
     *
     * @var array
     */
    protected $remote = [];

    /**
     * Default locale.
     *
     * @var string|null
     */
    protected $locale;

    /**
     * Locale to use as fallback if there is no translation.
     *
     * @var string|null
     */
    protected $fallbackLocale;

    /**
     * Translation cache.
     *
     * @var CacheStorage|null
     */
    protected $cache;

    /**
     * Plugin manager for translation loaders.
     *
     * @var LoaderPluginManager
     */
    protected $pluginManager;

    /**
     * Event manager for triggering translator events.
     *
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * Whether events are enabled
     *
     * @var bool
     */
    protected $eventsEnabled = false;

    /**
     * Instantiate a translator
     *
     * @param  array|Traversable $options
     * @return static
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (! is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable object; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        $translator = new static();

        // locales
        if (isset($options['locale'])) {
            $locales = (array) $options['locale'];
            $translator->setLocale(array_shift($locales));
            if ($locales) {
                $translator->setFallbackLocale(array_shift($locales));
            }
        }

        // file patterns
        if (isset($options['translation_file_patterns'])) {
            if (! is_array($options['translation_file_patterns'])) {
                throw new Exception\InvalidArgumentException(
                    '"translation_file_patterns" should be an array'
                );
            }

            $requiredKeys = ['type', 'base_dir', 'pattern'];
            foreach ($options['translation_file_patterns'] as $pattern) {
                foreach ($requiredKeys as $key) {
                    if (! isset($pattern[$key])) {
                        throw new Exception\InvalidArgumentException(
                            "'{$key}' is missing for translation pattern options"
                        );
                    }
                }

                $translator->addTranslationFilePattern(
                    $pattern['type'],
                    $pattern['base_dir'],
                    $pattern['pattern'],
                    isset($pattern['text_domain']) ? $pattern['text_domain'] : 'default'
                );
            }
        }

        // files
        if (isset($options['translation_files'])) {
            if (! is_array($options['translation_files'])) {
                throw new Exception\InvalidArgumentException(
                    '"translation_files" should be an array'
                );
            }

            $requiredKeys = ['type', 'filename'];
            foreach ($options['translation_files'] as $file) {
                foreach ($requiredKeys as $key) {
                    if (! isset($file[$key])) {
                        throw new Exception\InvalidArgumentException(
                            "'{$key}' is missing for translation file options"
                        );
                    }
                }

                $translator->addTranslationFile(
                    $file['type'],
                    $file['filename'],
                    isset($file['text_domain']) ? $file['text_domain'] : 'default',
                    isset($file['locale']) ? $file['locale'] : null
                );
            }
        }

        // remote
        if (isset($options['remote_translation'])) {
            if (! is_array($options['remote_translation'])) {
                throw new Exception\InvalidArgumentException(
                    '"remote_translation" should be an array'
                );
            }

            $requiredKeys = ['type'];
            foreach ($options['remote_translation'] as $remote) {
                foreach ($requiredKeys as $key) {
                    if (! isset($remote[$key])) {
                        throw new Exception\InvalidArgumentException(
                            "'{$key}' is missing for remote translation options"
                        );
                    }
                }

                $translator->addRemoteTranslations(
                    $remote['type'],
                    isset($remote['text_domain']) ? $remote['text_domain'] : 'default'
                );
            }
        }

        // cache
        if (isset($options['cache'])) {
            if ($options['cache'] instanceof CacheStorage) {
                $translator->setCache($options['cache']);
            } else {
                $translator->setCache(Cache\StorageFactory::factory($options['cache']));
            }
        }

        // event manager enabled
        if (isset($options['event_manager_enabled']) && $options['event_manager_enabled']) {
            $translator->enableEventManager();
        }

        return $translator;
    }

    /**
     * Set the default locale.
     *
     * @param  string|null $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the default locale.
     *
     * @return string
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present and no locale set
     */
    public function getLocale()
    {
        if ($this->locale === null) {
            if (! extension_loaded('intl')) {
                throw new Exception\ExtensionNotLoadedException(sprintf(
                    '%s component requires the intl PHP extension',
                    __NAMESPACE__
                ));
            }
            $this->locale = Locale::getDefault();
        }

        return $this->locale;
    }

    /**
     * Set the fallback locale.
     *
     * @param  string|null $locale
     * @return $this
     */
    public function setFallbackLocale($locale)
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * Get the fallback locale.
     *
     * @return string|null
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    /**
     * Sets a cache
     *
     * @param  CacheStorage|null $cache
     * @return $this
     */
    public function setCache(CacheStorage $cache = null)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Returns the set cache
     *
     * @return CacheStorage|null The set cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set the plugin manager for translation loaders
     *
     * @param  LoaderPluginManager $pluginManager
     * @return $this
     */
    public function setPluginManager(LoaderPluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;

        return $this;
    }

    /**
     * Retrieve the plugin manager for translation loaders.
     *
     * Lazy loads an instance if none currently set.
     *
     * @return LoaderPluginManager
     */
    public function getPluginManager()
    {
        if (! $this->pluginManager instanceof LoaderPluginManager) {
            $this->setPluginManager(new LoaderPluginManager(new ServiceManager));
        }

        return $this->pluginManager;
    }

    /**
     * Translate a message.
     *
     * @param  string      $message
     * @param  string      $textDomain
     * @param  string|null $locale
     * @return string
     */
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        $locale      = ($locale ?: $this->getLocale());
        $translation = $this->getTranslatedMessage($message, $locale, $textDomain);

        if ($translation !== null && $translation !== '') {
            return $translation;
        }

        if (null !== ($fallbackLocale = $this->getFallbackLocale())
            && $locale !== $fallbackLocale
        ) {
            return $this->translate($message, $textDomain, $fallbackLocale);
        }

        return $message;
    }

    /**
     * Translate a plural message.
     *
     * @param  string      $singular
     * @param  string      $plural
     * @param  int         $number
     * @param  string      $textDomain
     * @param  string|null $locale
     * @return string
     * @throws Exception\OutOfBoundsException
     */
    public function translatePlural(
        $singular,
        $plural,
        $number,
        $textDomain = 'default',
        $locale = null
    ) {
        $locale      = $locale ?: $this->getLocale();
        $translation = $this->getTranslatedMessage($singular, $locale, $textDomain);

        if (is_string($translation)) {
            $translation = [$translation];
        }

        $index = ($number === 1) ? 0 : 1; // en_EN Plural rule
        if ($this->messages[$textDomain][$locale] instanceof TextDomain) {
            $index = $this->messages[$textDomain][$locale]
                ->getPluralRule()
                ->evaluate($number);
        }

        if (isset($translation[$index]) && $translation[$index] !== '' && $translation[$index] !== null) {
            return $translation[$index];
        }

        if (null !== ($fallbackLocale = $this->getFallbackLocale())
            && $locale !== $fallbackLocale
        ) {
            return $this->translatePlural(
                $singular,
                $plural,
                $number,
                $textDomain,
                $fallbackLocale
            );
        }

        return $index === 0 ? $singular : $plural;
    }

    /**
     * Get a translated message.
     *
     * @triggers getTranslatedMessage.missing-translation
     * @param    string $message
     * @param    string $locale
     * @param    string $textDomain
     * @return   string|null
     */
    protected function getTranslatedMessage(
        $message,
        $locale,
        $textDomain = 'default'
    ) {
        if ($message === '' || $message === null) {
            return '';
        }

        if (! isset($this->messages[$textDomain][$locale])) {
            $this->loadMessages($textDomain, $locale);
        }

        if (isset($this->messages[$textDomain][$locale][$message])) {
            return $this->messages[$textDomain][$locale][$message];
        }


        /**
         * issue https://github.com/zendframework/zend-i18n/issues/53
         *
         * storage: array:8 [▼
         *   "default\x04Welcome" => "Cześć"
         *   "default\x04Top %s Product" => array:3 [▼
         *     0 => "Top %s Produkt"
         *     1 => "Top %s Produkty"
         *     2 => "Top %s Produktów"
         *   ]
         *   "Top %s Products" => ""
         * ]
         */
        if (isset($this->messages[$textDomain][$locale][$textDomain . "\x04" . $message])) {
            return $this->messages[$textDomain][$locale][$textDomain . "\x04" . $message];
        }

        if ($this->isEventManagerEnabled()) {
            $until = static function ($r) {
                return is_string($r);
            };

            $event = new Event(self::EVENT_MISSING_TRANSLATION, $this, [
                'message'     => $message,
                'locale'      => $locale,
                'text_domain' => $textDomain,
            ]);

            $results = $this->getEventManager()->triggerEventUntil($until, $event);

            $last = $results->last();
            if (is_string($last)) {
                return $last;
            }
        }

        return null;
    }

    /**
     * Add a translation file.
     *
     * @param  string      $type
     * @param  string      $filename
     * @param  string      $textDomain
     * @param  string|null $locale
     * @return $this
     */
    public function addTranslationFile(
        $type,
        $filename,
        $textDomain = 'default',
        $locale = null
    ) {
        $locale = $locale ?: '*';

        if (! isset($this->files[$textDomain])) {
            $this->files[$textDomain] = [];
        }

        $this->files[$textDomain][$locale][] = [
            'type' => $type,
            'filename' => $filename,
        ];

        return $this;
    }

    /**
     * Add multiple translations with a file pattern.
     *
     * @param  string $type
     * @param  string $baseDir
     * @param  string $pattern
     * @param  string $textDomain
     * @return $this
     */
    public function addTranslationFilePattern(
        $type,
        $baseDir,
        $pattern,
        $textDomain = 'default'
    ) {
        if (! isset($this->patterns[$textDomain])) {
            $this->patterns[$textDomain] = [];
        }

        $this->patterns[$textDomain][] = [
            'type'    => $type,
            'baseDir' => rtrim($baseDir, '/'),
            'pattern' => $pattern,
        ];

        return $this;
    }

    /**
     * Add remote translations.
     *
     * @param  string $type
     * @param  string $textDomain
     * @return $this
     */
    public function addRemoteTranslations($type, $textDomain = 'default')
    {
        if (! isset($this->remote[$textDomain])) {
            $this->remote[$textDomain] = [];
        }

        $this->remote[$textDomain][] = $type;

        return $this;
    }

    /**
     * Get the cache identifier for a specific textDomain and locale.
     *
     * @param  string $textDomain
     * @param  string $locale
     * @return string
     */
    public function getCacheId($textDomain, $locale)
    {
        return 'Laminas_I18n_Translator_Messages_' . md5($textDomain . $locale);
    }

    /**
     * Clears the cache for a specific textDomain and locale.
     *
     * @param  string $textDomain
     * @param  string $locale
     * @return bool
     */
    public function clearCache($textDomain, $locale)
    {
        if (null === ($cache = $this->getCache())) {
            return false;
        }
        return $cache->removeItem($this->getCacheId($textDomain, $locale));
    }

    /**
     * Load messages for a given language and domain.
     *
     * @triggers loadMessages.no-messages-loaded
     * @param    string $textDomain
     * @param    string $locale
     * @throws   Exception\RuntimeException
     * @return   void
     */
    protected function loadMessages($textDomain, $locale)
    {
        if (! isset($this->messages[$textDomain])) {
            $this->messages[$textDomain] = [];
        }

        if (null !== ($cache = $this->getCache())) {
            $cacheId = $this->getCacheId($textDomain, $locale);

            if (null !== ($result = $cache->getItem($cacheId))) {
                $this->messages[$textDomain][$locale] = $result;

                return;
            }
        }

        $messagesLoaded  = false;
        $messagesLoaded |= $this->loadMessagesFromRemote($textDomain, $locale);
        $messagesLoaded |= $this->loadMessagesFromPatterns($textDomain, $locale);
        $messagesLoaded |= $this->loadMessagesFromFiles($textDomain, $locale);

        if (! $messagesLoaded) {
            $discoveredTextDomain = null;
            if ($this->isEventManagerEnabled()) {
                $until = static function ($r) {
                    return ($r instanceof TextDomain);
                };

                $event = new Event(self::EVENT_NO_MESSAGES_LOADED, $this, [
                    'locale'      => $locale,
                    'text_domain' => $textDomain,
                ]);

                $results = $this->getEventManager()->triggerEventUntil($until, $event);

                $last = $results->last();
                if ($last instanceof TextDomain) {
                    $discoveredTextDomain = $last;
                }
            }

            $this->messages[$textDomain][$locale] = $discoveredTextDomain;
            $messagesLoaded = true;
        }

        if ($messagesLoaded && $cache !== null) {
            $cache->setItem($cacheId, $this->messages[$textDomain][$locale]);
        }
    }

    /**
     * Load messages from remote sources.
     *
     * @param  string $textDomain
     * @param  string $locale
     * @return bool
     * @throws Exception\RuntimeException When specified loader is not a remote loader
     */
    protected function loadMessagesFromRemote($textDomain, $locale)
    {
        $messagesLoaded = false;

        if (isset($this->remote[$textDomain])) {
            foreach ($this->remote[$textDomain] as $loaderType) {
                $loader = $this->getPluginManager()->get($loaderType);

                if (! $loader instanceof RemoteLoaderInterface) {
                    throw new Exception\RuntimeException('Specified loader is not a remote loader');
                }

                if (isset($this->messages[$textDomain][$locale])) {
                    $this->messages[$textDomain][$locale]->merge($loader->load($locale, $textDomain));
                } else {
                    $this->messages[$textDomain][$locale] = $loader->load($locale, $textDomain);
                }

                $messagesLoaded = true;
            }
        }

        return $messagesLoaded;
    }

    /**
     * Load messages from patterns.
     *
     * @param  string $textDomain
     * @param  string $locale
     * @return bool
     * @throws Exception\RuntimeException When specified loader is not a file loader
     */
    protected function loadMessagesFromPatterns($textDomain, $locale)
    {
        $messagesLoaded = false;

        if (isset($this->patterns[$textDomain])) {
            foreach ($this->patterns[$textDomain] as $pattern) {
                $filename = $pattern['baseDir'] . '/' . sprintf($pattern['pattern'], $locale);

                if (is_file($filename)) {
                    $loader = $this->getPluginManager()->get($pattern['type']);

                    if (! $loader instanceof FileLoaderInterface) {
                        throw new Exception\RuntimeException('Specified loader is not a file loader');
                    }

                    if (isset($this->messages[$textDomain][$locale])) {
                        $this->messages[$textDomain][$locale]->merge($loader->load($locale, $filename));
                    } else {
                        $this->messages[$textDomain][$locale] = $loader->load($locale, $filename);
                    }

                    $messagesLoaded = true;
                }
            }
        }

        return $messagesLoaded;
    }

    /**
     * Load messages from files.
     *
     * @param  string $textDomain
     * @param  string $locale
     * @return bool
     * @throws Exception\RuntimeException When specified loader is not a file loader
     */
    protected function loadMessagesFromFiles($textDomain, $locale)
    {
        $messagesLoaded = false;

        foreach ([$locale, '*'] as $currentLocale) {
            if (! isset($this->files[$textDomain][$currentLocale])) {
                continue;
            }

            foreach ($this->files[$textDomain][$currentLocale] as $file) {
                $loader = $this->getPluginManager()->get($file['type']);

                if (! $loader instanceof FileLoaderInterface) {
                    throw new Exception\RuntimeException('Specified loader is not a file loader');
                }

                if (isset($this->messages[$textDomain][$locale])) {
                    $this->messages[$textDomain][$locale]->merge($loader->load($locale, $file['filename']));
                } else {
                    $this->messages[$textDomain][$locale] = $loader->load($locale, $file['filename']);
                }

                $messagesLoaded = true;
            }

            unset($this->files[$textDomain][$currentLocale]);
        }

        return $messagesLoaded;
    }

    /**
     * Return all the messages.
     *
     * @param string      $textDomain
     * @param string|null $locale
     * @return mixed
     */
    public function getAllMessages($textDomain = 'default', $locale = null)
    {
        $locale = $locale ?: $this->getLocale();

        if (! isset($this->messages[$textDomain][$locale])) {
            $this->loadMessages($textDomain, $locale);
        }

        return $this->messages[$textDomain][$locale];
    }

    /**
     * Get the event manager.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (! $this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * Set the event manager instance used by this translator.
     *
     * @param  EventManagerInterface $events
     * @return $this
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers([
            __CLASS__,
            get_class($this),
            'translator',
        ]);
        $this->events = $events;
        return $this;
    }

    /**
     * Check whether the event manager is enabled.
     *
     * @return bool
     */
    public function isEventManagerEnabled()
    {
        return $this->eventsEnabled;
    }

    /**
     * Enable the event manager.
     *
     * @return $this
     */
    public function enableEventManager()
    {
        $this->eventsEnabled = true;
        return $this;
    }

    /**
     * Disable the event manager.
     *
     * @return $this
     */
    public function disableEventManager()
    {
        $this->eventsEnabled = false;
        return $this;
    }
}
