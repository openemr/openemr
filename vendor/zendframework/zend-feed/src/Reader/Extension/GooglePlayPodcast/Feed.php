<?php
/**
 * @see       https://github.com/zendframework/zend-feed for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-feed/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Feed\Reader\Extension\GooglePlayPodcast;

use DOMText;
use Zend\Feed\Reader\Extension;

class Feed extends Extension\AbstractFeed
{
    /**
     * Get the entry author
     *
     * @return string
     */
    public function getPlayPodcastAuthor()
    {
        if (isset($this->data['author'])) {
            return $this->data['author'];
        }

        $author = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/googleplay:author)');

        if (! $author) {
            $author = null;
        }

        $this->data['author'] = $author;

        return $this->data['author'];
    }

    /**
     * Get the entry block
     *
     * @return string
     */
    public function getPlayPodcastBlock()
    {
        if (isset($this->data['block'])) {
            return $this->data['block'];
        }

        $block = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/googleplay:block)');

        if (! $block) {
            $block = null;
        }

        $this->data['block'] = $block;

        return $this->data['block'];
    }

    /**
     * Get the entry category
     *
     * @return array|null
     */
    public function getPlayPodcastCategories()
    {
        if (isset($this->data['categories'])) {
            return $this->data['categories'];
        }

        $categoryList = $this->xpath->query($this->getXpathPrefix() . '/googleplay:category');

        $categories = [];

        if ($categoryList->length > 0) {
            foreach ($categoryList as $node) {
                $children = null;

                if ($node->childNodes->length > 0) {
                    $children = [];

                    foreach ($node->childNodes as $childNode) {
                        if (! ($childNode instanceof DOMText)) {
                            $children[$childNode->getAttribute('text')] = null;
                        }
                    }
                }

                $categories[$node->getAttribute('text')] = $children;
            }
        }

        if (! $categories) {
            $categories = null;
        }

        $this->data['categories'] = $categories;

        return $this->data['categories'];
    }

    /**
     * Get the entry explicit
     *
     * @return string
     */
    public function getPlayPodcastExplicit()
    {
        if (isset($this->data['explicit'])) {
            return $this->data['explicit'];
        }

        $explicit = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/googleplay:explicit)');

        if (! $explicit) {
            $explicit = null;
        }

        $this->data['explicit'] = $explicit;

        return $this->data['explicit'];
    }

    /**
     * Get the feed/podcast image
     *
     * @return string
     */
    public function getPlayPodcastImage()
    {
        if (isset($this->data['image'])) {
            return $this->data['image'];
        }

        $image = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/googleplay:image/@href)');

        if (! $image) {
            $image = null;
        }

        $this->data['image'] = $image;

        return $this->data['image'];
    }

    /**
     * Get the entry description
     *
     * @return string
     */
    public function getPlayPodcastDescription()
    {
        if (isset($this->data['description'])) {
            return $this->data['description'];
        }

        $description = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/googleplay:description)');

        if (! $description) {
            $description = null;
        }

        $this->data['description'] = $description;

        return $this->data['description'];
    }

    /**
     * Register googleplay namespace
     *
     */
    protected function registerNamespaces()
    {
        $this->xpath->registerNamespace('googleplay', 'http://www.google.com/schemas/play-podcasts/1.0');
    }
}
