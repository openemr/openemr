<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

abstract class AbstractHtmlElement extends AbstractHelper
{
    /**
     * EOL character
     *
     * @deprecated just use PHP_EOL
     */
    const EOL = PHP_EOL;

    /**
     * The tag closing bracket
     *
     * @var string
     */
    protected $closingBracket = null;

    /**
     * Get the tag closing bracket
     *
     * @return string
     */
    public function getClosingBracket()
    {
        if (! $this->closingBracket) {
            if ($this->isXhtml()) {
                $this->closingBracket = ' />';
            } else {
                $this->closingBracket = '>';
            }
        }

        return $this->closingBracket;
    }

    /**
     * Is doctype XHTML?
     *
     * @return bool
     */
    protected function isXhtml()
    {
        return $this->getView()->plugin('doctype')->isXhtml();
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * @access public
     *
     * @param array $attribs From this array, each key-value pair is
     * converted to an attribute name and value.
     *
     * @return string The XHTML for the attributes.
     */
    protected function htmlAttribs($attribs)
    {
        $xhtml          = '';
        $escaper        = $this->getView()->plugin('escapehtml');
        $escapeHtmlAttr = $this->getView()->plugin('escapehtmlattr');

        foreach ((array) $attribs as $key => $val) {
            $key = $escaper($key);

            if (0 === strpos($key, 'on') || ('constraints' == $key)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                if (! is_scalar($val)) {
                    // non-scalar data should be cast to JSON first
                    $val = \Laminas\Json\Json::encode($val);
                }
            } else {
                if (is_array($val)) {
                    $val = implode(' ', $val);
                }
            }

            $val = $escapeHtmlAttr($val);

            if ('id' == $key) {
                $val = $this->normalizeId($val);
            }

            if (strpos($val, '"') !== false) {
                $xhtml .= " $key='$val'";
            } else {
                $xhtml .= " $key=\"$val\"";
            }
        }

        return $xhtml;
    }

    /**
     * Normalize an ID
     *
     * @param  string $value
     * @return string
     */
    protected function normalizeId($value)
    {
        if (false !== strpos($value, '[')) {
            if ('[]' == substr($value, -2)) {
                $value = substr($value, 0, strlen($value) - 2);
            }
            $value = trim($value, ']');
            $value = str_replace('][', '-', $value);
            $value = str_replace('[', '-', $value);
        }

        return $value;
    }
}
