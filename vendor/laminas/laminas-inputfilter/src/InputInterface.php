<?php

/**
 * @see       https://github.com/laminas/laminas-inputfilter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-inputfilter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-inputfilter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\InputFilter;

use Laminas\Filter\FilterChain;
use Laminas\Validator\ValidatorChain;

interface InputInterface
{
    /**
     * @deprecated 2.4.8 Add Laminas\Validator\NotEmpty validator to the ValidatorChain and set this to `true`.
     *
     * @param bool $allowEmpty
     * @return self
     */
    public function setAllowEmpty($allowEmpty);

    /**
     * @param bool $breakOnFailure
     * @return self
     */
    public function setBreakOnFailure($breakOnFailure);

    /**
     * @param string|null $errorMessage
     * @return self
     */
    public function setErrorMessage($errorMessage);

    /**
     * @param FilterChain $filterChain
     * @return self
     */
    public function setFilterChain(FilterChain $filterChain);

    /**
     * @param string $name
     * @return self
     */
    public function setName($name);

    /**
     * @param bool $required
     * @return self
     */
    public function setRequired($required);

    /**
     * @param ValidatorChain $validatorChain
     * @return self
     */
    public function setValidatorChain(ValidatorChain $validatorChain);

    /**
     * @param mixed $value
     * @return self
     */
    public function setValue($value);

    /**
     * @param InputInterface $input
     * @return self
     */
    public function merge(InputInterface $input);

    /**
     * @deprecated 2.4.8 Add Laminas\Validator\NotEmpty validator to the ValidatorChain.
     *
     * @return bool
     */
    public function allowEmpty();

    /**
     * @return bool
     */
    public function breakOnFailure();

    /**
     * @return string|null
     */
    public function getErrorMessage();

    /**
     * @return FilterChain
     */
    public function getFilterChain();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getRawValue();

    /**
     * @return bool
     */
    public function isRequired();

    /**
     * @return ValidatorChain
     */
    public function getValidatorChain();

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return bool
     */
    public function isValid();

    /**
     * @return string[]
     */
    public function getMessages();
}
