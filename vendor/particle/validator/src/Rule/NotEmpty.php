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
use Particle\Validator\Rule;
use Particle\Validator\Value\Container;

/**
 * This class is responsible for checking if a certain key has a value.
 *
 * @package Particle\Validator\Rule
 */
class NotEmpty extends Rule
{
    use StringifyCallbackTrait;

    /**
     * The error code for when a value is empty while this is not allowed.
     */
    const EMPTY_VALUE = 'NotEmpty::EMPTY_VALUE';

    /**
     * The templates for the possible messages this validator can return.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::EMPTY_VALUE => '{{ name }} must not be empty',
    ];

    /**
     * Denotes whether or not the chain should be stopped after this rule.
     *
     * @var bool
     */
    protected $shouldBreak = false;

    /**
     * Indicates if the value can be empty.
     *
     * @var bool
     */
    protected $allowEmpty;

    /**
     * Optionally contains a callable to overwrite the allow empty requirement on time of validation.
     *
     * @var callable
     */
    protected $allowEmptyCallback;

    /**
     * Contains the input container.
     *
     * @var Container
     */
    protected $input;

    /**
     * Construct the NotEmpty validator.
     *
     * @param bool $allowEmpty
     */
    public function __construct($allowEmpty)
    {
        $this->allowEmpty = (bool) $allowEmpty;
    }

    /**
     * @return bool
     */
    public function shouldBreakChain()
    {
        return $this->shouldBreak;
    }

    /**
     * Ensures a certain key has a value.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        $this->shouldBreak = false;
        if ($this->isEmpty($value)) {
            $this->shouldBreak = true;

            return !$this->allowEmpty($this->input) ? $this->error(self::EMPTY_VALUE) : true;
        }
        return true;
    }

    /**
     * Determines whether or not value $value is to be considered "empty".
     *
     * @param mixed $value
     * @return bool
     */
    protected function isEmpty($value)
    {
        if (is_string($value) && strlen($value) === 0) {
            return true;
        } elseif ($value === null) {
            return true;
        } elseif (is_array($value) && count($value) === 0) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     *
     * @param string $key
     * @param Container $input
     * @return bool
     */
    public function isValid($key, Container $input)
    {
        $this->input = $input;

        return $this->validate($input->get($key));
    }

    /**
     * Set a callable or boolean value to potentially alter the allow empty requirement at the time of validation.
     *
     * This may be incredibly useful for conditional validation.
     *
     * @param callable|bool $allowEmpty
     * @return $this
     */
    public function setAllowEmpty($allowEmpty)
    {
        if (is_callable($allowEmpty)) {
            return $this->setAllowEmptyCallback($allowEmpty);
        }
        return $this->overwriteAllowEmpty($allowEmpty);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMessageParameters()
    {
        return array_merge(parent::getMessageParameters(), [
            'allowEmpty' => $this->allowEmpty,
            'callback' => $this->getCallbackAsString($this->allowEmptyCallback)
        ]);
    }

    /**
     * Overwrite the allow empty requirement after instantiation of this rule.
     *
     * @param bool $allowEmpty
     * @return $this
     */
    protected function overwriteAllowEmpty($allowEmpty)
    {
        $this->allowEmpty = $allowEmpty;
        return $this;
    }

    /**
     * Set the callback to execute to determine whether or not the rule should allow empty.
     *
     * @param callable $allowEmptyCallback
     * @return $this
     */
    protected function setAllowEmptyCallback(callable $allowEmptyCallback)
    {
        $this->allowEmptyCallback = $allowEmptyCallback;
        return $this;
    }

    /**
     * Determines whether or not the value may be empty.
     *
     * @param Container $input
     * @return bool
     */
    protected function allowEmpty(Container $input)
    {
        if (isset($this->allowEmptyCallback)) {
            $this->allowEmpty = call_user_func($this->allowEmptyCallback, $input->getArrayCopy());
        }
        return $this->allowEmpty;
    }
}
