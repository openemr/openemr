<?php

namespace RingCentral\SDK\Http;

use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class MultipartBuilder
{

    protected $_body = [];
    protected $_contents = [];
    protected $_boundary = null;

    public function setBoundary($boundary = '')
    {
        $this->_boundary = $boundary;
        return $this;
    }

    /**
     * @return null
     */
    public function boundary()
    {
        return $this->_boundary;
    }

    public function setBody(array $body = [])
    {
        $this->_body = $body;
        return $this;
    }

    public function body()
    {
        return $this->_body;
    }

    /**
     * Function always use provided $filename. In cases when it's empty, for string content or when name cannot be
     * automatically discovered the $filename will be set to attachment name.
     * If attachment name is not provided, it will be randomly generated.
     * @param resource|string|StreamInterface $content  StreamInterface/resource/string to send
     * @param string                          $filename Optional. Filename of attachment, can't be empty if content is string
     * @param array                           $headers  Optional. Associative array of custom headers
     * @param string                          $name     Optional. Form field name
     * @return $this
     */
    public function add($content, $filename = '', array $headers = [], $name = '')
    {

        $uri = '';

        if (!empty($filename)) {

            $uri = $filename;

        } elseif ($content instanceof StreamInterface) {

            $meta = $content->getMetadata('uri');

            if (substr($meta, 0, 6) !== 'php://') {
                $uri = $meta;
            }

        } elseif (is_resource($content)) {

            $meta = stream_get_meta_data($content);
            $uri = $meta['uri'];

        }

        $basename = basename($uri);

        if (empty($basename)) {
            throw new \InvalidArgumentException('File name was not provided and cannot be auto-discovered');
        }

        $name = !empty($name) ? $name : $basename;

        $element = [
            'contents' => $content,
            'name'     => $name
        ];

        // always set as defined or else it will be auto-discovered by Guzzle
        if (!empty($filename)) {
            $element['filename'] = $filename;
        }

        if (!empty($headers)) {
            $element['headers'] = $headers;
        }

        $contentKey = null;

        foreach ($headers as $k => $v) {
            if (strtolower($k) == 'content-type') {
                $contentKey = $k;
            }
        }

        if (empty($contentKey)) {

            if (is_string($content)) {

                // Automatically set
                $element['headers']['Content-Type'] = 'application/octet-stream';

            } elseif ($content instanceof StreamInterface) {

                $type = \GuzzleHttp\Psr7\MimeType::fromFilename($basename);

                if (!$type) {
                    throw new \InvalidArgumentException('Content-Type header was not provided and cannot be auto-discovered');
                }

            }

        }

        $this->_contents[] = $element;

        return $this;

    }

    public function contents()
    {
        return $this->_contents;
    }

    /**
     * @param string $uri
     * @param string $method
     * @return RequestInterface
     * @throws \InvalidArgumentException
     */
    public function request($uri, $method = 'POST')
    {

        $stream = $this->requestBody();
        $headers = ['Content-Type' => 'multipart/form-data; boundary=' . $stream->getBoundary()];

        return new Request($method, $uri, $headers, $stream);

    }

    /**
     * @return StreamInterface|MultipartStream
     */
    protected function requestBody()
    {

        $body = [
            [
                'name'     => 'json',
                'contents' => json_encode($this->_body),
                'headers'  => [
                    'Content-Type' => 'application/json'
                ],
                'filename' => 'request.json',
            ]
        ];

        $stream = new MultipartStream(array_merge($body, $this->_contents), $this->_boundary);

        return $stream;

    }

}