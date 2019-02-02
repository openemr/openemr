<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\PubSubHubbub;

use Traversable;
use Zend\Http\PhpEnvironment\Response as PhpResponse;
use Zend\Stdlib\ArrayUtils;

abstract class AbstractCallback implements CallbackInterface
{
    /**
     * An instance of Zend\Feed\Pubsubhubbub\Model\SubscriptionPersistenceInterface
     * used to background save any verification tokens associated with a subscription
     * or other.
     *
     * @var Model\SubscriptionPersistenceInterface
     */
    protected $storage = null;

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend\Feed\Pubsubhubbub\HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend\Controller\Response\Http.
     *
     * @var HttpResponse|PhpResponse
     */
    protected $httpResponse = null;

    /**
     * The input stream to use when retrieving the request body. Defaults to
     * php://input, but can be set to another value in order to force usage
     * of another input method. This should primarily be used for testing
     * purposes.
     *
     * @var string|resource String indicates a filename or stream to open;
     *     resource indicates an already created stream to use.
     */
    protected $inputStream = 'php://input';

    /**
     * The number of Subscribers for which any updates are on behalf of.
     *
     * @var int
     */
    protected $subscriberCount = 1;

    /**
     * Constructor; accepts an array or Traversable object to preset
     * options for the Subscriber without calling all supported setter
     * methods in turn.
     *
     * @param  array|Traversable $options Options array or Traversable object
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * Process any injected configuration options
     *
     * @param  array|Traversable $options Options array or Traversable object
     * @return AbstractCallback
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (! is_array($options)) {
            throw new Exception\InvalidArgumentException('Array or Traversable object'
            . 'expected, got ' . gettype($options));
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }

        if (array_key_exists('storage', $options)) {
            $this->setStorage($options['storage']);
        }
        return $this;
    }

    /**
     * Send the response, including all headers.
     * If you wish to handle this via Zend\Http, use the getter methods
     * to retrieve any data needed to be set on your HTTP Response object, or
     * simply give this object the HTTP Response instance to work with for you!
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->getHttpResponse()->send();
    }

    /**
     * Sets an instance of Zend\Feed\Pubsubhubbub\Model\SubscriptionPersistence used
     * to background save any verification tokens associated with a subscription
     * or other.
     *
     * @param  Model\SubscriptionPersistenceInterface $storage
     * @return AbstractCallback
     */
    public function setStorage(Model\SubscriptionPersistenceInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Gets an instance of Zend\Feed\Pubsubhubbub\Model\SubscriptionPersistence used
     * to background save any verification tokens associated with a subscription
     * or other.
     *
     * @return Model\SubscriptionPersistenceInterface
     * @throws Exception\RuntimeException
     */
    public function getStorage()
    {
        if ($this->storage === null) {
            throw new Exception\RuntimeException('No storage object has been'
                . ' set that subclasses Zend\Feed\Pubsubhubbub\Model\SubscriptionPersistence');
        }
        return $this->storage;
    }

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend\Feed\Pubsubhubbub\HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend\Controller\Response\Http.
     *
     * @param  HttpResponse|PhpResponse $httpResponse
     * @return AbstractCallback
     * @throws Exception\InvalidArgumentException
     */
    public function setHttpResponse($httpResponse)
    {
        if (! $httpResponse instanceof HttpResponse && ! $httpResponse instanceof PhpResponse) {
            throw new Exception\InvalidArgumentException('HTTP Response object must'
                . ' implement one of Zend\Feed\Pubsubhubbub\HttpResponse or'
                . ' Zend\Http\PhpEnvironment\Response');
        }
        $this->httpResponse = $httpResponse;
        return $this;
    }

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend\Feed\Pubsubhubbub\HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend\Controller\Response\Http.
     *
     * @return HttpResponse|PhpResponse
     */
    public function getHttpResponse()
    {
        if ($this->httpResponse === null) {
            $this->httpResponse = new HttpResponse;
        }
        return $this->httpResponse;
    }

    /**
     * Sets the number of Subscribers for which any updates are on behalf of.
     * In other words, is this class serving one or more subscribers? How many?
     * Defaults to 1 if left unchanged.
     *
     * @param  string|int $count
     * @return AbstractCallback
     * @throws Exception\InvalidArgumentException
     */
    public function setSubscriberCount($count)
    {
        $count = intval($count);
        if ($count <= 0) {
            throw new Exception\InvalidArgumentException('Subscriber count must be'
                . ' greater than zero');
        }
        $this->subscriberCount = $count;
        return $this;
    }

    /**
     * Gets the number of Subscribers for which any updates are on behalf of.
     * In other words, is this class serving one or more subscribers? How many?
     *
     * @return int
     */
    public function getSubscriberCount()
    {
        return $this->subscriberCount;
    }

    /**
     * Attempt to detect the callback URL (specifically the path forward)
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _detectCallbackUrl()
    {
        // @codingStandardsIgnoreEnd
        $callbackUrl = null;

        // IIS7 with URL Rewrite: make sure we get the unencoded url
        // (double slash problem).
        $iisUrlRewritten = isset($_SERVER['IIS_WasUrlRewritten']) ? $_SERVER['IIS_WasUrlRewritten'] : null;
        $unencodedUrl    = isset($_SERVER['UNENCODED_URL']) ? $_SERVER['UNENCODED_URL'] : null;
        if ('1' == $iisUrlRewritten && ! empty($unencodedUrl)) {
            return $unencodedUrl;
        }

        // HTTP proxy requests setup request URI with scheme and host [and port]
        // + the URL path, only use URL path.
        if (isset($_SERVER['REQUEST_URI'])) {
            $callbackUrl = $this->buildCallbackUrlFromRequestUri();
        }

        if (null !== $callbackUrl) {
            return $callbackUrl;
        }

        if (isset($_SERVER['ORIG_PATH_INFO'])) {
            return $this->buildCallbackUrlFromOrigPathInfo();
        }

        return '';
    }

    /**
     * Get the HTTP host
     *
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _getHttpHost()
    {
        // @codingStandardsIgnoreEnd
        if (! empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        $https  = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null;
        $scheme = $https === 'on' ? 'https' : 'http';
        $name   = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        $port   = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 80;

        if (($scheme === 'http' && $port === 80)
            || ($scheme === 'https' && $port === 443)
        ) {
            return $name;
        }

        return sprintf('%s:%d', $name, $port);
    }

    /**
     * Retrieve a Header value from either $_SERVER or Apache
     *
     * @param string $header
     * @return bool|string
     */
    // @codingStandardsIgnoreStart
    protected function _getHeader($header)
    {
        // @codingStandardsIgnoreEnd
        $temp = strtoupper(str_replace('-', '_', $header));
        if (! empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (! empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (! empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return false;
    }

    /**
     * Return the raw body of the request
     *
     * @return string|false Raw body, or false if not present
     */
    // @codingStandardsIgnoreStart
    protected function _getRawBody()
    {
        // @codingStandardsIgnoreEnd
        $body = is_resource($this->inputStream)
            ? stream_get_contents($this->inputStream)
            : file_get_contents($this->inputStream);

        return strlen(trim($body)) > 0 ? $body : false;
    }

    /**
     * Build the callback URL from the REQUEST_URI server parameter.
     *
     * @return string
     */
    private function buildCallbackUrlFromRequestUri()
    {
        $callbackUrl = $_SERVER['REQUEST_URI'];
        $https = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null;
        $scheme = $https === 'on' ? 'https' : 'http';
        if ($https === 'on') {
            $scheme = 'https';
        }
        $schemeAndHttpHost = $scheme . '://' . $this->_getHttpHost();
        if (strpos($callbackUrl, $schemeAndHttpHost) === 0) {
            $callbackUrl = substr($callbackUrl, strlen($schemeAndHttpHost));
        }
        return $callbackUrl;
    }

    /**
     * Build the callback URL from the ORIG_PATH_INFO server parameter.
     *
     * @return string
     */
    private function buildCallbackUrlFromOrigPathInfo()
    {
        $callbackUrl = $_SERVER['ORIG_PATH_INFO'];
        if (! empty($_SERVER['QUERY_STRING'])) {
            $callbackUrl .= '?' . $_SERVER['QUERY_STRING'];
        }
        return $callbackUrl;
    }
}
