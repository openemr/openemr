<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form;

use Traversable;

interface LabelAwareInterface
{
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
     * Set the attributes to use with the label
     *
     * @param  array $labelAttributes
     * @return $this
     */
    public function setLabelAttributes(array $labelAttributes);

    /**
     * Get the attributes to use with the label
     *
     * @return array
     */
    public function getLabelAttributes();

    /**
     * Set many label options at once
     *
     * Implementation will decide if this will overwrite or merge.
     *
     * @param  array|Traversable $arrayOrTraversable
     * @return $this
     */
    public function setLabelOptions($arrayOrTraversable);

    /**
     * Get label specific options
     *
     * @return array
     */
    public function getLabelOptions();

    /**
     * Set a single label optionn
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function setLabelOption($key, $value);

    /**
     * Retrieve a single label option
     *
     * @param  $key
     * @return mixed|null
     */
    public function getLabelOption($key);

    /**
     * Remove a single label option
     *
     * @param  string $key
     * @return $this
     */
    public function removeLabelOption($key);

    /**
     * Does the element has a specific label option ?
     *
     * @param  string $key
     * @return bool
     */
    public function hasLabelOption($key);

    /**
     * Remove many attributes at once
     *
     * @param  array $keys
     * @return $this
     */
    public function removeLabelOptions(array $keys);

    /**
     * Clear all label options
     *
     * @return $this
     */
    public function clearLabelOptions();
}
