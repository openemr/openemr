<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Output;

/**
 * Subject is an object for communicating the internal state of a Chain to an output object.
 *
 * @package Particle\Validator\Output
 */
class Subject
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Rule[]
     */
    protected $rules;

    /**
     * @param string $key
     * @param string $name
     */
    public function __construct($key, $name)
    {
        $this->key = $key;
        $this->name = $name;
    }

    /**
     * Adds a rule for this subject.
     *
     * @param Rule $rule
     */
    public function addRule(Rule $rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * Returns the key for this subject.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the name for this subject.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns an array of all rules in this subject.
     *
     * @return Rule[]
     */
    public function getRules()
    {
        return $this->rules;
    }
}
