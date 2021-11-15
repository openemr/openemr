<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Rule;

/**
 * This rule checks if the value consists solely out of alphanumeric characters.
 *
 * @package Particle\Validator\Rule
 */
class Alnum extends Regex
{
    /**
     * A constant that will be used for the error message when the value is not alphanumeric.
     */
    const NOT_ALNUM = 'Alnum::NOT_ALNUM';

    /**
     * A constant indicating spaces are allowed.
     */
    const ALLOW_SPACES = true;

    /**
     * A constant indicated spaces are *not* allowed.
     */
    const DISALLOW_SPACES = false;

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_ALNUM => '{{ name }} may only consist out of numeric and alphabetic characters'
    ];

    /**
     * Construct the validation rule.
     *
     * @param bool $allowSpaces
     */
    public function __construct($allowSpaces = self::DISALLOW_SPACES)
    {
        parent::__construct($allowSpaces ? '~^[\p{L}0-9\s]*$~iu' : '~^[\p{L}0-9]*$~iu');
    }

    /**
     * Checks whether $value consists solely out of alphanumeric characters.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return $this->match($this->regex, $value, self::NOT_ALNUM);
    }
}
