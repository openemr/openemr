<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

class HtmlQuicktime extends AbstractHtmlElement
{
    /**
     * Default file type for a movie applet
     */
    const TYPE = 'video/quicktime';

    /**
     * Object classid
     */
    const ATTRIB_CLASSID  = 'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B';

    /**
     * Object Codebase
     */
    const ATTRIB_CODEBASE = 'http://www.apple.com/qtactivex/qtplugin.cab';

    /**
     * Default attributes
     *
     * @var array
     */
    protected $attribs = ['classid' => self::ATTRIB_CLASSID, 'codebase' => self::ATTRIB_CODEBASE];

    /**
     * Output a quicktime movie object tag
     *
     * @param  string $data    The quicktime file
     * @param  array  $attribs Attribs for the object tag
     * @param  array  $params  Params for in the object tag
     * @param  string $content Alternative content
     * @return string
     */
    public function __invoke($data, array $attribs = [], array $params = [], $content = null)
    {
        // Attrs
        $attribs = array_merge($this->attribs, $attribs);

        // Params
        $params = array_merge(['src' => $data], $params);

        $htmlObject = $this->getView()->plugin('htmlObject');
        return $htmlObject($data, self::TYPE, $attribs, $params, $content);
    }
}
