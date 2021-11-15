<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form;

use Traversable;

interface ElementInterface
{
    /**
     * Set the name of this element
     *
     * In most cases, this will proxy to the attributes for storage, but is
     * present to indicate that elements are generally named.
     *
     * @param  string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Retrieve the element name
     *
     * @return string
     */
    public function getName();

    /**
     * Set options for an element
     *
     * @param  array|Traversable $options
     * @return $this
     */
    public function setOptions($options);

    /**
     * Set a single option for an element
     *
     * @param  string $key
     * @param  mixed $value
     * @return $this
     */
    public function setOption($key, $value);

    /**
     * get the defined options
     *
     * @return array
     */
    public function getOptions();

    /**
     * return the specified option
     *
     * @param string $option
     * @return null|mixed
     */
    public function getOption($option);

    /**
     * Set a single element attribute
     *
     * @param  string $key
     * @param  mixed $value
     * @return $this
     */
    public function setAttribute($key, $value);

    /**
     * Retrieve a single element attribute
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key);

    /**
     * Return true if a specific attribute is set
     *
     * @param  string $key
     * @return bool
     */
    public function hasAttribute($key);

    /**
     * Set many attributes at once
     *
     * Implementation will decide if this will overwrite or merge.
     *
     * @param  array|Traversable $arrayOrTraversable
     * @return $this
     */
    public function setAttributes($arrayOrTraversable);

    /**
     * Retrieve all attributes at once
     *
     * @return array|Traversable
     */
    public function getAttributes();

    /**
     * Set the value of the element
     *
     * @param  mixed $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Retrieve the element value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the label (if any) used for this element
     *
     * @param  $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Retrieve the label (if any) used for this element
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set a list of messages to report when validation fails
     *
     * @param  array|Traversable $messages
     * @return $this
     */
    public function setMessages($messages);

    /**
     * Get validation error messages, if any
     *
     * Returns a list of validation failure messages, if any.
     *
     * @return array|Traversable
     */
    public function getMessages();
}
