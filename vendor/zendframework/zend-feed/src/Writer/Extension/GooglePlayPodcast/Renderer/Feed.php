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

class Feed extends Extension\AbstractRenderer
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
     * Render feed
     *
     * @return void
     */
    public function render()
    {
        $this->_setAuthors($this->dom, $this->base);
        $this->_setBlock($this->dom, $this->base);
        $this->_setCategories($this->dom, $this->base);
        $this->_setImage($this->dom, $this->base);
        $this->_setExplicit($this->dom, $this->base);
        $this->_setDescription($this->dom, $this->base);
        if ($this->called) {
            $this->_appendNamespaces();
        }
    }

    /**
     * Append feed namespaces
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
     * Set feed authors
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _setAuthors(DOMDocument $dom, DOMElement $root)
    {
        // @codingStandardsIgnoreEnd
        $authors = $this->getDataContainer()->getPlayPodcastAuthors();
        if (! $authors || empty($authors)) {
            return;
        }
        foreach ($authors as $author) {
            $el = $dom->createElement('googleplay:author');
            $text = $dom->createTextNode($author);
            $el->appendChild($text);
            $root->appendChild($el);
        }
        $this->called = true;
    }

    /**
     * Set feed itunes block
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
     * Set feed categories
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _setCategories(DOMDocument $dom, DOMElement $root)
    {
        // @codingStandardsIgnoreEnd
        $cats = $this->getDataContainer()->getPlayPodcastCategories();
        if (! $cats || empty($cats)) {
            return;
        }
        foreach ($cats as $key => $cat) {
            if (! is_array($cat)) {
                $el = $dom->createElement('googleplay:category');
                $el->setAttribute('text', $cat);
                $root->appendChild($el);
            } else {
                $el = $dom->createElement('googleplay:category');
                $el->setAttribute('text', $key);
                $root->appendChild($el);
                foreach ($cat as $subcat) {
                    $el2 = $dom->createElement('googleplay:category');
                    $el2->setAttribute('text', $subcat);
                    $el->appendChild($el2);
                }
            }
        }
        $this->called = true;
    }

    /**
     * Set feed image (icon)
     *
     * @param  DOMDocument $dom
     * @param  DOMElement $root
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _setImage(DOMDocument $dom, DOMElement $root)
    {
        // @codingStandardsIgnoreEnd
        $image = $this->getDataContainer()->getPlayPodcastImage();
        if (! $image) {
            return;
        }
        $el = $dom->createElement('googleplay:image');
        $el->setAttribute('href', $image);
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
     * Set podcast description
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
