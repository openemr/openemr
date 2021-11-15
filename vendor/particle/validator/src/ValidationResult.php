<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator;

/**
 * The ValidationResult class holds the validation result and the validation messages.
 *
 * @package Particle\Validator
 */
class ValidationResult
{
    /**
     * @var bool
     */
    protected $isValid;

    /**
     * @var array
     */
    protected $messages;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var Failure[]
     */
    protected $failures;

    /**
     * Construct the validation result.
     *
     * @param bool $isValid
     * @param array $failures
     * @param array $values
     */
    public function __construct($isValid, array $failures, array $values)
    {
        $this->isValid = $isValid;
        $this->failures = $failures;
        $this->values = $values;
    }

    /**
     * Returns whether or not the validator has validated the values.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Returns whether or not the validator has validated the values.
     *
     * @return bool
     */
    public function isNotValid()
    {
        return !$this->isValid;
    }

    /**
     * Returns the array of messages that were collected during validation.
     *
     * @return array
     */
    public function getMessages()
    {
        if ($this->messages === null) {
            $this->messages = [];
            foreach ($this->failures as $failure) {
                $this->messages[$failure->getKey()][$failure->getReason()] = $failure->format();
            }
        }
        return $this->messages;
    }

    /**
     * @return Failure[]
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * Returns all validated values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
}
