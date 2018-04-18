<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Config;

use SessionHandlerInterface;
use Zend\Session\Exception;

/**
 * Session configuration proxying to session INI options
 */
class SessionConfig extends StandardConfig
{
    /**
     * List of known PHP save handlers.
     *
     * @var null|array
     */
    protected $knownSaveHandlers;

    /**
     * Used with {@link handleError()}; stores PHP error code
     * @var int
     */
    protected $phpErrorCode    = false;

    /**
     * Used with {@link handleError()}; stores PHP error message
     * @var string
     */
    protected $phpErrorMessage = false;

    /**
     * @var int Default number of seconds to make session sticky, when rememberMe() is called
     */
    protected $rememberMeSeconds = 1209600; // 2 weeks

    /**
     * Name of the save handler currently in use. This will either be a PHP
     * built-in save handler name, or the name of a SessionHandlerInterface
     * class being used as a save handler.
     *
     * @var null|string
     */
    protected $saveHandler;

    /**
     * @var string session.serialize_handler
     */
    protected $serializeHandler;

    /**
     * @var array Valid cache limiters (per session.cache_limiter)
     */
    protected $validCacheLimiters = [
        '',
        'nocache',
        'public',
        'private',
        'private_no_expire',
    ];

    /**
     * @var array Valid hash bits per character (per session.hash_bits_per_character)
     */
    protected $validHashBitsPerCharacters = [
        4,
        5,
        6,
    ];

    /**
     * @var array Valid sid bits per character (per session.sid_bits_per_character)
     */
    protected $validSidBitsPerCharacters = [
        4,
        5,
        6,
    ];

    /**
     * @var array Valid hash functions (per session.hash_function)
     */
    protected $validHashFunctions;

    /**
     * Override standard option setting.
     *
     * Provides an overload for setting the save handler.
     *
     * {@inheritDoc}
     */
    public function setOption($option, $value)
    {
        switch (strtolower($option)) {
            case 'save_handler':
                $this->setPhpSaveHandler($value);
                return $this;
            default:
                return parent::setOption($option, $value);
        }
    }

    /**
     * Set storage option in backend configuration store
     *
     * @param  string $storageName
     * @param  mixed $storageValue
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setStorageOption($storageName, $storageValue)
    {
        switch ($storageName) {
            case 'remember_me_seconds':
                // do nothing; not an INI option
                return;
            case 'url_rewriter_tags':
                $key = 'url_rewriter.tags';
                break;
            case 'save_handler':
                // Save handlers must be treated differently due to changes
                // introduced in PHP 7.2. Do not alter running INI setting.
                return $this;
            default:
                $key = 'session.' . $storageName;
                break;
        }

        $iniGet       = ini_get($key);
        $storageValue = (string) $storageValue;
        if (false !== $iniGet && (string) $iniGet === $storageValue) {
            return $this;
        }

        $sessionRequiresRestart = false;
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
            $sessionRequiresRestart = true;
        }

        $result = ini_set($key, $storageValue);

        if ($sessionRequiresRestart) {
            session_start();
        }

        if (false === $result) {
            throw new Exception\InvalidArgumentException(
                "'{$key}' is not a valid sessions-related ini setting."
            );
        }
        return $this;
    }

    /**
     * Retrieve a storage option from a backend configuration store
     *
     * Used to retrieve default values from a backend configuration store.
     *
     * @param  string $storageOption
     * @return mixed
     */
    public function getStorageOption($storageOption)
    {
        switch ($storageOption) {
            case 'remember_me_seconds':
                // No remote storage option; just return the current value
                return $this->rememberMeSeconds;
            case 'url_rewriter_tags':
                return ini_get('url_rewriter.tags');
            // The following all need a transformation on the retrieved value;
            // however they use the same key naming scheme
            case 'use_cookies':
            case 'use_only_cookies':
            case 'use_trans_sid':
            case 'cookie_httponly':
                return (bool) ini_get('session.' . $storageOption);
            case 'save_handler':
                // Save handlers must be treated differently due to changes
                // introduced in PHP 7.2.
                return $this->saveHandler ?: session_module_name();
            default:
                return ini_get('session.' . $storageOption);
        }
    }

    /**
     * Proxy to setPhpSaveHandler()
     *
     * Prevents calls to `setSaveHandler()` from hitting `setOption()` instead,
     * and thus bypassing the logic of `setPhpSaveHandler()`.
     *
     * @param  string $phpSaveHandler
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setSaveHandler($phpSaveHandler)
    {
        return $this->setPhpSaveHandler($phpSaveHandler);
    }

    /**
     * Set session.save_handler
     *
     * @param  string $phpSaveHandler
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setPhpSaveHandler($phpSaveHandler)
    {
        $this->saveHandler = $this->performSaveHandlerUpdate($phpSaveHandler);
        $this->options['save_handler'] = $this->saveHandler;
        return $this;
    }

    /**
     * Set session.save_path
     *
     * @param  string $savePath
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException on invalid path
     */
    public function setSavePath($savePath)
    {
        if ($this->getOption('save_handler') === 'files') {
            parent::setSavePath($savePath);
        }
        $this->savePath = $savePath;
        $this->setOption('save_path', $savePath);
        return $this;
    }

    /**
     * Set session.serialize_handler
     *
     * @param  string $serializeHandler
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setSerializeHandler($serializeHandler)
    {
        $serializeHandler = (string) $serializeHandler;

        set_error_handler([$this, 'handleError']);
        ini_set('session.serialize_handler', $serializeHandler);
        restore_error_handler();
        if ($this->phpErrorCode >= E_WARNING) {
            throw new Exception\InvalidArgumentException('Invalid serialize handler specified');
        }

        $this->serializeHandler = (string) $serializeHandler;
        return $this;
    }

    // session.cache_limiter

    /**
     * Set cache limiter
     *
     * @param $cacheLimiter
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setCacheLimiter($cacheLimiter)
    {
        $cacheLimiter = (string) $cacheLimiter;
        if (! in_array($cacheLimiter, $this->validCacheLimiters)) {
            throw new Exception\InvalidArgumentException('Invalid cache limiter provided');
        }
        $this->setOption('cache_limiter', $cacheLimiter);
        ini_set('session.cache_limiter', $cacheLimiter);
        return $this;
    }

    /**
     * Set session.hash_function
     *
     * @param  string|int $hashFunction
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setHashFunction($hashFunction)
    {
        if (PHP_VERSION_ID >= 70100) {
            trigger_error('session.hash_function is removed starting with PHP 7.1', E_USER_DEPRECATED);
        }

        $hashFunction = (string) $hashFunction;
        $validHashFunctions = $this->getHashFunctions();
        if (! in_array($hashFunction, $validHashFunctions, true)) {
            throw new Exception\InvalidArgumentException('Invalid hash function provided');
        }

        $this->setOption('hash_function', $hashFunction);
        ini_set('session.hash_function', $hashFunction);
        return $this;
    }

    /**
     * Set session.hash_bits_per_character
     *
     * @param  int $hashBitsPerCharacter
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setHashBitsPerCharacter($hashBitsPerCharacter)
    {
        if (PHP_VERSION_ID >= 70100) {
            trigger_error('session.hash_bits_per_character is removed starting with PHP 7.1', E_USER_DEPRECATED);
        }

        if (! is_numeric($hashBitsPerCharacter)
            || ! in_array($hashBitsPerCharacter, $this->validHashBitsPerCharacters)
        ) {
            throw new Exception\InvalidArgumentException('Invalid hash bits per character provided');
        }

        $hashBitsPerCharacter = (int) $hashBitsPerCharacter;
        $this->setOption('hash_bits_per_character', $hashBitsPerCharacter);
        ini_set('session.hash_bits_per_character', $hashBitsPerCharacter);
        return $this;
    }

    /**
     * Set session.sid_bits_per_character
     *
     * @param  int $sidBitsPerCharacter
     * @return SessionConfig
     * @throws Exception\InvalidArgumentException
     */
    public function setSidBitsPerCharacter($sidBitsPerCharacter)
    {
        if (! is_numeric($sidBitsPerCharacter)
            || ! in_array($sidBitsPerCharacter, $this->validSidBitsPerCharacters)
        ) {
            throw new Exception\InvalidArgumentException('Invalid sid bits per character provided');
        }

        $sidBitsPerCharacter = (int) $sidBitsPerCharacter;
        $this->setOption('sid_bits_per_character', $sidBitsPerCharacter);
        ini_set('session.sid_bits_per_character', $sidBitsPerCharacter);
        return $this;
    }

    /**
     * Retrieve list of valid hash functions
     *
     * @return array
     */
    protected function getHashFunctions()
    {
        if (empty($this->validHashFunctions)) {
            /**
             * @link http://php.net/manual/en/session.configuration.php#ini.session.hash-function
             * "0" and "1" refer to MD5-128 and SHA1-160, respectively, and are
             * valid in addition to whatever is reported by hash_algos()
             */
            $this->validHashFunctions = ['0', '1'] + hash_algos();
        }
        return $this->validHashFunctions;
    }

    /**
     * Handle PHP errors
     *
     * @param  int $code
     * @param  string $message
     * @return void
     */
    protected function handleError($code, $message)
    {
        $this->phpErrorCode    = $code;
        $this->phpErrorMessage = $message;
    }

    /**
     * Determine what save handlers are available.
     *
     * The only way to get at this information is via phpinfo(), and the output
     * of that function varies based on the SAPI.
     *
     * Strips the handler "user" from the list, as PHP 7.2 does not allow
     * setting that as a handler, because it essentially requires you to have
     * already set a custom handler via `session_set_save_handler()`. It
     * wasn't really valid in prior versions, either; the language simply did
     * not complain previously.
     *
     * @return array
     */
    private function locateRegisteredSaveHandlers()
    {
        if (null !== $this->knownSaveHandlers) {
            return $this->knownSaveHandlers;
        }

        if (! preg_match('#Registered save handlers.*#m', $this->getPhpInfoForModules(), $matches)) {
            $this->knownSaveHandlers = [];
            return $this->knownSaveHandlers;
        }

        $content = array_shift($matches);

        $handlers = strstr($content, '</td>')
            ? $this->parseSaveHandlersFromHtml($content)
            : $this->parseSaveHandlersFromPlainText($content);

        if (false !== ($index = array_search('user', $handlers, true))) {
            unset($handlers[$index]);
        }

        $this->knownSaveHandlers = $handlers;

        return $this->knownSaveHandlers;
    }

    /**
     * Perform a session.save_handler update.
     *
     * Determines if the save handler represents a PHP built-in
     * save handler, and, if so, passes that value to session_module_name
     * in order to activate it. The save handler name is then returned.
     *
     * If it is not, it tests to see if it is a SessionHandlerInterface
     * implementation. If the string is a class implementing that interface,
     * it creates an instance of it. In such cases, it then calls
     * session_set_save_handler to activate it. The class name of the
     * handler is returned.
     *
     * In all other cases, an exception is raised.
     *
     * @param string|SessionHandlerInterface $phpSaveHandler
     * @return string
     * @throws Exception\InvalidArgumentException if an error occurs when
     *     setting a PHP session save handler module.
     * @throws Exception\InvalidArgumentException if the $phpSaveHandler
     *     is a string that does not represent a class implementing
     *     SessionHandlerInterface.
     * @throws Exception\InvalidArgumentException if $phpSaveHandler is
     *     a non-string value that does not implement SessionHandlerInterface.
     */
    private function performSaveHandlerUpdate($phpSaveHandler)
    {
        $knownHandlers = $this->locateRegisteredSaveHandlers();

        if (in_array($phpSaveHandler, $knownHandlers, true)) {
            $phpSaveHandler = strtolower($phpSaveHandler);
            set_error_handler([$this, 'handleError']);
            session_module_name($phpSaveHandler);
            restore_error_handler();
            if ($this->phpErrorCode >= E_WARNING) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Error setting session save handler module "%s": %s',
                    $phpSaveHandler,
                    $this->phpErrorMessage
                ));
            }

            return $phpSaveHandler;
        }

        if (is_string($phpSaveHandler)
            && (! class_exists($phpSaveHandler)
                || ! (in_array(SessionHandlerInterface::class, class_implements($phpSaveHandler)))
            )
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid save handler specified ("%s"); must be one of [%s]'
                . ' or a class implementing %s',
                $phpSaveHandler,
                implode(', ', $knownHandlers),
                SessionHandlerInterface::class,
                SessionHandlerInterface::class
            ));
        }

        if (is_string($phpSaveHandler)) {
            $phpSaveHandler = new $phpSaveHandler();
        }

        if (! $phpSaveHandler instanceof SessionHandlerInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid save handler specified ("%s"); must implement %s',
                get_class($phpSaveHandler),
                SessionHandlerInterface::class
            ));
        }

        session_set_save_handler($phpSaveHandler);

        return get_class($phpSaveHandler);
    }

    /**
     * Grab module information from phpinfo.
     *
     * Requires capturing an output buffer, as phpinfo does not have an option
     * to return the value as a string.
     *
     * @return string
     */
    private function getPhpInfoForModules()
    {
        ob_start();
        phpinfo(INFO_MODULES);
        return ob_get_clean();
    }

    /**
     * Parse a list of PHP session save handlers from HTML.
     *
     * Format is "<tr><td class="e">Registered save handlers</td><td class="v">{handlers}</td></tr>".
     *
     * @param string $content
     * @return array
     */
    private function parseSaveHandlersFromHtml($content)
    {
        if (! preg_match('#<td class="v">(?P<handlers>[^<]+)</td>#', $content, $matches)) {
            return [];
        }

        $handlers = trim($matches['handlers']);
        return preg_split('#\s+#', $handlers);
    }

    /**
     * Parse a list of PHP session save handlers from plain text.
     *
     * Format is "Registered save handlers => <handlers>".
     *
     * @param string $content
     * @return array
     */
    private function parseSaveHandlersFromPlainText($content)
    {
        list($prefix, $handlers) = explode('=>', $content);
        $handlers = trim($handlers);
        return preg_split('#\s+#', $handlers);
    }
}
