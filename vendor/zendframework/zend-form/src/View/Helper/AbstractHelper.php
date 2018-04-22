<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\I18n\View\Helper\AbstractTranslatorHelper as BaseAbstractHelper;
use Zend\View\Helper\Doctype;
use Zend\View\Helper\EscapeHtml;
use Zend\View\Helper\EscapeHtmlAttr;

/**
 * Base functionality for all form view helpers
 */
abstract class AbstractHelper extends BaseAbstractHelper
{
    /**
     * The default translatable HTML attributes
     *
     * @var array
     */
    protected static $defaultTranslatableHtmlAttributes = [
        'title' => true,
    ];

    /**
     * The default translatable HTML attribute prefixes
     *
     * @var array
     */
    protected static $defaultTranslatableHtmlAttributePrefixes = [];

    /**
     * Standard boolean attributes, with expected values for enabling/disabling
     *
     * @var array
     */
    protected $booleanAttributes = [
        'autofocus'    => ['on' => 'autofocus', 'off' => ''],
        'checked'      => ['on' => 'checked',   'off' => ''],
        'disabled'     => ['on' => 'disabled',  'off' => ''],
        'multiple'     => ['on' => 'multiple',  'off' => ''],
        'readonly'     => ['on' => 'readonly',  'off' => ''],
        'required'     => ['on' => 'required',  'off' => ''],
        'selected'     => ['on' => 'selected',  'off' => ''],
    ];

    /**
     * Translatable attributes
     *
     * @var array
     */
    protected $translatableAttributes = [
        'placeholder' => true,
    ];

    /**
     * Prefixes of translatable HTML attributes
     *
     * @var array
     */
    protected $translatableAttributePrefixes = [];

    /**
     * @var Doctype
     */
    protected $doctypeHelper;

    /**
     * @var EscapeHtml
     */
    protected $escapeHtmlHelper;

    /**
     * @var EscapeHtmlAttr
     */
    protected $escapeHtmlAttrHelper;

    /**
     * Attributes globally valid for all tags
     *
     * @var array
     */
    protected $validGlobalAttributes = [
        'accesskey'          => true,
        'class'              => true,
        'contenteditable'    => true,
        'contextmenu'        => true,
        'dir'                => true,
        'draggable'          => true,
        'dropzone'           => true,
        'hidden'             => true,
        'id'                 => true,
        'lang'               => true,
        'onabort'            => true,
        'onblur'             => true,
        'oncanplay'          => true,
        'oncanplaythrough'   => true,
        'onchange'           => true,
        'onclick'            => true,
        'oncontextmenu'      => true,
        'ondblclick'         => true,
        'ondrag'             => true,
        'ondragend'          => true,
        'ondragenter'        => true,
        'ondragleave'        => true,
        'ondragover'         => true,
        'ondragstart'        => true,
        'ondrop'             => true,
        'ondurationchange'   => true,
        'onemptied'          => true,
        'onended'            => true,
        'onerror'            => true,
        'onfocus'            => true,
        'oninput'            => true,
        'oninvalid'          => true,
        'onkeydown'          => true,
        'onkeypress'         => true,
        'onkeyup'            => true,
        'onload'             => true,
        'onloadeddata'       => true,
        'onloadedmetadata'   => true,
        'onloadstart'        => true,
        'onmousedown'        => true,
        'onmousemove'        => true,
        'onmouseout'         => true,
        'onmouseover'        => true,
        'onmouseup'          => true,
        'onmousewheel'       => true,
        'onpause'            => true,
        'onplay'             => true,
        'onplaying'          => true,
        'onprogress'         => true,
        'onratechange'       => true,
        'onreadystatechange' => true,
        'onreset'            => true,
        'onscroll'           => true,
        'onseeked'           => true,
        'onseeking'          => true,
        'onselect'           => true,
        'onshow'             => true,
        'onstalled'          => true,
        'onsubmit'           => true,
        'onsuspend'          => true,
        'ontimeupdate'       => true,
        'onvolumechange'     => true,
        'onwaiting'          => true,
        'role'               => true,
        'spellcheck'         => true,
        'style'              => true,
        'tabindex'           => true,
        'title'              => true,
        'xml:base'           => true,
        'xml:lang'           => true,
        'xml:space'          => true,
    ];

    /**
     * Attributes valid for the tag represented by this helper
     *
     * This should be overridden in extending classes
     *
     * @var array
     */
    protected $validTagAttributes = [
    ];

    /**
     * Set value for doctype
     *
     * @param  string $doctype
     * @return AbstractHelper
     */
    public function setDoctype($doctype)
    {
        $this->getDoctypeHelper()->setDoctype($doctype);
        return $this;
    }

    /**
     * Get value for doctype
     *
     * @return string
     */
    public function getDoctype()
    {
        return $this->getDoctypeHelper()->getDoctype();
    }

    /**
     * Set value for character encoding
     *
     * @param  string $encoding
     * @return AbstractHelper
     */
    public function setEncoding($encoding)
    {
        $this->getEscapeHtmlHelper()->setEncoding($encoding);
        $this->getEscapeHtmlAttrHelper()->setEncoding($encoding);
        return $this;
    }

    /**
     * Get character encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->getEscapeHtmlHelper()->getEncoding();
    }

    /**
     * Create a string of all attribute/value pairs
     *
     * Escapes all attribute values
     *
     * @param  array $attributes
     * @return string
     */
    public function createAttributesString(array $attributes)
    {
        $attributes = $this->prepareAttributes($attributes);
        $escape     = $this->getEscapeHtmlHelper();
        $escapeAttr = $this->getEscapeHtmlAttrHelper();
        $strings    = [];

        foreach ($attributes as $key => $value) {
            $key = strtolower($key);

            if (! $value && isset($this->booleanAttributes[$key])) {
                // Skip boolean attributes that expect empty string as false value
                if ('' === $this->booleanAttributes[$key]['off']) {
                    continue;
                }
            }

            //check if attribute is translatable and translate it
            $value = $this->translateHtmlAttributeValue($key, $value);

            //@TODO Escape event attributes like AbstractHtmlElement view helper does in htmlAttribs ??
            $strings[] = sprintf('%s="%s"', $escape($key), $escapeAttr($value));
        }

        return implode(' ', $strings);
    }

    /**
     * Get the ID of an element
     *
     * If no ID attribute present, attempts to use the name attribute.
     * If no name attribute is present, either, returns null.
     *
     * @param  ElementInterface $element
     * @return null|string
     */
    public function getId(ElementInterface $element)
    {
        $id = $element->getAttribute('id');
        if (null !== $id) {
            return $id;
        }

        return $element->getName();
    }

    /**
     * Get the closing bracket for an inline tag
     *
     * Closes as either "/>" for XHTML doctypes or ">" otherwise.
     *
     * @return string
     */
    public function getInlineClosingBracket()
    {
        $doctypeHelper = $this->getDoctypeHelper();
        if ($doctypeHelper->isXhtml()) {
            return '/>';
        }
        return '>';
    }

    /**
     * Retrieve the doctype helper
     *
     * @return Doctype
     */
    protected function getDoctypeHelper()
    {
        if ($this->doctypeHelper) {
            return $this->doctypeHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->doctypeHelper = $this->view->plugin('doctype');
        }

        if (! $this->doctypeHelper instanceof Doctype) {
            $this->doctypeHelper = new Doctype();
        }

        return $this->doctypeHelper;
    }

    /**
     * Retrieve the escapeHtml helper
     *
     * @return EscapeHtml
     */
    protected function getEscapeHtmlHelper()
    {
        if ($this->escapeHtmlHelper) {
            return $this->escapeHtmlHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->escapeHtmlHelper = $this->view->plugin('escapehtml');
        }

        if (! $this->escapeHtmlHelper instanceof EscapeHtml) {
            $this->escapeHtmlHelper = new EscapeHtml();
        }

        return $this->escapeHtmlHelper;
    }

    /**
     * Retrieve the escapeHtmlAttr helper
     *
     * @return EscapeHtmlAttr
     */
    protected function getEscapeHtmlAttrHelper()
    {
        if ($this->escapeHtmlAttrHelper) {
            return $this->escapeHtmlAttrHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->escapeHtmlAttrHelper = $this->view->plugin('escapehtmlattr');
        }

        if (! $this->escapeHtmlAttrHelper instanceof EscapeHtmlAttr) {
            $this->escapeHtmlAttrHelper = new EscapeHtmlAttr();
        }

        return $this->escapeHtmlAttrHelper;
    }

    /**
     * Prepare attributes for rendering
     *
     * Ensures appropriate attributes are present (e.g., if "name" is present,
     * but no "id", sets the latter to the former).
     *
     * Removes any invalid attributes
     *
     * @param  array $attributes
     * @return array
     */
    protected function prepareAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $attribute = strtolower($key);

            if (! isset($this->validGlobalAttributes[$attribute])
                && ! isset($this->validTagAttributes[$attribute])
                && 'data-' != substr($attribute, 0, 5)
                && 'aria-' != substr($attribute, 0, 5)
                && 'x-' != substr($attribute, 0, 2)
            ) {
                // Invalid attribute for the current tag
                unset($attributes[$key]);
                continue;
            }

            // Normalize attribute key, if needed
            if ($attribute != $key) {
                unset($attributes[$key]);
                $attributes[$attribute] = $value;
            }

            // Normalize boolean attribute values
            if (isset($this->booleanAttributes[$attribute])) {
                $attributes[$attribute] = $this->prepareBooleanAttributeValue($attribute, $value);
            }
        }

        return $attributes;
    }

    /**
     * Prepare a boolean attribute value
     *
     * Prepares the expected representation for the boolean attribute specified.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return string
     */
    protected function prepareBooleanAttributeValue($attribute, $value)
    {
        if (! is_bool($value) && in_array($value, $this->booleanAttributes[$attribute])) {
            return $value;
        }

        $value = (bool) $value;
        return ($value
            ? $this->booleanAttributes[$attribute]['on']
            : $this->booleanAttributes[$attribute]['off']
        );
    }

    /**
     * Translates the value of the HTML attribute if it should be translated and this view helper has a translator
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    protected function translateHtmlAttributeValue($key, $value)
    {
        if (empty($value) || ($this->getTranslator() === null)) {
            return $value;
        }

        if (isset($this->translatableAttributes[$key]) || isset(self::$defaultTranslatableHtmlAttributes[$key])) {
            return $this->getTranslator()->translate($value, $this->getTranslatorTextDomain());
        } else {
            foreach ($this->translatableAttributePrefixes as $prefix) {
                if (mb_substr($key, 0, mb_strlen($prefix)) === $prefix) {
                    // prefix matches => return translated $value
                    return $this->getTranslator()->translate($value, $this->getTranslatorTextDomain());
                }
            }
            foreach (self::$defaultTranslatableHtmlAttributePrefixes as $prefix) {
                if (mb_substr($key, 0, mb_strlen($prefix)) === $prefix) {
                    // default prefix matches => return translated $value
                    return $this->getTranslator()->translate($value, $this->getTranslatorTextDomain());
                }
            }
        }

        return $value;
    }

    /**
     * Adds an HTML attribute to the list of translatable attributes
     *
     * @param string $attribute
     *
     * @return AbstractHelper
     */
    public function addTranslatableAttribute($attribute)
    {
        $this->translatableAttributes[$attribute] = true;

        return $this;
    }

    /**
     * Adds an HTML attribute to the list of the default translatable attributes
     *
     * @param string $attribute
     */
    public static function addDefaultTranslatableAttribute($attribute)
    {
        self::$defaultTranslatableHtmlAttributes[$attribute] = true;
    }

    /**
     * Adds an HTML attribute to the list of translatable attributes
     *
     * @param string $prefix
     *
     * @return AbstractHelper
     */
    public function addTranslatableAttributePrefix($prefix)
    {
        $this->translatableAttributePrefixes[] = $prefix;

        return $this;
    }

    /**
     * Adds an HTML attribute to the list of translatable attributes
     *
     * @param string $prefix
     */
    public static function addDefaultTranslatableAttributePrefix($prefix)
    {
        self::$defaultTranslatableHtmlAttributePrefixes[] = $prefix;
    }
}
