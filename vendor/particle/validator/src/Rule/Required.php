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
 * This class is responsible for checking if a required value is set.
 *
 * @package Particle\Validator\Rule
 */
class Required extends Rule
{
    use StringifyCallbackTrait;

    /**
     * The error code when a required field doesn't exist.
     */
    const NON_EXISTENT_KEY = 'Required::NON_EXISTENT_KEY';

    /**
     * The templates for the possible messages this validator can return.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NON_EXISTENT_KEY => '{{ key }} must be provided, but does not exist'
    ];

    /**
     * Denotes whether or not the chain should be stopped after this rule.
     *
     * @var bool
     */
    protected $shouldBreak = false;

    /**
     * Indicates if the value is required.
     *
     * @var bool
     */
    protected $required;

    /**
     * Optionally contains a callable to overwrite the required requirement on time of validation.
     *
     * @var callable
     */
    protected $requiredCallback;

    /**
     * Contains the input container.
     *
     * @var Container
     */
    protected $input;

    /**
     * Construct the Required validator.
     *
     * @param bool $required
     */
    public function __construct($required)
    {
        $this->required = $required;
    }

    /**
     * @return bool
     */
    public function shouldBreakChain()
    {
        return $this->shouldBreak;
    }

    /**
     * Does nothing, because validity is determined in isValid.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return true;
    }

    /**
     * Determines whether or not the key is set when required, and if there is a value if allow empty is false.
     *
     * @param string $key
     * @param Container $input
     * @return bool
     */
    public function isValid($key, Container $input)
    {
        $this->shouldBreak = false;
        $this->required = $this->isRequired($input);

        if (!$input->has($key)) {
            $this->shouldBreak = true;

            if ($this->required) {
                return $this->error(self::NON_EXISTENT_KEY);
            }
        }

        return $this->validate($input->get($key));
    }

    /**
     * Set a callable to potentially alter the required requirement at the time of validation.
     *
     * This may be incredibly useful for conditional validation.
     *
     * @param callable|bool $required
     * @return $this
     */
    public function setRequired($required)
    {
        if (is_callable($required)) {
            return $this->setRequiredCallback($required);
        }

        return $this->overwriteRequired((bool) $required);
    }

    /**
     * Overwrite the required requirement after instantiation of this object.
     *
     * @param bool $required
     * @return $this
     */
    protected function overwriteRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Set the required callback, and return $this.
     *
     * @param callable $requiredCallback
     * @return $this
     */
    protected function setRequiredCallback(callable $requiredCallback)
    {
        $this->requiredCallback = $requiredCallback;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMessageParameters()
    {
        return array_merge(parent::getMessageParameters(), [
            'required' => $this->required,
            'callback' => $this->getCallbackAsString($this->requiredCallback)
        ]);
    }

    /**
     * Determines if the value is required.
     *
     * @param Container $input
     * @return bool
     */
    protected function isRequired(Container $input)
    {
        if (isset($this->requiredCallback)) {
            $this->required = call_user_func_array($this->requiredCallback, [$input->getArrayCopy()]);
        }
        return $this->required;
    }
}
