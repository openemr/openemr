<?php
/**
 * @see       https://github.com/zendframework/zend-tag for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-tag/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Tag\Cloud\Decorator;

/**
 * Simple HTML decorator for clouds
 */
class HtmlCloud extends AbstractCloud
{
    /**
     * List of HTML tags
     *
     * @var array
     */
    protected $htmlTags = [
        'ul' => ['class' => 'zend-tag-cloud'],
    ];

    /**
     * Separator for the single tags
     *
     * @var string
     */
    protected $separator = ' ';

    /**
     * Set the HTML tags surrounding all tags
     *
     * @param  array $htmlTags
     * @return HTMLCloud
     */
    public function setHTMLTags(array $htmlTags)
    {
        $this->htmlTags = $htmlTags;
        return $this;
    }

    /**
     * Retrieve HTML tag map
     *
     * @return array
     */
    public function getHTMLTags()
    {
        return $this->htmlTags;
    }

    /**
     * Set the separator between the single tags
     *
     * @param  string
     * @return HTMLCloud
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * Get tag separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Defined by Zend\Tag\Cloud\Decorator\Cloud
     *
     * @param  array $tags
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function render($tags)
    {
        if (! is_array($tags)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'HtmlCloud::render() expects an array argument; received "%s"',
                (is_object($tags) ? get_class($tags) : gettype($tags))
            ));
        }
        $cloudHTML = implode($this->getSeparator(), $tags);
        $cloudHTML = $this->wrapTag($cloudHTML);
        return $cloudHTML;
    }
}
