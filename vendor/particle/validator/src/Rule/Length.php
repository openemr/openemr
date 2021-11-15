<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Rule;

use Particle\Validator\Rule;

/**
 * This rule is for validating the exact length of a string.
 *
 * @package Particle\Validator\Rule
 */
class Length extends Rule
{
    /**
     * A constant that will be used for the error message when the value is too short.
     */
    const TOO_SHORT = 'Length::TOO_SHORT';

    /**
     * A constant that will be used for the error message when the value is too long.
     */
    const TOO_LONG = 'Length::TOO_LONG';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::TOO_SHORT => '{{ name }} is too short and must be {{ length }} characters long',
        self::TOO_LONG => '{{ name }} is too long and must be {{ length }} characters long',
    ];

    /**
     * The length the value should have.
     *
     * @var int
     */
    protected $length;

    /**
     * Construct the Length validator.
     *
     * @param int $length
     */
    public function __construct($length)
    {
        $this->length = $length;
    }

    /**
     * Attempts to see if the length of the value is exactly the number expected and returns the result as a bool.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        $actualLength = strlen($value);

        if ($actualLength > $this->length) {
            return $this->error(self::TOO_LONG);
        }
        if ($actualLength < $this->length) {
            return $this->error(self::TOO_SHORT);
        }
        return true;
    }

    /**
     * Returns the parameters that may be used in a validation message.
     *
     * @return array
     */
    protected function getMessageParameters()
    {
        return array_merge(parent::getMessageParameters(), [
            'length' => $this->length
        ]);
    }
}
