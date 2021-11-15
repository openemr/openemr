<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Rule;

use Particle\Validator\StringifyCallbackTrait;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Rule;
use Particle\Validator\Value\Container;

/**
 * This rule is for validating a value with a custom callback.
 *
 * @package Particle\Validator\Rule
 */
class Callback extends Rule
{
    use StringifyCallbackTrait;

    /**
     * A constant that will be used to indicate that the callback returned false.
     */
    const INVALID_VALUE = 'Callback::INVALID_VALUE';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_VALUE => '{{ name }} is invalid',
    ];

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var Container
     */
    protected $input;

    /**
     * Construct the Callback validator.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Validates $value by calling the callback.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        try {
            $result = call_user_func($this->callback, $value, $this->values);

            if ($result === true) {
                return true;
            }
            return $this->error(self::INVALID_VALUE);
        } catch (InvalidValueException $exception) {
            $reason = $exception->getIdentifier();
            $this->messageTemplates[$reason] = $exception->getMessage();

            return $this->error($reason);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getMessageParameters()
    {
        return array_merge(parent::getMessageParameters(), [
            'callback' => $this->getCallbackAsString($this->callback),
        ]);
    }

    /**
     * Validates the value according to this rule, and returns the result as a bool.
     *
     * @param string $key
     * @param Container $input
     * @return bool
     */
    public function isValid($key, Container $input)
    {
        $this->values = $input->getArrayCopy();

        return parent::isValid($key, $input);
    }
}
