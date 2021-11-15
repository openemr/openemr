<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Client\Adapter;

use Laminas\Http\Client;
use Laminas\Http\Client\Adapter\Exception as AdapterException;
use Laminas\Http\Response;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\ErrorHandler;
use Traversable;

use function stream_context_set_option;

/**
 * HTTP Proxy-supporting Laminas\Http\Client adapter class, based on the default
 * socket based adapter.
 *
 * Should be used if proxy HTTP access is required. If no proxy is set, will
 * fall back to Laminas\Http\Client\Adapter\Socket behavior. Just like the
 * default Socket adapter, this adapter does not require any special extensions
 * installed.
 */
class Proxy extends Socket
{
    /**
     * Parameters array
     *
     * @var array
     */
    protected $config = [
        'persistent'         => false,
        'ssltransport'       => 'tls',
        'sslcert'            => null,
        'sslpassphrase'      => null,
        'sslverifypeer'      => true,
        'sslcafile'          => null,
        'sslcapath'          => null,
        'sslallowselfsigned' => false,
        'sslusecontext'      => false,
        'sslverifypeername'  => true,
        'proxy_host'         => '',
        'proxy_port'         => 8080,
        'proxy_user'         => '',
        'proxy_pass'         => '',
        'proxy_auth'         => Client::AUTH_BASIC,
    ];

    /**
     * Whether HTTPS CONNECT was already negotiated with the proxy or not
     *
     * @var bool
     */
    protected $negotiated = false;

    /**
     * Set the configuration array for the adapter
     *
     * @param array $options
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

        //enforcing that the proxy keys are set in the form proxy_*
        foreach ($options as $k => $v) {
            if (preg_match('/^proxy[a-z]+/', $k)) {
                $options['proxy_' . substr($k, 5, strlen($k))] = $v;
                unset($options[$k]);
            }
        }

        parent::setOptions($options);
    }

    /**
     * Connect to the remote server
     *
     * Will try to connect to the proxy server. If no proxy was set, will
     * fall back to the target server (behave like regular Socket adapter)
     *
     * @param string  $host
     * @param int     $port
     * @param  bool $secure
     * @throws AdapterException\RuntimeException
     */
    public function connect($host, $port = 80, $secure = false)
    {
        // If no proxy is set, fall back to Socket adapter
        if (! $this->config['proxy_host']) {
            parent::connect($host, $port, $secure);
            return;
        }

        /* Url might require stream context even if proxy connection doesn't */
        if ($secure) {
            $this->config['sslusecontext'] = true;
            $this->setSslCryptoMethod = false;
        }

        // Connect (a non-secure connection) to the proxy server
        parent::connect(
            $this->config['proxy_host'],
            $this->config['proxy_port'],
            false
        );
    }

    /**
     * Send request to the proxy server
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
        // If no proxy is set, fall back to default Socket adapter
        if (! $this->config['proxy_host']) {
            return parent::write($method, $uri, $httpVer, $headers, $body);
        }

        // Make sure we're properly connected
        if (! $this->socket) {
            throw new AdapterException\RuntimeException('Trying to write but we are not connected');
        }

        $host = $this->config['proxy_host'];
        $port = $this->config['proxy_port'];

        $isSecure = strtolower($uri->getScheme()) === 'https';
        $connectedHost = ($isSecure ? $this->config['ssltransport'] : 'tcp') . '://' . $host;

        if ($this->connectedTo[1] !== $port || $this->connectedTo[0] !== $connectedHost) {
            throw new AdapterException\RuntimeException(
                'Trying to write but we are connected to the wrong proxy server'
            );
        }

        // Add Proxy-Authorization header
        if ($this->config['proxy_user'] && ! isset($headers['proxy-authorization'])) {
            $headers['proxy-authorization'] = Client::encodeAuthHeader(
                $this->config['proxy_user'],
                $this->config['proxy_pass'],
                $this->config['proxy_auth']
            );
        }

        // if we are proxying HTTPS, preform CONNECT handshake with the proxy
        if ($isSecure && ! $this->negotiated) {
            $this->connectHandshake($uri->getHost(), $uri->getPort(), $httpVer, $headers);
            $this->negotiated = true;
        }

        // Save request method for later
        $this->method = $method;

        if ($uri->getUserInfo()) {
            $headers['Authorization'] = 'Basic ' . base64_encode($uri->getUserInfo());
        }

        $path = $uri->getPath();
        $query = $uri->getQuery();
        $path .= $query ? '?' . $query : '';

        if (! $this->negotiated) {
            $path = $uri->getScheme() . '://' . $uri->getHost() . $path;
        }

        // Build request headers
        $request = sprintf('%s %s HTTP/%s%s', $method, $path, $httpVer, "\r\n");

        // Add all headers to the request string
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
        if ($test === false) {
            throw new AdapterException\RuntimeException('Error writing request to proxy server', 0, $error);
        }

        if (is_resource($body)) {
            if (stream_copy_to_stream($body, $this->socket) == 0) {
                throw new AdapterException\RuntimeException('Error writing request to server');
            }
        }

        return $request;
    }

    /**
     * Preform handshaking with HTTPS proxy using CONNECT method
     *
     * @param string  $host
     * @param int $port
     * @param string  $httpVer
     * @param array   $headers
     * @throws AdapterException\RuntimeException
     */
    protected function connectHandshake($host, $port = 443, $httpVer = '1.1', array &$headers = [])
    {
        $request = 'CONNECT ' . $host . ':' . $port . ' HTTP/' . $httpVer . "\r\n"
            . 'Host: ' . $host . "\r\n";

        // Add the user-agent header
        if (isset($this->config['useragent'])) {
            $request .= 'User-agent: ' . $this->config['useragent'] . "\r\n";
        }

        // If the proxy-authorization header is set, send it to proxy but remove
        // it from headers sent to target host
        if (isset($headers['proxy-authorization'])) {
            $request .= 'Proxy-authorization: ' . $headers['proxy-authorization'] . "\r\n";
            unset($headers['proxy-authorization']);
        }

        $request .= "\r\n";

        // Send the request
        ErrorHandler::start();
        $test  = fwrite($this->socket, $request);
        $error = ErrorHandler::stop();
        if (! $test) {
            throw new AdapterException\RuntimeException('Error writing request to proxy server', 0, $error);
        }

        // Read response headers only
        $response = '';
        $gotStatus = false;
        ErrorHandler::start();
        while ($line = fgets($this->socket)) {
            $gotStatus = $gotStatus || (strpos($line, 'HTTP') !== false);
            if ($gotStatus) {
                $response .= $line;
                if (! rtrim($line)) {
                    break;
                }
            }
        }
        ErrorHandler::stop();

        // Check that the response from the proxy is 200
        if (Response::fromString($response)->getStatusCode() != 200) {
            throw new AdapterException\RuntimeException(sprintf(
                'Unable to connect to HTTPS proxy. Server response: %s',
                $response
            ));
        }

        // provide hostname to ssl for SNI
        $context = $this->getStreamContext();
        stream_context_set_option($context, 'ssl', 'peer_name', $host);

        try {
            $this->enableCryptoTransport($this->config['ssltransport'], $this->socket, $host);
        } catch (AdapterException\RuntimeException $e) {
            throw new AdapterException\RuntimeException(
                'Unable to connect to HTTPS server through proxy: could not negotiate secure connection.',
                0,
                $e
            );
        }
    }

    /**
     * Close the connection to the server
     */
    public function close()
    {
        parent::close();
        $this->negotiated = false;
    }

    /**
     * Destructor: make sure the socket is disconnected
     */
    public function __destruct()
    {
        if ($this->socket) {
            $this->close();
        }
    }
}
