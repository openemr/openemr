<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\View;
use Laminas\View\Exception;
use stdClass;

/**
 * Laminas\View\Helper\HeadMeta
 *
 * @see http://www.w3.org/TR/xhtml1/dtds.html
 *
 * Allows the following 'virtual' methods:
 * @method HeadMeta appendName($keyValue, $content, $modifiers = array())
 * @method HeadMeta offsetGetName($index, $keyValue, $content, $modifiers = array())
 * @method HeadMeta prependName($keyValue, $content, $modifiers = array())
 * @method HeadMeta setName($keyValue, $content, $modifiers = array())
 * @method HeadMeta appendHttpEquiv($keyValue, $content, $modifiers = array())
 * @method HeadMeta offsetGetHttpEquiv($index, $keyValue, $content, $modifiers = array())
 * @method HeadMeta prependHttpEquiv($keyValue, $content, $modifiers = array())
 * @method HeadMeta setHttpEquiv($keyValue, $content, $modifiers = array())
 * @method HeadMeta appendProperty($keyValue, $content, $modifiers = array())
 * @method HeadMeta offsetGetProperty($index, $keyValue, $content, $modifiers = array())
 * @method HeadMeta prependProperty($keyValue, $content, $modifiers = array())
 * @method HeadMeta setProperty($keyValue, $content, $modifiers = array())
 * @method HeadMeta appendItemprop($keyValue, $content, $modifiers = array())
 * @method HeadMeta offsetGetItemprop($index, $keyValue, $content, $modifiers = array())
 * @method HeadMeta prependItemprop($keyValue, $content, $modifiers = array())
 * @method HeadMeta setItemprop($keyValue, $content, $modifiers = array())
 */
class HeadMeta extends Placeholder\Container\AbstractStandalone
{
    /**
     * Allowed key types
     *
     * @var array
     */
    protected $typeKeys = ['name', 'http-equiv', 'charset', 'property', 'itemprop'];

    /**
     * Required attributes for meta tag
     *
     * @var array
     */
    protected $requiredKeys = ['content'];

    /**
     * Allowed modifier keys
     *
     * @var array
     */
    protected $modifierKeys = ['lang', 'scheme'];

    /**
     * Constructor
     *
     * Set separator to PHP_EOL
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->setSeparator(PHP_EOL);
    }

    /**
     * Retrieve object instance; optionally add meta tag
     *
     * @param  string $content
     * @param  string $keyValue
     * @param  string $keyType
     * @param  array  $modifiers
     * @param  string $placement
     * @return HeadMeta
     */
    public function __invoke(
        $content = null,
        $keyValue = null,
        $keyType = 'name',
        $modifiers = [],
        $placement = Placeholder\Container\AbstractContainer::APPEND
    ) {
        if ((null !== $content) && (null !== $keyValue)) {
            $item   = $this->createData($keyType, $keyValue, $content, $modifiers);
            $action = strtolower($placement);
            switch ($action) {
                case 'append':
                case 'prepend':
                case 'set':
                    $this->$action($item);
                    break;
                default:
                    $this->append($item);
                    break;
            }
        }

        return $this;
    }

    /**
     * Overload method access
     *
     * @param  string $method
     * @param  array  $args
     * @throws Exception\BadMethodCallException
     * @return HeadMeta
     */
    public function __call($method, $args)
    {
        if (preg_match(
            '/^(?P<action>set|(pre|ap)pend|offsetSet)(?P<type>Name|HttpEquiv|Property|Itemprop)$/',
            $method,
            $matches
        )) {
            $action = $matches['action'];
            $type   = $this->normalizeType($matches['type']);
            $argc   = count($args);
            $index  = null;

            if ('offsetSet' == $action) {
                if (0 < $argc) {
                    $index = array_shift($args);
                    --$argc;
                }
            }

            if (2 > $argc) {
                throw new Exception\BadMethodCallException(
                    'Too few arguments provided; requires key value, and content'
                );
            }

            if (3 > $argc) {
                $args[] = [];
            }

            $item  = $this->createData($type, $args[0], $args[1], $args[2]);

            if ('offsetSet' == $action) {
                return $this->offsetSet($index, $item);
            }

            $this->$action($item);

            return $this;
        }

        return parent::__call($method, $args);
    }

    /**
     * Render placeholder as string
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        $items = [];
        $this->getContainer()->ksort();

        $isHtml5 = $this->view->plugin('doctype')->isHtml5();

        try {
            foreach ($this as $item) {
                $content = $this->itemToString($item);

                if ($isHtml5 && $item->type == 'charset') {
                    array_unshift($items, $content);
                    continue;
                }

                $items[] = $content;
            }
        } catch (Exception\InvalidArgumentException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return '';
        }

        return $indent . implode($this->escape($this->getSeparator()) . $indent, $items);
    }

    /**
     * Create data item for inserting into stack
     *
     * @param  string $type
     * @param  string $typeValue
     * @param  string $content
     * @param  array  $modifiers
     * @return stdClass
     */
    public function createData($type, $typeValue, $content, array $modifiers)
    {
        $data            = new stdClass;
        $data->type      = $type;
        $data->$type     = $typeValue;
        $data->content   = $content;
        $data->modifiers = $modifiers;

        return $data;
    }

    /**
     * Build meta HTML string
     *
     * @param  stdClass $item
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function itemToString(stdClass $item)
    {
        if (! in_array($item->type, $this->typeKeys)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided for meta',
                $item->type
            ));
        }
        $type = $item->type;

        $modifiersString = '';
        foreach ($item->modifiers as $key => $value) {
            if ($this->view->plugin('doctype')->isHtml5()
                && $key == 'scheme'
            ) {
                throw new Exception\InvalidArgumentException(
                    'Invalid modifier "scheme" provided; not supported by HTML5'
                );
            }
            if (! in_array($key, $this->modifierKeys)) {
                continue;
            }
            $modifiersString .= sprintf('%s="%s"', $key, $this->autoEscape ? $this->escapeAttribute($value) : $value);
        }

        $modifiersString = rtrim($modifiersString);

        if ('' != $modifiersString) {
            $modifiersString = ' ' . $modifiersString;
        }

        if (method_exists($this->view, 'plugin')) {
            if ($this->view->plugin('doctype')->isHtml5()
                && $type == 'charset'
            ) {
                $tpl = ($this->view->plugin('doctype')->isXhtml())
                    ? '<meta %s="%s"/>'
                    : '<meta %s="%s">';
            } elseif ($this->view->plugin('doctype')->isXhtml()) {
                $tpl = '<meta %s="%s" content="%s"%s />';
            } else {
                $tpl = '<meta %s="%s" content="%s"%s>';
            }
        } else {
            $tpl = '<meta %s="%s" content="%s"%s />';
        }

        $meta = sprintf(
            $tpl,
            $type,
            $this->autoEscape ? $this->escapeAttribute($item->$type) : $item->$type,
            $this->autoEscape ? $this->escapeAttribute($item->content) : $item->content,
            $modifiersString
        );

        if (isset($item->modifiers['conditional'])
            && ! empty($item->modifiers['conditional'])
            && is_string($item->modifiers['conditional'])
        ) {
            // inner wrap with comment end and start if !IE
            if (str_replace(' ', '', $item->modifiers['conditional']) === '!IE') {
                $meta = '<!-->' . $meta . '<!--';
            }
            $meta = '<!--[if ' . $this->escape($item->modifiers['conditional']) . ']>' . $meta . '<![endif]-->';
        }

        return $meta;
    }

    /**
     * Normalize type attribute of meta
     *
     * @param  string $type type in CamelCase
     * @throws Exception\DomainException
     * @return string
     */
    protected function normalizeType($type)
    {
        switch ($type) {
            case 'Name':
                return 'name';
            case 'HttpEquiv':
                return 'http-equiv';
            case 'Property':
                return 'property';
            case 'Itemprop':
                return 'itemprop';
            default:
                throw new Exception\DomainException(sprintf(
                    'Invalid type "%s" passed to normalizeType',
                    $type
                ));
        }
    }

    /**
     * Determine if item is valid
     *
     * @param  stdClass $item
     * @return bool
     */
    protected function isValid($item)
    {
        if ((! $item instanceof stdClass)
            || ! isset($item->type)
            || ! isset($item->modifiers)
        ) {
            return false;
        }

        $doctype = $this->view->plugin('doctype');
        if ($item->type === 'charset' && $doctype->isXhtml()) {
            return false;
        }

        if (! isset($item->content)
            && (! $doctype->isHtml5()
            || (! $doctype->isHtml5() && $item->type !== 'charset'))
        ) {
            return false;
        }

        // <meta itemprop= ... /> is only supported with doctype html
        if (! $doctype->isHtml5()
            && $item->type === 'itemprop'
        ) {
            return false;
        }

        // <meta property= ... /> is only supported with doctype RDFa
        if (! $doctype->isRdfa()
            && $item->type === 'property'
        ) {
            return false;
        }

        return true;
    }

    /**
     * Append
     *
     * @param  stdClass $value
     * @return View\Helper\Placeholder\Container\AbstractContainer
     * @throws Exception\InvalidArgumentException
     */
    public function append($value)
    {
        if (! $this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'Invalid value passed to append'
            );
        }

        return $this->getContainer()->append($value);
    }

    /**
     * OffsetSet
     *
     * @param  string|int $index
     * @param  string     $value
     * @throws Exception\InvalidArgumentException
     */
    public function offsetSet($index, $value)
    {
        if (! $this->isValid($value)) {
            throw  new Exception\InvalidArgumentException(
                'Invalid value passed to offsetSet; please use offsetSetName() or offsetSetHttpEquiv()'
            );
        }

        return $this->getContainer()->offsetSet($index, $value);
    }

    /**
     * OffsetUnset
     *
     * @param  string|int $index
     * @throws Exception\InvalidArgumentException
     */
    public function offsetUnset($index)
    {
        if (! in_array($index, $this->getContainer()->getKeys())) {
            throw new Exception\InvalidArgumentException('Invalid index passed to offsetUnset()');
        }

        return $this->getContainer()->offsetUnset($index);
    }

    /**
     * Prepend
     *
     * @param  stdClass $value
     * @throws Exception\InvalidArgumentException
     * @return View\Helper\Placeholder\Container\AbstractContainer
     */
    public function prepend($value)
    {
        if (! $this->isValid($value)) {
            throw new Exception\InvalidArgumentException(
                'Invalid value passed to prepend'
            );
        }

        return $this->getContainer()->prepend($value);
    }

    /**
     * Set
     *
     * @param  stdClass $value
     * @throws Exception\InvalidArgumentException
     * @return View\Helper\Placeholder\Container\AbstractContainer
     */
    public function set($value)
    {
        if (! $this->isValid($value)) {
            throw new Exception\InvalidArgumentException('Invalid value passed to set');
        }

        $container = $this->getContainer();
        foreach ($container->getArrayCopy() as $index => $item) {
            if ($item->type == $value->type && $item->{$item->type} == $value->{$value->type}) {
                $this->offsetUnset($index);
            }
        }

        return $this->append($value);
    }

    /**
     * Create an HTML5-style meta charset tag. Something like <meta charset="utf-8">
     *
     * Not valid in a non-HTML5 doctype
     *
     * @param  string $charset
     * @param  Exception\InvalidArgumentException
     * @return HeadMeta Provides a fluent interface
     */
    public function setCharset($charset)
    {
        $item = new stdClass;
        $item->type = 'charset';
        $item->charset = $charset;
        $item->content = null;
        $item->modifiers = [];

        if (! $this->isValid($item)) {
            throw new Exception\InvalidArgumentException(
                'XHTML* doctype has no attribute charset; please use appendHttpEquiv()'
            );
        }

        $this->set($item);

        return $this;
    }
}
