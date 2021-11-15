<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

/**
 * Renders <html> tag (both opening and closing) of a web page, to which some custom
 * attributes can be added dynamically.
 *
 * @author Nikola Posa <posa.nikola@gmail.com>
 */
class HtmlTag extends AbstractHtmlElement
{
    /**
     * Attributes for the <html> tag.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Whether to pre-set appropriate attributes in accordance
     * with the currently set DOCTYPE.
     *
     * @var bool
     */
    protected $useNamespaces = false;

    /**
     * @var bool
     */
    private $handledNamespaces = false;

    /**
     * Retrieve object instance; optionally add attributes.
     *
     * @param array $attribs
     * @return self
     */
    public function __invoke(array $attribs = [])
    {
        if (! empty($attribs)) {
            $this->setAttributes($attribs);
        }

        return $this;
    }

    /**
     * Set new attribute.
     *
     * @param string $attrName
     * @param string $attrValue
     * @return self
     */
    public function setAttribute($attrName, $attrValue)
    {
        $this->attributes[$attrName] = $attrValue;
        return $this;
    }

    /**
     * Add new or overwrite the existing attributes.
     *
     * @param array $attribs
     * @return self
     */
    public function setAttributes(array $attribs)
    {
        foreach ($attribs as $name => $value) {
            $this->setAttribute($name, $value);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param bool $useNamespaces
     * @return self
     */
    public function setUseNamespaces($useNamespaces)
    {
        $this->useNamespaces = (bool) $useNamespaces;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUseNamespaces()
    {
        return $this->useNamespaces;
    }

    /**
     * Render opening tag.
     *
     * @return string
     */
    public function openTag()
    {
        $this->handleNamespaceAttributes();

        return sprintf('<html%s>', $this->htmlAttribs($this->attributes));
    }

    protected function handleNamespaceAttributes()
    {
        if ($this->useNamespaces && ! $this->handledNamespaces) {
            if (method_exists($this->view, 'plugin')) {
                $doctypeAttributes = [];

                if ($this->view->plugin('doctype')->isXhtml()) {
                    $doctypeAttributes = ['xmlns' => 'http://www.w3.org/1999/xhtml'];
                }

                if (! empty($doctypeAttributes)) {
                    $this->attributes = array_merge($doctypeAttributes, $this->attributes);
                }
            }

            $this->handledNamespaces = true;
        }
    }

    /**
     * Render closing tag.
     *
     * @return string
     */
    public function closeTag()
    {
        return '</html>';
    }
}
