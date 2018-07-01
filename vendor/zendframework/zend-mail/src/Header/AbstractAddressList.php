<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Header;

use TrueBV\Exception\OutOfBoundsException;
use TrueBV\Punycode;
use Zend\Mail\Address;
use Zend\Mail\AddressList;
use Zend\Mail\Headers;

/**
 * Base class for headers composing address lists (to, from, cc, bcc, reply-to)
 */
abstract class AbstractAddressList implements HeaderInterface
{
    /**
     * @var AddressList
     */
    protected $addressList;

    /**
     * @var string Normalized field name
     */
    protected $fieldName;

    /**
     * Header encoding
     *
     * @var string
     */
    protected $encoding = 'ASCII';

    /**
     * @var string lower case field name
     */
    protected static $type;

    /**
     * @var Punycode|null
     */
    private static $punycode;

    public static function fromString($headerLine)
    {
        list($fieldName, $fieldValue) = GenericHeader::splitHeaderLine($headerLine);
        if (strtolower($fieldName) !== static::$type) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid header line for "%s" string',
                __CLASS__
            ));
        }

        // split value on ","
        $fieldValue = str_replace(Headers::FOLDING, ' ', $fieldValue);
        $fieldValue = preg_replace('/[^:]+:([^;]*);/', '$1,', $fieldValue);
        $values = ListParser::parse($fieldValue);

        $wasEncoded = false;
        $addresses = array_map(
            function ($value) use (&$wasEncoded) {
                $decodedValue = HeaderWrap::mimeDecodeValue($value);
                $wasEncoded = $wasEncoded || ($decodedValue !== $value);

                $value = trim($decodedValue);

                $comments = self::getComments($value);
                $value = self::stripComments($value);

                $value = preg_replace(
                    [
                        '#(?<!\\\)"(.*)(?<!\\\)"#',            // quoted-text
                        '#\\\([\x01-\x09\x0b\x0c\x0e-\x7f])#', // quoted-pair
                    ],
                    [
                        '\\1',
                        '\\1',
                    ],
                    $value
                );

                return empty($value) ? null : Address::fromString($value, $comments);
            },
            $values
        );
        $addresses = array_filter($addresses);

        $header = new static();
        if ($wasEncoded) {
            $header->setEncoding('UTF-8');
        }

        /** @var AddressList $addressList */
        $addressList = $header->getAddressList();
        foreach ($addresses as $address) {
            $addressList->add($address);
        }

        return $header;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Safely convert UTF-8 encoded domain name to ASCII
     * @param string $domainName the UTF-8 encoded email
     * @return string
     */
    protected function idnToAscii($domainName)
    {
        if (null === self::$punycode) {
            self::$punycode = new Punycode();
        }
        try {
            return self::$punycode->encode($domainName);
        } catch (OutOfBoundsException $e) {
            return $domainName;
        }
    }

    public function getFieldValue($format = HeaderInterface::FORMAT_RAW)
    {
        $emails   = [];
        $encoding = $this->getEncoding();

        foreach ($this->getAddressList() as $address) {
            $email = $address->getEmail();
            $name  = $address->getName();

            if (! empty($name) && false !== strstr($name, ',')) {
                $name = sprintf('"%s"', $name);
            }

            if ($format === HeaderInterface::FORMAT_ENCODED
                && 'ASCII' !== $encoding
            ) {
                if (! empty($name)) {
                    $name = HeaderWrap::mimeEncodeValue($name, $encoding);
                }

                if (preg_match('/^(.+)@([^@]+)$/', $email, $matches)) {
                    $localPart = $matches[1];
                    $hostname  = $this->idnToAscii($matches[2]);
                    $email = sprintf('%s@%s', $localPart, $hostname);
                }
            }

            if (empty($name)) {
                $emails[] = $email;
            } else {
                $emails[] = sprintf('%s <%s>', $name, $email);
            }
        }

        // Ensure the values are valid before sending them.
        if ($format !== HeaderInterface::FORMAT_RAW) {
            foreach ($emails as $email) {
                HeaderValue::assertValid($email);
            }
        }

        return implode(',' . Headers::FOLDING, $emails);
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set address list for this header
     *
     * @param  AddressList $addressList
     */
    public function setAddressList(AddressList $addressList)
    {
        $this->addressList = $addressList;
    }

    /**
     * Get address list managed by this header
     *
     * @return AddressList
     */
    public function getAddressList()
    {
        if (null === $this->addressList) {
            $this->setAddressList(new AddressList());
        }
        return $this->addressList;
    }

    public function toString()
    {
        $name  = $this->getFieldName();
        $value = $this->getFieldValue(HeaderInterface::FORMAT_ENCODED);
        return (empty($value)) ? '' : sprintf('%s: %s', $name, $value);
    }

    /**
     * Retrieve comments from value, if any.
     *
     * Supposed to be private, protected as a workaround for PHP bug 68194
     *
     * @param string $value
     * @return string
     */
    protected static function getComments($value)
    {
        $matches = [];
        preg_match_all(
            '/\\(
                (?P<comment>(
                    \\\\.|
                    [^\\\\)]
                )+)
            \\)/x',
            $value,
            $matches
        );
        return isset($matches['comment']) ? implode(', ', $matches['comment']) : '';
    }

    /**
     * Strip all comments from value, if any.
     *
     * Supposed to be private, protected as a workaround for PHP bug 68194
     *
     * @param string $value
     * @return void
     */
    protected static function stripComments($value)
    {
        return preg_replace(
            '/\\(
                (
                    \\\\.|
                    [^\\\\)]
                )+
            \\)/x',
            '',
            $value
        );
    }
}
