<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Http;

use Zend\Feed\Reader\Exception;
use Zend\Http\Client as ZendHttpClient;
use Zend\Http\Headers;

class ZendHttpClientDecorator implements HeaderAwareClientInterface
{
    /**
     * @var ZendHttpClient
     */
    private $client;

    /**
     * @param ZendHttpClient $client
     */
    public function __construct(ZendHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return ZendHttpClient
     */
    public function getDecoratedClient()
    {
        return $this->client;
    }

    /**
     * {@inheritDoc}
     */
    public function get($uri, array $headers = [])
    {
        $this->client->resetParameters();
        $this->client->setMethod('GET');
        $this->client->setHeaders(new Headers());
        $this->client->setUri($uri);
        if (! empty($headers)) {
            $this->injectHeaders($headers);
        }
        $response = $this->client->send();

        return new Response(
            $response->getStatusCode(),
            $response->getBody(),
            $this->prepareResponseHeaders($response->getHeaders())
        );
    }

    /**
     * Inject header values into the client.
     *
     * @param array $headerValues
     */
    private function injectHeaders(array $headerValues)
    {
        $headers = $this->client->getRequest()->getHeaders();
        foreach ($headerValues as $name => $values) {
            if (! is_string($name) || is_numeric($name) || empty($name)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Header names provided to %s::get must be non-empty, non-numeric strings; received %s',
                    __CLASS__,
                    $name
                ));
            }

            if (! is_array($values)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Header values provided to %s::get must be arrays of values; received %s',
                    __CLASS__,
                    (is_object($values) ? get_class($values) : gettype($values))
                ));
            }

            foreach ($values as $value) {
                if (! is_string($value) && ! is_numeric($value)) {
                    throw new Exception\InvalidArgumentException(sprintf(
                        'Individual header values provided to %s::get must be strings or numbers; '
                        . 'received %s for header %s',
                        __CLASS__,
                        (is_object($value) ? get_class($value) : gettype($value)),
                        $name
                    ));
                }

                $headers->addHeaderLine($name, $value);
            }
        }
    }

    /**
     * Normalize headers to use with HeaderAwareResponseInterface.
     *
     * Ensures multi-value headers are represented as a single string, via
     * comma concatenation.
     *
     * @param Headers $headers
     * @return array
     */
    private function prepareResponseHeaders(Headers $headers)
    {
        $normalized = [];
        foreach ($headers->toArray() as $name => $value) {
            $normalized[$name] = is_array($value) ? implode(', ', $value) : $value;
        }
        return $normalized;
    }
}
