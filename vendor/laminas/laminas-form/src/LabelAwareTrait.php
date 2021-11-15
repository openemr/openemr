<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form;

use Traversable;

use function array_key_exists;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function sprintf;

trait LabelAwareTrait
{
    /**
     * Label specific html attributes
     *
     * @var array
     */
    protected $labelAttributes;

    /**
     * Label specific options
     *
     * @var array
     */
    protected $labelOptions = [];

    /**
     * Set the attributes to use with the label
     *
     * @param array $labelAttributes
     * @return $this
     */
    public function setLabelAttributes(array $labelAttributes)
    {
        $this->labelAttributes = $labelAttributes;
        return $this;
    }

    /**
     * Get the attributes to use with the label
     *
     * @return array
     */
    public function getLabelAttributes()
    {
        return $this->labelAttributes;
    }

    /**
     * Set many label options at once
     *
     * Implementation will decide if this will overwrite or merge.
     *
     * @param  array|Traversable $arrayOrTraversable
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setLabelOptions($arrayOrTraversable)
    {
        if (! is_array($arrayOrTraversable) && ! $arrayOrTraversable instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable argument; received "%s"',
                __METHOD__,
                is_object($arrayOrTraversable) ? get_class($arrayOrTraversable) : gettype($arrayOrTraversable)
            ));
        }
        foreach ($arrayOrTraversable as $key => $value) {
            $this->setLabelOption($key, $value);
        }
        return $this;
    }

    /**
     * Get label specific options
     *
     * @return array
     */
    public function getLabelOptions()
    {
        return $this->labelOptions;
    }

    /**
     * Clear all label options
     *
     * @return $this
     */
    public function clearLabelOptions()
    {
        $this->labelOptions = [];
        return $this;
    }

    /**
     * Remove many attributes at once
     *
     * @param  array $keys
     * @return $this
     */
    public function removeLabelOptions(array $keys)
    {
        foreach ($keys as $key) {
            unset($this->labelOptions[$key]);
        }

        return $this;
    }

    /**
     * Set a single label optionn
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function setLabelOption($key, $value)
    {
        $this->labelOptions[$key] = $value;
        return $this;
    }

    /**
     * Retrieve a single label option
     *
     * @param  $key
     * @return mixed|null
     */
    public function getLabelOption($key)
    {
        if (! array_key_exists($key, $this->labelOptions)) {
            return null;
        }
        return $this->labelOptions[$key];
    }

    /**
     * Remove a single label option
     *
     * @param string $key
     * @return $this
     */
    public function removeLabelOption($key)
    {
        unset($this->labelOptions[$key]);
        return $this;
    }

    /**
     * Does the element has a specific label option ?
     *
     * @param  string $key
     * @return bool
     */
    public function hasLabelOption($key)
    {
        return array_key_exists($key, $this->labelOptions);
    }
}
