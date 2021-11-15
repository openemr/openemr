<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Rule;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Particle\Validator\Rule;

/**
 * This Rule is for validating a phone number.
 *
 * @package Particle\Validator\Rule
 */
class Phone extends Rule
{
    /**
     * Constants that will be used when an invalid phone number is passed.
     */
    const INVALID_VALUE = 'Phone::INVALID_VALUE';
    const INVALID_FORMAT = 'Phone::INVALID_FORMAT';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_VALUE => '{{ name }} must be a valid phone number',
        self::INVALID_FORMAT => '{{ name }} must have a valid phone number format',
    ];

    /**
     * @var string
     */
    protected $countryCode;

    /**
     * Construct the Phone validator.
     *
     * @param string $countryCode
     */
    public function __construct($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * Validates if $value is a valid phone number.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $numberProto = $phoneUtil->parse($value, $this->countryCode);
            if (!$phoneUtil->isValidNumberForRegion($numberProto, $this->countryCode)) {
                return $this->error(self::INVALID_VALUE);
            }
        } catch (NumberParseException $e) {
            return $this->error(self::INVALID_FORMAT);
        }

        return true;
    }
}
