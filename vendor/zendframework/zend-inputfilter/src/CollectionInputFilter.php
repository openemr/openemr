<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

use Traversable;

class CollectionInputFilter extends InputFilter
{
    /**
     * @var bool
     */
    protected $isRequired = false;

    /**
     * @var int
     */
    protected $count = null;

    /**
     * @var array[]
     */
    protected $collectionValues = [];

    /**
     * @var array[]
     */
    protected $collectionRawValues = [];

    /**
     * @var array
     */
    protected $collectionMessages = [];

    /**
     * @var BaseInputFilter
     */
    protected $inputFilter;

    /**
     * Set the input filter to use when looping the data
     *
     * @param BaseInputFilter|array|Traversable $inputFilter
     * @throws Exception\RuntimeException
     * @return CollectionInputFilter
     */
    public function setInputFilter($inputFilter)
    {
        if (is_array($inputFilter) || $inputFilter instanceof Traversable) {
            $inputFilter = $this->getFactory()->createInputFilter($inputFilter);
        }

        if (! $inputFilter instanceof BaseInputFilter) {
            throw new Exception\RuntimeException(sprintf(
                '%s expects an instance of %s; received "%s"',
                __METHOD__,
                BaseInputFilter::class,
                (is_object($inputFilter) ? get_class($inputFilter) : gettype($inputFilter))
            ));
        }

        $this->inputFilter = $inputFilter;

        return $this;
    }

    /**
     * Get the input filter used when looping the data
     *
     * @return BaseInputFilter
     */
    public function getInputFilter()
    {
        if (null === $this->inputFilter) {
            $this->setInputFilter(new InputFilter());
        }

        return $this->inputFilter;
    }

    /**
     * Set if the collection can be empty
     *
     * @param bool $isRequired
     * @return CollectionInputFilter
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    /**
     * Get if collection can be empty
     *
     * @return bool
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * Set the count of data to validate
     *
     * @param int $count
     * @return CollectionInputFilter
     */
    public function setCount($count)
    {
        $this->count = $count > 0 ? $count : 0;

        return $this;
    }

    /**
     * Get the count of data to validate, use the count of data by default
     *
     * @return int
     */
    public function getCount()
    {
        if (null === $this->count) {
            return count($this->data);
        }

        return $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        if (! (is_array($data) || $data instanceof Traversable)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable collection; invalid collection of type %s provided',
                __METHOD__,
                is_object($data) ? get_class($data) : gettype($data)
            ));
        }

        foreach ($data as $item) {
            if (is_array($item) || $item instanceof Traversable) {
                continue;
            }

            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects each item in a collection to be an array or Traversable; '
                . 'invalid item in collection of type %s detected',
                __METHOD__,
                is_object($item) ? get_class($item) : gettype($item)
            ));
        }

        $this->data = $data;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @param mixed $context Ignored, but present to retain signature compatibility.
     */
    public function isValid($context = null)
    {
        $this->collectionMessages = [];
        $inputFilter = $this->getInputFilter();
        $valid = true;

        if ($this->getCount() < 1) {
            if ($this->isRequired) {
                $valid = false;
            }
        }

        if (count($this->data) < $this->getCount()) {
            $valid = false;
        }

        if (! $this->data) {
            $this->clearValues();
            $this->clearRawValues();

            return $valid;
        }

        foreach ($this->data as $key => $data) {
            $inputFilter->setData($data);

            if (null !== $this->validationGroup) {
                $inputFilter->setValidationGroup($this->validationGroup[$key]);
            }

            if ($inputFilter->isValid()) {
                $this->validInputs[$key] = $inputFilter->getValidInput();
            } else {
                $valid = false;
                $this->collectionMessages[$key] = $inputFilter->getMessages();
                $this->invalidInputs[$key] = $inputFilter->getInvalidInput();
            }

            $this->collectionValues[$key] = $inputFilter->getValues();
            $this->collectionRawValues[$key] = $inputFilter->getRawValues();
        }

        return $valid;
    }

    /**
     * {@inheritdoc}
     */
    public function setValidationGroup($name)
    {
        if ($name === self::VALIDATE_ALL) {
            $name = null;
        }
        $this->validationGroup = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        return $this->collectionValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValues()
    {
        return $this->collectionRawValues;
    }

    /**
     * Clear collectionValues
     *
     * @return array[]
     */
    public function clearValues()
    {
        return $this->collectionValues = [];
    }

    /**
     * Clear collectionRawValues
     *
     * @return array[]
     */
    public function clearRawValues()
    {
        return $this->collectionRawValues = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->collectionMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnknown()
    {
        if (! $this->data) {
            throw new Exception\RuntimeException(sprintf(
                '%s: no data present!',
                __METHOD__
            ));
        }

        $inputFilter = $this->getInputFilter();

        $unknownInputs = [];
        foreach ($this->data as $key => $data) {
            $inputFilter->setData($data);

            if ($unknown = $inputFilter->getUnknown()) {
                $unknownInputs[$key] = $unknown;
            }
        }

        return $unknownInputs;
    }
}
