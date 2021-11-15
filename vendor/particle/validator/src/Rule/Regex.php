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
 * This rule is for validating that the value matches a certain regex.
 *
 * @package Particle\Validator\Rule
 */
class Regex extends Rule
{
    /**
     * A constant that will be used when the value doesn't match the regex.
     */
    const NO_MATCH = 'Regex::NO_MATCH';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NO_MATCH => '{{ name }} is invalid'
    ];

    /**
     * The regex that should be matched.
     *
     * @var string
     */
    protected $regex;

    /**
     * Construct the Regex rule.
     *
     * @param string $regex
     */
    public function __construct($regex)
    {
        $this->regex = $regex;
    }

    /**
     * Validates that the value matches the predefined regex.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return $this->match($this->regex, $value, self::NO_MATCH);
    }

    /**
     * A method to match against a regex. If it doesn't match, it will log the message $reason.
     *
     * @param string $regex
     * @param mixed $value
     * @param string $reason
     * @return bool
     */
    protected function match($regex, $value, $reason)
    {
        $result = preg_match($regex, $value);

        if ($result === 0) {
            return $this->error($reason);
        }
        return true;
    }
}
