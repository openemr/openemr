<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

use Laminas\Uri\Exception\ExceptionInterface as UriException;
use Laminas\Uri\Uri as UriHandler;
use Laminas\Validator\Exception\InvalidArgumentException;
use Traversable;

class Uri extends AbstractValidator
{
    const INVALID = 'uriInvalid';
    const NOT_URI = 'notUri';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID => 'Invalid type given. String expected',
        self::NOT_URI => 'The input does not appear to be a valid Uri',
    ];

    /**
     * @var UriHandler
     */
    protected $uriHandler;

    /**
     * @var bool
     */
    protected $allowRelative = true;

    /**
     * @var bool
     */
    protected $allowAbsolute = true;

    /**
     * Sets default option values for this instance
     *
     * @param array|Traversable $options
     */
    public function __construct($options = [])
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        } elseif (! is_array($options)) {
            $options = func_get_args();
            $temp['uriHandler'] = array_shift($options);
            if (! empty($options)) {
                $temp['allowRelative'] = array_shift($options);
            }
            if (! empty($options)) {
                $temp['allowAbsolute'] = array_shift($options);
            }

            $options = $temp;
        }

        if (isset($options['uriHandler'])) {
            $this->setUriHandler($options['uriHandler']);
        }
        if (isset($options['allowRelative'])) {
            $this->setAllowRelative($options['allowRelative']);
        }
        if (isset($options['allowAbsolute'])) {
            $this->setAllowAbsolute($options['allowAbsolute']);
        }

        parent::__construct($options);
    }

    /**
     * @throws InvalidArgumentException
     * @return UriHandler
     */
    public function getUriHandler()
    {
        if (null === $this->uriHandler) {
            // Lazy load the base Uri handler
            $this->uriHandler = new UriHandler();
        } elseif (is_string($this->uriHandler) && class_exists($this->uriHandler)) {
            // Instantiate string Uri handler that references a class
            $this->uriHandler = new $this->uriHandler;
        }
        return $this->uriHandler;
    }

    /**
     * @param  UriHandler|string $uriHandler
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setUriHandler($uriHandler)
    {
        if (! is_a($uriHandler, UriHandler::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Expecting a subclass name or instance of %s as $uriHandler',
                UriHandler::class
            ));
        }

        $this->uriHandler = $uriHandler;
        return $this;
    }

    /**
     * Returns the allowAbsolute option
     *
     * @return bool
     */
    public function getAllowAbsolute()
    {
        return $this->allowAbsolute;
    }

    /**
     * Sets the allowAbsolute option
     *
     * @param  bool $allowAbsolute
     * @return $this
     */
    public function setAllowAbsolute($allowAbsolute)
    {
        $this->allowAbsolute = (bool) $allowAbsolute;
        return $this;
    }

    /**
     * Returns the allowRelative option
     *
     * @return bool
     */
    public function getAllowRelative()
    {
        return $this->allowRelative;
    }

    /**
     * Sets the allowRelative option
     *
     * @param  bool $allowRelative
     * @return $this
     */
    public function setAllowRelative($allowRelative)
    {
        $this->allowRelative = (bool) $allowRelative;
        return $this;
    }

    /**
     * Returns true if and only if $value validates as a Uri
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (! is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $uriHandler = $this->getUriHandler();
        try {
            $uriHandler->parse($value);
            if ($uriHandler->isValid()) {
                // It will either be a valid absolute or relative URI
                if (($this->allowRelative && $this->allowAbsolute)
                    || ($this->allowAbsolute && $uriHandler->isAbsolute())
                    || ($this->allowRelative && $uriHandler->isValidRelative())
                ) {
                    return true;
                }
            }
        } catch (UriException $ex) {
            // Error parsing URI, it must be invalid
        }

        $this->error(self::NOT_URI);
        return false;
    }
}
