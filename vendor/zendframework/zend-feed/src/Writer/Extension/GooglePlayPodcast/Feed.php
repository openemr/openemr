<?php
/**
 * @see       https://github.com/zendframework/zend-zend-feed for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-zend-feed/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Feed\Writer\Extension\GooglePlayPodcast;

use Zend\Feed\Uri;
use Zend\Feed\Writer;
use Zend\Stdlib\StringUtils;
use Zend\Stdlib\StringWrapper\StringWrapperInterface;

class Feed
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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->stringWrapper = StringUtils::getWrapper($this->encoding);
    }

    /**
     * Set feed encoding
     *
     * @param  string $enc
     * @return Feed
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
     * @return Feed
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
        return $this;
    }

    /**
     * Add feed authors
     *
     * @param  array $values
     * @return Feed
     */
    public function addPlayPodcastAuthors(array $values)
    {
        foreach ($values as $value) {
            $this->addPlayPodcastAuthor($value);
        }
        return $this;
    }

    /**
     * Add feed author
     *
     * @param  string $value
     * @return Feed
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function addPlayPodcastAuthor($value)
    {
        if ($this->stringWrapper->strlen($value) > 255) {
            throw new Writer\Exception\InvalidArgumentException(
                'invalid parameter: any "author" may only contain a maximum of 255 characters each'
            );
        }
        if (! isset($this->data['authors'])) {
            $this->data['authors'] = [];
        }
        $this->data['authors'][] = $value;
        return $this;
    }

    /**
     * Set feed categories
     *
     * @param  array $values
     * @return Feed
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function setPlayPodcastCategories(array $values)
    {
        if (! isset($this->data['categories'])) {
            $this->data['categories'] = [];
        }
        foreach ($values as $key => $value) {
            if (! is_array($value)) {
                if ($this->stringWrapper->strlen($value) > 255) {
                    throw new Writer\Exception\InvalidArgumentException(
                        'invalid parameter: any "category" may only contain a maximum of 255 characters each'
                    );
                }
                $this->data['categories'][] = $value;
            } else {
                if ($this->stringWrapper->strlen($key) > 255) {
                    throw new Writer\Exception\InvalidArgumentException(
                        'invalid parameter: any "category" may only contain a maximum of 255 characters each'
                    );
                }
                $this->data['categories'][$key] = [];
                foreach ($value as $val) {
                    if ($this->stringWrapper->strlen($val) > 255) {
                        throw new Writer\Exception\InvalidArgumentException(
                            'invalid parameter: any "category" may only contain a maximum of 255 characters each'
                        );
                    }
                    $this->data['categories'][$key][] = $val;
                }
            }
        }
        return $this;
    }

    /**
     * Set feed image (icon)
     *
     * @param  string $value
     * @return Feed
     * @throws Writer\Exception\InvalidArgumentException
     */
    public function setPlayPodcastImage($value)
    {
        if (! is_string($value) || ! Uri::factory($value)->isValid()) {
            throw new Writer\Exception\InvalidArgumentException(
                'invalid parameter: "image" may only be a valid URI/IRI'
            );
        }
        $this->data['image'] = $value;
        return $this;
    }

    /**
     * Set "explicit" flag
     *
     * @param  bool $value
     * @return Feed
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
     * Set podcast description
     *
     * @param  string $value
     * @return Feed
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
     * Overloading: proxy to internal setters
     *
     * @param  string $method
     * @param  array $params
     * @return mixed
     * @throws Writer\Exception\BadMethodCallException
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

        if (! array_key_exists($point, $this->data) || empty($this->data[$point])) {
            return;
        }
        return $this->data[$point];
    }
}
