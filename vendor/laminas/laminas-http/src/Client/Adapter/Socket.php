<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Client\Adapter;

use Laminas\Http\Client\Adapter\AdapterInterface as HttpAdapter;
use Laminas\Http\Client\Adapter\Exception as AdapterException;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\ErrorHandler;
use Traversable;

use function extension_loaded;
use function is_dir;
use function is_file;
use function openssl_error_string;
use function sprintf;
use function stream_socket_enable_crypto;
use function version_compare;

use const PHP_VERSION;
use const STREAM_CRYPTO_METHOD_SSLv3_CLIENT;
use const STREAM_CRYPTO_METHOD_TLS_CLIENT;
use const STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT;
use const STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
use const STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

/**
 * A sockets based (stream\socket\client) adapter class for Laminas\Http\Client. Can be used
 * on almost every PHP environment, and does not require any special extensions.
 */
class Socket implements HttpAdapter, StreamInterface
{
    /**
     * Map SSL transport wrappers to stream crypto method constants
     *
     * @var array
     */
    protected static $sslCryptoTypes = [
        'ssl'   => STREAM_CRYPTO_METHOD_SSLv23_CLIENT,
        'sslv2' => STREAM_CRYPTO_METHOD_SSLv2_CLIENT,
        'sslv3' => STREAM_CRYPTO_METHOD_SSLv3_CLIENT,
        'tls'   => STREAM_CRYPTO_METHOD_TLS_CLIENT,
    ];

    /**
     * The socket for server connection
     *
     * @var resource|null
     */
    protected $socket;

    /**
     * What host/port are we connected to?
     *
     * @var array
     */
    protected $connectedTo = [null, null];

    /**
     * Stream for storing output
     *
     * @var resource
     */
    protected $outStream;

    /**
     * Parameters array
     *
     * @var array
     */
    protected $config = [
        'persistent'            => false,
        'ssltransport'          => 'tls',
        'sslcert'               => null,
        'sslpassphrase'         => null,
        'sslverifypeer'         => true,
        'sslcafile'             => null,
        'sslcapath'             => null,
        'sslallowselfsigned'    => false,
        'sslusecontext'         => false,
        'sslverifypeername'     => true,
    ];

    /**
     * Request method - will be set by write() and might be used by read()
     *
     * @var string
     */
    protected $method;

    /**
     * Stream context
     *
     * @var resource
     */
    protected $context;

    /**
     * @var bool
     */
    protected $setSslCryptoMethod = true;

    /**
     * Adapter constructor, currently empty. Config is set using setOptions()
     *
     */
    public function __construct()
    {
    }

    /**
     * Set the configuration array for the adapter
     *
     * @param  array|Traversable $options
     * @throws AdapterException\InvalidArgumentException
     */
    public function setOptions($options = [])
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (! is_array($options)) {
            throw new AdapterException\InvalidArgumentException(
                'Array or Laminas\Config object expected, got ' . gettype($options)
            );
        }

        foreach ($options as $k => $v) {
            $this->config[strtolower($k)] = $v;
        }
    }

    /**
     * Retrieve the array of all configuration options
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the stream context for the TCP connection to the server
     *
     * Can accept either a pre-existing stream context resource, or an array
     * of stream options, similar to the options array passed to the
     * stream_context_create() PHP function. In such case a new stream context
     * will be created using the passed options.
     *
     * @since  Laminas 1.9
     *
     * @param  mixed $context Stream context or array of context options
     * @throws Exception\InvalidArgumentException
     * @return $this
     */
    public function setStreamContext($context)
    {
        if (is_resource($context) && get_resource_type($context) == 'stream-context') {
            $this->context = $context;
        } elseif (is_array($context)) {
            $this->context = stream_context_create($context);
        } else {
            // Invalid parameter
            throw new AdapterException\InvalidArgumentException(sprintf(
                'Expecting either a stream context resource or array, got %s',
                gettype($context)
            ));
        }

        return $this;
    }

    /**
     * Get the stream context for the TCP connection to the server.
     *
     * If no stream context is set, will create a default one.
     *
     * @return resource
     */
    public function getStreamContext()
    {
        if (! $this->context) {
            $this->context = stream_context_create();
        }

        return $this->context;
    }

    /**
     * Connect to the remote server
     *
     * @param string  $host
     * @param int     $port
     * @param  bool $secure
     * @throws AdapterException\RuntimeException
     */
    public function connect($host, $port = 80, $secure = false)
    {
        // If we are connected to the wrong host, disconnect first
        $connectedHost = (strpos($this->connectedTo[0], '://'))
            ? substr($this->connectedTo[0], (strpos($this->connectedTo[0], '://') + 3), strlen($this->connectedTo[0]))
            : $this->connectedTo[0];

        if ($connectedHost != $host || $this->connectedTo[1] != $port) {
            if (is_resource($this->socket)) {
                $this->close();
            }
        }

        // Now, if we are not connected, connect
        if (! is_resource($this->socket) || ! $this->config['keepalive']) {
            $context = $this->getStreamContext();

            if ($secure || $this->config['sslusecontext']) {
                if ($this->config['sslverifypeer'] !== null) {
                    if (! stream_context_set_option($context, 'ssl', 'verify_peer', $this->config['sslverifypeer'])) {
                        throw new AdapterException\RuntimeException('Unable to set sslverifypeer option');
                    }
                }

                if ($this->config['sslcafile']) {
                    if (! stream_context_set_option($context, 'ssl', 'cafile', $this->config['sslcafile'])) {
                        throw new AdapterException\RuntimeException('Unable to set sslcafile option');
                    }
                }

                if ($this->config['sslcapath']) {
                    if (! stream_context_set_option($context, 'ssl', 'capath', $this->config['sslcapath'])) {
                        throw new AdapterException\RuntimeException('Unable to set sslcapath option');
                    }
                }

                if ($this->config['sslallowselfsigned'] !== null) {
                    if (! stream_context_set_option(
                        $context,
                        'ssl',
                        'allow_self_signed',
                        $this->config['sslallowselfsigned']
                    )) {
                        throw new AdapterException\RuntimeException('Unable to set sslallowselfsigned option');
                    }
                }

                if ($this->config['sslcert'] !== null) {
                    if (! stream_context_set_option($context, 'ssl', 'local_cert', $this->config['sslcert'])) {
                        throw new AdapterException\RuntimeException('Unable to set sslcert option');
                    }
                }

                if ($this->config['sslpassphrase'] !== null) {
                    if (! stream_context_set_option($context, 'ssl', 'passphrase', $this->config['sslpassphrase'])) {
                        throw new AdapterException\RuntimeException('Unable to set sslpassphrase option');
                    }
                }

                if ($this->config['sslverifypeername'] !== null) {
                    if (! stream_context_set_option(
                        $context,
                        'ssl',
                        'verify_peer_name',
                        $this->config['sslverifypeername']
                    )) {
                        throw new AdapterException\RuntimeException('Unable to set sslverifypeername option');
                    }
                }
            }

            $flags = STREAM_CLIENT_CONNECT;
            if ($this->config['persistent']) {
                $flags |= STREAM_CLIENT_PERSISTENT;
            }

            if (isset($this->config['connecttimeout'])) {
                $connectTimeout = $this->config['connecttimeout'];
            } else {
                $connectTimeout = $this->config['timeout'];
            }

            if ($connectTimeout !== null && ! is_numeric($connectTimeout)) {
                throw new AdapterException\InvalidArgumentException(sprintf(
                    'integer or numeric string expected, got %s',
                    gettype($connectTimeout)
                ));
            }

            ErrorHandler::start();
            $this->socket = stream_socket_client(
                $host . ':' . $port,
                $errno,
                $errstr,
                (int) $connectTimeout,
                $flags,
                $context
            );
            $error = ErrorHandler::stop();

            if (! $this->socket) {
                $this->close();
                throw new AdapterException\RuntimeException(
                    sprintf(
                        'Unable to connect to %s:%d%s',
                        $host,
                        $port,
                        ($error ? ' . Error #' . $error->getCode() . ': ' . $error->getMessage() : '')
                    ),
                    0,
                    $error
                );
            }

            // Set the stream timeout
            if (! stream_set_timeout($this->socket, (int) $this->config['timeout'])) {
                throw new AdapterException\RuntimeException('Unable to set the connection timeout');
            }

            if ($secure || $this->config['sslusecontext']) {
                if ($this->setSslCryptoMethod) {
                    try {
                        $this->enableCryptoTransport($this->config['ssltransport'], $this->socket, $host);
                    } catch (AdapterException\RuntimeException $e) {
                        $this->close();
                        throw  $e;
                    }
                }

                $host = $this->config['ssltransport'] . '://' . $host;
            } else {
                $host = 'tcp://' . $host;
            }

            // Update connectedTo
            $this->connectedTo = [$host, $port];
        }
    }

    /**
     * @param string $sslTransport Transport name from $config['ssltransport']
     * @param resource $socket
     * @param string $host Host name used only for useful exception message
     */
    protected function enableCryptoTransport($sslTransport, $socket, $host)
    {
        $sslCryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (isset(static::$sslCryptoTypes[$sslTransport])) {
            $sslCryptoMethod = static::$sslCryptoTypes[$this->config['ssltransport']];
        }

        // Since php 5.6.7 and up to 7.2.0 constant means tls 1.0 only, expand back to all versions
        // We can do this because STREAM_CRYPTO_METHOD_TLS_ANY_CLIENT is available
        // in enum but not registered as php constant.
        // @see  https://github.com/php/php-src/blob/php-5.6.7/main/streams/php_stream_transport.h#L179
        if (version_compare(PHP_VERSION, '7.2.0', '<')
            && $sslCryptoMethod === STREAM_CRYPTO_METHOD_TLS_CLIENT
        ) {
            $sslCryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT;
            $sslCryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
            $sslCryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        }
        ErrorHandler::start();
        $test  = stream_socket_enable_crypto($socket, true, $sslCryptoMethod);
        $error = ErrorHandler::stop();
        if (! $test || $error) {
            // Error handling is kind of difficult when it comes to SSL
            $errorString = '';
            if (extension_loaded('openssl')) {
                while (($sslError = openssl_error_string()) != false) {
                    $errorString .= sprintf('; SSL error: %s', $sslError);
                }
            }

            if ((! $errorString) && $this->config['sslverifypeer']) {
                // There's good chance our error is due to sslcapath not being properly set
                if (! ($this->config['sslcafile'] || $this->config['sslcapath'])) {
                    $errorString = 'make sure the "sslcafile" or "sslcapath" option are properly set for '
                        . 'the environment.';
                } elseif ($this->config['sslcafile'] && ! is_file($this->config['sslcafile'])) {
                    $errorString = 'make sure the "sslcafile" option points to a valid SSL certificate '
                        . 'file';
                } elseif ($this->config['sslcapath'] && ! is_dir($this->config['sslcapath'])) {
                    $errorString = 'make sure the "sslcapath" option points to a valid SSL certificate '
                        . 'directory';
                }
            }

            if ($errorString) {
                $errorString = sprintf(': %s', $errorString);
            }

            throw new AdapterException\RuntimeException(
                sprintf(
                    'Unable to enable crypto on TCP connection %s%s',
                    $host,
                    $errorString
                ),
                0,
                $error
            );
        }
    }

    /**
     * Send request to the remote server
     *
     * @param string        $method
     * @param \Laminas\Uri\Uri $uri
     * @param string        $httpVer
     * @param array         $headers
     * @param string        $body
     * @throws AdapterException\RuntimeException
     * @return string Request as string
     */
    public function write($method, $uri, $httpVer = '1.1', $headers = [], $body = '')
    {
        // Make sure we're properly connected
        if (! $this->socket) {
            throw new AdapterException\RuntimeException('Trying to write but we are not connected');
        }

        $host = $uri->getHost();
        $host = (strtolower($uri->getScheme()) == 'https' ? $this->config['ssltransport'] : 'tcp') . '://' . $host;
        if ($this->connectedTo[0] != $host || $this->connectedTo[1] != $uri->getPort()) {
            throw new AdapterException\RuntimeException('Trying to write but we are connected to the wrong host');
        }

        // Save request method for later
        $this->method = $method;

        // Build request headers
        $path = $uri->getPath();
        $query = $uri->getQuery();
        $path .= $query ? '?' . $query : '';
        $request = $method . ' ' . $path . ' HTTP/' . $httpVer . "\r\n";
        foreach ($headers as $k => $v) {
            if (is_string($k)) {
                $v = $k . ': ' . $v;
            }
            $request .= $v . "\r\n";
        }

        if (is_resource($body)) {
            $request .= "\r\n";
        } else {
            // Add the request body
            $request .= "\r\n" . $body;
        }

        // Send the request
        ErrorHandler::start();
        $test  = fwrite($this->socket, $request);
        $error = ErrorHandler::stop();
        if (false === $test) {
            throw new AdapterException\RuntimeException('Error writing request to server', 0, $error);
        }

        if (is_resource($body)) {
            if (stream_copy_to_stream($body, $this->socket) == 0) {
                throw new AdapterException\RuntimeException('Error writing request to server');
            }
        }

        return $request;
    }

    /**
     * Read response from server
     *
     * @throws AdapterException\RuntimeException
     * @return string
     */
    public function read()
    {
        // First, read headers only
        $response = '';
        $gotStatus = false;

        while (($line = fgets($this->socket)) !== false) {
            $gotStatus = $gotStatus || (strpos($line, 'HTTP') !== false);
            if ($gotStatus) {
                $response .= $line;
                if (rtrim($line) === '') {
                    break;
                }
            }
        }

        $this->_checkSocketReadTimeout();

        $responseObj = Response::fromString($response);

        $statusCode = $responseObj->getStatusCode();

        // Handle 100 and 101 responses internally by restarting the read again
        if ($statusCode == 100 || $statusCode == 101) {
            return $this->read();
        }

        // Check headers to see what kind of connection / transfer encoding we have
        $headers = $responseObj->getHeaders();

        /**
         * Responses to HEAD requests and 204 or 304 responses are not expected
         * to have a body - stop reading here
         */
        if ($statusCode == 304
            || $statusCode == 204
            || $this->method == Request::METHOD_HEAD
        ) {
            // Close the connection if requested to do so by the server
            $connection = $headers->get('connection');
            if ($connection && $connection->getFieldValue() == 'close') {
                $this->close();
            }
            return $response;
        }

        // If we got a 'transfer-encoding: chunked' header
        $transferEncoding = $headers->get('transfer-encoding');
        $contentLength = $headers->get('content-length');
        if ($transferEncoding !== false) {
            if (strtolower($transferEncoding->getFieldValue()) == 'chunked') {
                do {
                    $line  = fgets($this->socket);
                    $this->_checkSocketReadTimeout();

                    $chunk = $line;

                    // Figure out the next chunk size
                    $chunksize = trim($line);
                    if (! ctype_xdigit($chunksize)) {
                        $this->close();
                        throw new AdapterException\RuntimeException(sprintf(
                            'Invalid chunk size "%s" unable to read chunked body',
                            $chunksize
                        ));
                    }

                    // Convert the hexadecimal value to plain integer
                    $chunksize = hexdec($chunksize);

                    // Read next chunk
                    $readTo = ftell($this->socket) + $chunksize;

                    do {
                        $currentPos = ftell($this->socket);
                        if ($currentPos >= $readTo) {
                            break;
                        }

                        if ($this->outStream) {
                            if (stream_copy_to_stream($this->socket, $this->outStream, $readTo - $currentPos) == 0) {
                                $this->_checkSocketReadTimeout();
                                break;
                            }
                        } else {
                            $line = fread($this->socket, $readTo - $currentPos);
                            if ($line === false || strlen($line) === 0) {
                                $this->_checkSocketReadTimeout();
                                break;
                            }
                            $chunk .= $line;
                        }
                    } while (! feof($this->socket));

                    ErrorHandler::start();
                    $chunk .= fgets($this->socket);
                    ErrorHandler::stop();
                    $this->_checkSocketReadTimeout();

                    if (! $this->outStream) {
                        $response .= $chunk;
                    }
                } while ($chunksize > 0);
            } else {
                $this->close();
                throw new AdapterException\RuntimeException(sprintf(
                    'Cannot handle "%s" transfer encoding',
                    $transferEncoding->getFieldValue()
                ));
            }

            // We automatically decode chunked-messages when writing to a stream
            // this means we have to disallow the Laminas\Http\Response to do it again
            if ($this->outStream) {
                $response = str_ireplace("Transfer-Encoding: chunked\r\n", '', $response);
            }
        // Else, if we got the content-length header, read this number of bytes
        } elseif ($contentLength !== false) {
            // If we got more than one Content-Length header (see Laminas-9404) use
            // the last value sent
            if (is_array($contentLength)) {
                $contentLength = $contentLength[count($contentLength) - 1];
            }
            $contentLength = $contentLength->getFieldValue();

            $currentPos = ftell($this->socket);

            for ($readTo = $currentPos + $contentLength;
                 $readTo > $currentPos;
                 $currentPos = ftell($this->socket)) {
                if ($this->outStream) {
                    if (stream_copy_to_stream($this->socket, $this->outStream, $readTo - $currentPos) == 0) {
                        $this->_checkSocketReadTimeout();
                        break;
                    }
                } else {
                    $chunk = fread($this->socket, $readTo - $currentPos);
                    if ($chunk === false || strlen($chunk) === 0) {
                        $this->_checkSocketReadTimeout();
                        break;
                    }

                    $response .= $chunk;
                }

                // Break if the connection ended prematurely
                if (feof($this->socket)) {
                    break;
                }
            }

        // Fallback: just read the response until EOF
        } else {
            do {
                if ($this->outStream) {
                    if (stream_copy_to_stream($this->socket, $this->outStream) == 0) {
                        $this->_checkSocketReadTimeout();
                        break;
                    }
                } else {
                    $buff = fread($this->socket, 8192);
                    if ($buff === false || strlen($buff) === 0) {
                        $this->_checkSocketReadTimeout();
                        break;
                    } else {
                        $response .= $buff;
                    }
                }
            } while (feof($this->socket) === false);

            $this->close();
        }

        // Close the connection if requested to do so by the server
        $connection = $headers->get('connection');
        if ($connection && $connection->getFieldValue() == 'close') {
            $this->close();
        }

        return $response;
    }

    /**
     * Close the connection to the server
     *
     */
    public function close()
    {
        if (is_resource($this->socket)) {
            ErrorHandler::start();
            fclose($this->socket);
            ErrorHandler::stop();
        }
        $this->socket = null;
        $this->connectedTo = [null, null];
    }

    /**
     * Check if the socket has timed out - if so close connection and throw
     * an exception
     *
     * @throws AdapterException\TimeoutException with READ_TIMEOUT code
     */
    // @codingStandardsIgnoreStart
    protected function _checkSocketReadTimeout()
    {
        // @codingStandardsIgnoreEnd
        if ($this->socket) {
            $info = stream_get_meta_data($this->socket);
            $timedout = $info['timed_out'];
            if ($timedout) {
                $this->close();
                throw new AdapterException\TimeoutException(
                    sprintf('Read timed out after %d seconds', $this->config['timeout']),
                    AdapterException\TimeoutException::READ_TIMEOUT
                );
            }
        }
    }

    /**
     * Set output stream for the response
     *
     * @param resource $stream
     * @return \Laminas\Http\Client\Adapter\Socket
     */
    public function setOutputStream($stream)
    {
        $this->outStream = $stream;
        return $this;
    }

    /**
     * Destructor: make sure the socket is disconnected
     *
     * If we are in persistent TCP mode, will not close the connection
     *
     */
    public function __destruct()
    {
        if (! $this->config['persistent']) {
            if ($this->socket) {
                $this->close();
            }
        }
    }
}
