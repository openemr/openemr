<?php

/**
 * @see       https://github.com/laminas/laminas-uri for the canonical source repository
 * @copyright https://github.com/laminas/laminas-uri/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-uri/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Uri;

use Laminas\Validator\EmailAddress as EmailValidator;
use Laminas\Validator\ValidatorInterface;

use function strpos;

/**
 * "Mailto" URI handler
 *
 * The 'mailto:...' scheme is loosely defined in RFC-1738
 */
class Mailto extends Uri
{
    /** @var array<int,string> */
    protected static $validSchemes = ['mailto'];

    /**
     * Validator for use when validating email address
     *
     * @var ValidatorInterface
     */
    protected $emailValidator;

    /**
     * Check if the URI is a valid Mailto URI
     *
     * This applies additional specific validation rules beyond the ones
     * required by the generic URI syntax
     *
     * @see    Uri::isValid()
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->host || $this->userInfo || $this->port) {
            return false;
        }

        if (empty($this->path)) {
            return false;
        }

        if (0 === strpos($this->path, '/')) {
            return false;
        }

        $validator = $this->getValidator();
        return $validator->isValid($this->path);
    }

    /**
     * Set the email address
     *
     * This is in fact equivalent to setPath() - but provides a more clear interface
     *
     * @param  string $email
     * @return Mailto
     */
    public function setEmail($email)
    {
        return $this->setPath($email);
    }

    /**
     * Get the email address
     *
     * This is infact equivalent to getPath() - but provides a more clear interface
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getPath();
    }

    /**
     * Set validator to use when validating email address
     *
     * @return Mailto
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->emailValidator = $validator;
        return $this;
    }

    /**
     * Retrieve validator for use with validating email address
     *
     * If none is currently set, an EmailValidator instance with default options
     * will be used.
     *
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        if (null === $this->emailValidator) {
            $this->setValidator(new EmailValidator());
        }
        return $this->emailValidator;
    }
}
