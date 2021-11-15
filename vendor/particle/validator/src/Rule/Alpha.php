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
 * This rule checks if the value consists solely out of alphabetic characters.
 *
 * @package Particle\Validator\Rule
 */
class Alpha extends Regex
{
    /**
     * A constant that will be used for the error message when the value contains non-alphabetic characters.
     */
    const NOT_ALPHA = 'Alpha::NOT_ALPHA';

    /**
     * A constant indicated spaces are allowed.
     */
    const ALLOW_SPACES = true;

    /**
     * A constant indicating spaces are *not* allowed.
     */
    const DISALLOW_SPACES = false;

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_ALPHA => '{{ name }} may only consist out of alphabetic characters'
    ];

    /**
     * Construct the Alpha rule.
     *
     * @param bool $allowWhitespace
     */
    public function __construct($allowWhitespace = self::DISALLOW_SPACES)
    {
        parent::__construct($allowWhitespace ? '~^[\p{L}\s]*$~iu' : '~^[\p{L}]*$~ui');
    }

    /**
     * Checks whether $value consists solely out of alphabetic characters.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return $this->match($this->regex, $value, self::NOT_ALPHA);
    }
}
