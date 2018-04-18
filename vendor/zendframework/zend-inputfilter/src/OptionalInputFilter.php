<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

/**
 * InputFilter which only checks the containing Inputs when non-empty data is set,
 * else it reports valid
 *
 * This is analog to {@see Zend\InputFilter\Input} with the option ->setRequired(false)
 */
class OptionalInputFilter extends InputFilter
{
    /**
     * Set data to use when validating and filtering
     *
     * @param  iterable|mixed $data
     *     must be a non-empty iterable in order trigger actual validation, else it is always valid
     * @throws Exception\InvalidArgumentException
     * @return InputFilterInterface
     */
    public function setData($data)
    {
        return parent::setData($data ?: []);
    }

    /**
     * Run validation, or return true if the data was empty
     *
     * {@inheritDoc}
     */
    public function isValid($context = null)
    {
        if ($this->data) {
            return parent::isValid($context);
        }

        return true;
    }

    /**
     * Return a list of filtered values, or null if the data was missing entirely
     * Null is returned instead of an empty array to prevent it being passed to a hydrator,
     *     which would likely cause failures later on in your program
     * Fallbacks for the inputs are not respected by design
     *
     * @return array|null
     */
    public function getValues()
    {
        return $this->data ? parent::getValues() : null;
    }
}
