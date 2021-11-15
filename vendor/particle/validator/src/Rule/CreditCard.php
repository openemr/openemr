<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Rule;

use byrokrat\checkdigit\Luhn;
use Particle\Validator\Rule;

/**
 * This rule is for validating if a value is a valid credit card number.
 *
 * @package Particle\Validator\Rule
 */
class CreditCard extends Rule
{
    /**
     * A constant that will be used when the value is not a valid credit card number.
     */
    const INVALID_FORMAT = 'CreditCard::INVALID_FORMAT';
    const INVALID_CHECKSUM = 'CreditCard::INVALID_CHECKSUM';

    /**
     * Regular expressions to validate the different credit card brands
     *
     * @var array
     */
    protected $validationRegExps = [
        '/^4[0-9]{12}(?:[0-9]{3})?$/', // Visa
        '/^5[1-5][0-9]{14}$/', // Mastercard
        '/^3[47][0-9]{13}$/', // American Express
        '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', // Diners Club
        '/^6(?:011|5[0-9]{2})[0-9]{12}$/', // Discover
        '/^(?:2131|1800|35\d{3})\d{11}$/', // JCB
    ];

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_FORMAT => '{{ name }} must have a valid credit card number format',
        self::INVALID_CHECKSUM => '{{ name }} must be a valid credit card number',
    ];

    /**
     * Validates if the value is a valid credit card number.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (!$this->validateFormat($value)) {
            return $this->error(self::INVALID_FORMAT);
        } elseif (!$this->validateChecksum($value)) {
            return $this->error(self::INVALID_CHECKSUM);
        }

        return true;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function validateChecksum($value)
    {
        $luhn = new Luhn();

        return $luhn->isValid($value);
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function validateFormat($value)
    {
        foreach ($this->validationRegExps as $validationRegExp) {
            if (preg_match($validationRegExp, $value) === 1) {
                return true;
            }
        }

        return false;
    }
}
