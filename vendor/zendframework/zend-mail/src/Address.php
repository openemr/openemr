<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail;

use Zend\Validator\EmailAddress as EmailAddressValidator;
use Zend\Validator\Hostname;

class Address implements Address\AddressInterface
{
    protected $comment;
    protected $email;
    protected $name;

    /**
     * Create an instance from a string value.
     *
     * Parses a string representing a single address. If it is a valid format,
     * it then creates and returns an instance of itself using the name and
     * email it has parsed from the value.
     *
     * @param string $address
     * @param null|string $comment Comment associated with the address, if any.
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public static function fromString($address, $comment = null)
    {
        if (! preg_match('/^((?P<name>.*)<(?P<namedEmail>[^>]+)>|(?P<email>.+))$/', $address, $matches)) {
            throw new Exception\InvalidArgumentException('Invalid address format');
        }

        $name = null;
        if (isset($matches['name'])) {
            $name = trim($matches['name']);
        }
        if (empty($name)) {
            $name = null;
        }

        if (isset($matches['namedEmail'])) {
            $email = $matches['namedEmail'];
        }
        if (isset($matches['email'])) {
            $email = $matches['email'];
        }
        $email = trim($email);

        return new static($email, $name, $comment);
    }

    /**
     * Constructor
     *
     * @param  string $email
     * @param  null|string $name
     * @param  null|string $comment
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($email, $name = null, $comment = null)
    {
        $emailAddressValidator = new EmailAddressValidator(Hostname::ALLOW_DNS | Hostname::ALLOW_LOCAL);
        if (! is_string($email) || empty($email)) {
            throw new Exception\InvalidArgumentException('Email must be a valid email address');
        }

        if (preg_match("/[\r\n]/", $email)) {
            throw new Exception\InvalidArgumentException('CRLF injection detected');
        }

        if (! $emailAddressValidator->isValid($email)) {
            $invalidMessages = $emailAddressValidator->getMessages();
            throw new Exception\InvalidArgumentException(array_shift($invalidMessages));
        }

        if (null !== $name) {
            if (! is_string($name)) {
                throw new Exception\InvalidArgumentException('Name must be a string');
            }

            if (preg_match("/[\r\n]/", $name)) {
                throw new Exception\InvalidArgumentException('CRLF injection detected');
            }

            $this->name = $name;
        }

        $this->email = $email;

        if (null !== $comment) {
            $this->comment = $comment;
        }
    }

    /**
     * Retrieve email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Retrieve name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Retrieve comment, if any
     *
     * @return null|string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * String representation of address
     *
     * @return string
     */
    public function toString()
    {
        $string = sprintf('<%s>', $this->getEmail());
        $name   = $this->constructName();
        if (null === $name) {
            return $string;
        }

        return sprintf('%s %s', $name, $string);
    }

    /**
     * Constructs the name string
     *
     * If a comment is present, appends the comment (commented using parens) to
     * the name before returning it; otherwise, returns just the name.
     *
     * @return null|string
     */
    private function constructName()
    {
        $name = $this->getName();
        $comment = $this->getComment();

        if ($comment === null || $comment === '') {
            return $name;
        }

        $string = sprintf('%s (%s)', $name, $comment);
        return trim($string);
    }
}
