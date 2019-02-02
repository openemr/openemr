<?php
/**
 * @see       https://github.com/zendframework/zend-feed for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-feed/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Feed\Writer\Extension\GooglePlayPodcast\Renderer;

use DOMDocument;
use DOMElement;
use Zend\Feed\Writer\Extension;

class Entry extends Extension\AbstractRenderer
{
    /**
     * Set to TRUE if a rendering method actually renders something. This
     * is used to prevent premature appending of a XML namespace declaration
     * until an element which requires it is actually appended.
     *
     * @var bool
     */
    protected $called = false;

    /**
     * Render entry
     *
     * @return void
     */
    public function render()
    {
        $this->_setBlock($this->dom, $this->base);
        $this->_setExplicit($this->dom, $this->base);
        $this->_setDescription($this->dom, $this->base);
        if ($this->called) {
            $this->_appendNamespaces();
        }
    }

    /**
     * Append namespaces to entry root
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _appendNamespaces()
    {
        // @codingStandardsIgnoreEnd
        $this->getRootElement()->setAttribute(
            'xmlns:googleplay',
            'http://www.google.com/schemas/play-podcasts/1.0'
        );
    }

    /**
     * Set itunes block
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _setBlock(DOMDocument $dom, DOMElement $root)
    {
        // @codingStandardsIgnoreEnd
        $block = $this->getDataContainer()->getPlayPodcastBlock();
        if ($block === null) {
            return;
        }
        $el = $dom->createElement('googleplay:block');
        $text = $dom->createTextNode($block);
        $el->appendChild($text);
        $root->appendChild($el);
        $this->called = true;
    }

    /**
     * Set explicit flag
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _setExplicit(DOMDocument $dom, DOMElement $root)
    {
        // @codingStandardsIgnoreEnd
        $explicit = $this->getDataContainer()->getPlayPodcastExplicit();
        if ($explicit === null) {
            return;
        }
        $el = $dom->createElement('googleplay:explicit');
        $text = $dom->createTextNode($explicit);
        $el->appendChild($text);
        $root->appendChild($el);
        $this->called = true;
    }

    /**
     * Set episode description
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _setDescription(DOMDocument $dom, DOMElement $root)
    {
        // @codingStandardsIgnoreEnd
        $description = $this->getDataContainer()->getPlayPodcastDescription();
        if (! $description) {
            return;
        }
        $el = $dom->createElement('googleplay:description');
        $text = $dom->createTextNode($description);
        $el->appendChild($text);
        $root->appendChild($el);
        $this->called = true;
    }
}
