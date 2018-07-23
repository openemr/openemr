<?php
/**
 * @see       https://github.com/zendframework/zend-feed for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-feed/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Feed\Writer\Extension\GooglePlayPodcast;

use Zend\Feed\Uri;
use Zend\Feed\Writer;
use Zend\Stdlib\StringUtils;
use Zend\Stdlib\StringWrapper\StringWrapperInterface;

class Entry
{
    /**
     * Array of Feed data for rendering by Extension's renderers
     *
     * @var array
     */
    protected $data = [];

    /**
     * Encoding of all text values
     *
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * The used string wrapper supporting encoding
     *
     * @var StringWrapperInterface
     */
    protected $stringWrapper;

    public function __construct()
    {
        $this->stringWrapper = StringUtils::getWrapper($this->encoding);
    }

    /**
     * Set feed encoding
     *
     * @param  string $enc
     * @return Entry
     */
    public function setEncoding($enc)
    {
        $this->stringWrapper = StringUtils::getWrapper($enc);
        $this->encoding      = $enc;
        return $this;
    }

    /**
     * Get feed encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set a block value of "yes" or "no". You may also set an empty string.
     *
     * @param  string
     * @return Entry
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function setPlayPodcastBlock($value)
    {
        if (! ctype_alpha($value) && strlen($value) > 0) {
            throw new Writer\Exception\InvalidArgumentException(
                'invalid parameter: "block" may only contain alphabetic characters'
            );
        }

        if ($this->stringWrapper->strlen($value) > 255) {
            throw new Writer\Exception\InvalidArgumentException(
                'invalid parameter: "block" may only contain a maximum of 255 characters'
            );
        }
        $this->data['block'] = $value;
    }

    /**
     * Set "explicit" flag
     *
     * @param  bool $value
     * @return Entry
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function setPlayPodcastExplicit($value)
    {
        if (! in_array($value, ['yes', 'no', 'clean'], true)) {
            throw new Writer\Exception\InvalidArgumentException(
                'invalid parameter: "explicit" may only be one of "yes", "no" or "clean"'
            );
        }
        $this->data['explicit'] = $value;
        return $this;
    }

    /**
     * Set episode description
     *
     * @param  string $value
     * @return Entry
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function setPlayPodcastDescription($value)
    {
        if ($this->stringWrapper->strlen($value) > 4000) {
            throw new Writer\Exception\InvalidArgumentException(
                'invalid parameter: "description" may only contain a maximum of 4000 characters'
            );
        }
        $this->data['description'] = $value;
        return $this;
    }

    /**
     * Overloading to itunes specific setters
     *
     * @param  string $method
     * @param  array $params
     * @throws Writer\Exception\BadMethodCallException
     * @return mixed
     */
    public function __call($method, array $params)
    {
        $point = lcfirst(substr($method, 14));
        if (! method_exists($this, 'setPlayPodcast' . ucfirst($point))
            && ! method_exists($this, 'addPlayPodcast' . ucfirst($point))
        ) {
            throw new Writer\Exception\BadMethodCallException(
                'invalid method: ' . $method
            );
        }
        if (! array_key_exists($point, $this->data)
            || empty($this->data[$point])
        ) {
            return;
        }
        return $this->data[$point];
    }
}
