<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator;

class Failure
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @param string $key
     * @param string $reason
     * @param string $template
     * @param array $parameters
     */
    public function __construct($key, $reason, $template, $parameters)
    {
        $this->key = $key;
        $this->reason = $reason;
        $this->template = $template;
        $this->parameters = $parameters;
    }

    /**
     * Formats and returns the error message for this failure.
     *
     * @return string
     */
    public function format()
    {
        $replace = function ($matches) {
            if (array_key_exists($matches[1], $this->parameters)) {
                return $this->parameters[$matches[1]];
            }
            return $matches[0];
        };

        return preg_replace_callback('~{{\s*([^}\s]+)\s*}}~', $replace, $this->template);
    }

    /**
     * Returns the key for this failure.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the reason for failure.
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Overwrites the message template.
     *
     * @param string $template
     */
    public function overwriteMessageTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @param string $key
     */
    public function overwriteKey($key)
    {
        $this->key = $key;
    }
}
