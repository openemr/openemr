<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Exception;

use Particle\Validator\ExceptionInterface;

/**
 * The invalid value exception is used by a callback to provide custom errors.
 *
 * @package Particle\Validator
 */
class InvalidValueException extends \Exception implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $message;

    /**
     * @param string $message
     * @param string $identifier
     * @param \Exception $previous
     */
    public function __construct($message, $identifier, \Exception $previous = null)
    {
        $this->message = $message;
        $this->identifier = $identifier;

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
