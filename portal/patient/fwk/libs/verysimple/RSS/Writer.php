<?php

/** @package    verysimple::RSS */

require_once("verysimple/XML/XMLUtil.php");

/**
 * A API for writing RSS feeds
 *
 * @package verysimple::RSS
 * @link http://www.110mb.com/forum/how-to-make-a-rss-feed-with-php-t11030.0.html
 * @version 1.1
 * @example require_once('verysimple/RSS/Writer.php');
 *          $rss_writer = new RSS_Writer('Channel','www.dev.nul','Feed Title');
 *          $rss_writer->setLanguage('us-en');
 *          $rss_writer->addCategory("Category Name");
 *          $rss_writer->addItem("Title","http://...",'Category Name','Author',date(DATE_RSS,strtotime('2008-01-04: 01:01:01')));
 *          $rss_writer->writeOut();
 */
class RSS_Writer
{
    protected $channel;
    private $title;
    private $link;
    private $description;
    private $rss_dom;
    private $rss_simplexml;
    protected $item_list;
    protected $item_counter;

    /**
     * Instantiate an RSS_Writer
     *
     * @param string $channel_title
     * @param string $channel_link
     * @param string $channel_description
     * @param string $generator
     *          [optional]
     * @param string $docs
     *          [optional]
     */
    public function __construct($channel_title, $channel_link, $channel_description, $generator = "RSS_Writer", $docs = "http://cyber.law.harvard.edu/rss/rss.html")
    {
        $this->item_list = array ();
        $this->item_counter = 0;
        $this->rss_dom = new DOMDocument('1.0', 'UTF-8');
        $rss_node = $this->elementCreate($this->rss_dom, 'rss', '', array (
                'version' => '2.0'
        ));
        $this->channel = $this->elementCreate($rss_node, 'channel');
        $this->title = $this->elementCreate($this->channel, 'title', $channel_title);
        $this->link = $this->elementCreate($this->channel, 'link', $channel_link);
        $this->description = $this->elementCreate($this->channel, 'description', $channel_description);
        $this->elementCreate($this->channel, 'lastBuildDate', date(DATE_RSS), false, false);
        $this->elementCreate($this->channel, 'generator', $generator, false, false);
        $this->elementCreate($this->channel, 'docs', $docs, false, false);
        $this->rss_simplexml = simplexml_import_dom($this->rss_dom);
    }
    private function elementCreate($parent_node, $node_name, $content = '', $attributes = false, $return = true)
    {
        $content = XMLUtil::Escape($content);

        $element = $this->rss_dom->createElement($node_name, $content);
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $element->setAttribute($key, $value);
            }
        }

        $parent_node->appendChild($element);
        if ($return) {
            return $element;
        } else {
            return false;
        }
    }
    protected function elementSet($parent_node, $xpath, $node_name, $value, $attributes = false)
    {
        $result = $this->rss_simplexml->xpath($xpath . $node_name);
        if ($result) {
            $result [0] = $value;

            if ($attributes) {
                foreach ($attributes as $key => $value) {
                    $result [0] [$key] = $value;
                }
            }
        } else {
            $this->elementCreate($parent_node, $node_name, $value, $attributes, false);
        }
    }
    public function setLanguage($language = 'en-us')
    {
        $this->elementSet($this->channel, '/channel/', 'language', $language);
    }
    public function addCategory($category)
    {
        $this->elementCreate($this->channel, 'category', $category);
    }
    public function addImage($url, $width = 88, $height = 31, $description = 'channel image')
    {
        $image = $this->elementCreate($this->channel, 'image');
        $this->elementCreate($image, 'url', $url);

        if ($width > 144) {
            $width = 144;
        }

        if ($height > 400) {
            $height = 400;
        }

        $this->elementCreate($image, 'width', $width);
        $this->elementCreate($image, 'height', $height);

        $this->elementCreate($image, 'title', $this->title->nodeValue);
        $this->elementCreate($image, 'link', $this->link->nodeValue);

        $this->elementCreate($image, 'description', $description);
    }
    public function addItem($title, $link, $description, $author, $date, $source = '', $guid = '')
    {
        $this->item_counter ++;
        if (! $guid) {
            $guid = 'item' . $this->item_counter;
        }

        $item_element = $this->elementCreate($this->channel, 'item', '', false, true);
        $this->elementCreate($item_element, 'title', $title);
        $this->elementCreate($item_element, 'link', $link);
        $this->elementCreate($item_element, 'description', $description);
        $this->elementCreate($item_element, 'author', $author);
        $this->elementCreate($item_element, 'guid', $guid);
        $this->elementCreate($item_element, 'pubDate', $date);
        if ($source) {
            $this->elementCreate($item_element, 'source', $source);
        }

        $this->item_list [$this->item_counter] = $item_element;
    }
    public function addEnclosure($url, $type)
    {
        $item_element = $this->item_list [$this->item_counter];
        $curl = curl_init($url);
        if (! $curl || ! $item_element) {
            return false;
        } else {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_NOBODY, 1);
            curl_exec($curl);
            $size = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            curl_close($curl);
        }

        $this->elementCreate($item_element, 'enclosure', '', array (
                'url' => $url,
                'length' => $size,
                'type' => $type
        ), false);
    }

    /**
     * returns RSS xml
     *
     * @return string RSS
     *
     */
    public function returnXML()
    {
        return $this->rss_dom->saveXML();
    }

    /**
     * Writes out the XML header and content to the browser
     */
    public function writeOut()
    {
        header("Content-Type: text/xml");
        echo $this->returnXML();
    }
}
