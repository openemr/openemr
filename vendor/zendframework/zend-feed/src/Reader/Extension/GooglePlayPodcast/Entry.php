<?php
/**
 * @see       https://github.com/zendframework/zend-feed for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-feed/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Feed\Reader\Extension\GooglePlayPodcast;

use Zend\Feed\Reader\Extension;

class Entry extends Extension\AbstractEntry
{
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
     * Get the episode summary/description
     *
     * Uses verbiage so it does not conflict with base entry.
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
