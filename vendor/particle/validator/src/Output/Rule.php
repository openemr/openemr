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
 * The Rule class is a representation of an actual rule for displaying purposes.
 *
 * @package Particle\Validator
 */
class Rule
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $messages;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param string $name
     * @param array $messages
     * @param array $parameters
     */
    public function __construct($name, array $messages, array $parameters)
    {
        $this->name = $name;
        $this->messages = $messages;
        $this->parameters = $parameters;
    }

    /**
     * Returns the name (short class name) for this rule.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns all messages for this rule.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Returns all parameters for this rule.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
