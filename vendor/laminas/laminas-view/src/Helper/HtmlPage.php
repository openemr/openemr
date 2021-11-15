<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

class HtmlPage extends AbstractHtmlElement
{
    /**
     * Default file type for html
     */
    const TYPE = 'text/html';

    /**
     * Object classid
     */
    const ATTRIB_CLASSID  = 'clsid:25336920-03F9-11CF-8FD0-00AA00686F13';

    /**
     * Default attributes
     *
     * @var array
     */
    protected $attribs = ['classid' => self::ATTRIB_CLASSID];

    /**
     * Output a html object tag
     *
     * @param  string $data    The html url
     * @param  array  $attribs Attribs for the object tag
     * @param  array  $params  Params for in the object tag
     * @param  string $content Alternative content
     * @return string
     */
    public function __invoke($data, array $attribs = [], array $params = [], $content = null)
    {
        // Attribs
        $attribs = array_merge($this->attribs, $attribs);

        // Params
        $params = array_merge(['data' => $data], $params);

        $htmlObject = $this->getView()->plugin('htmlObject');
        return $htmlObject($data, self::TYPE, $attribs, $params, $content);
    }
}
