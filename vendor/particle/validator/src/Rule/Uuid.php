<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Rule;

/**
 * This rule is for validating if a the value is a valid UUIDv4.
 *
 * @package Particle\Validator\Rule
 */
class Uuid extends Regex
{
    /**
     * A constant that will be used if the value is not a valid UUIDv4.
     */
    const INVALID_UUID = 'Uuid::INVALID_UUID';

    /**
     * UUID NIL & version binary masks
     */
    const UUID_VALID = 0b0000100;
    const UUID_NIL   = 0b0000001;
    const UUID_V1    = 0b0000010;
    const UUID_V2    = 0b0001000;
    const UUID_V3    = 0b0010000;
    const UUID_V4    = 0b0100000;
    const UUID_V5    = 0b1000000;

    /**
     * An array of all validation regexes.
     *
     * @var array
     */
    protected $regexes = [
        self::UUID_VALID => '~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$~i',
        self::UUID_NIL   => '~^[0]{8}-[0]{4}-[0]{4}-[0]{4}-[0]{12}$~i',
        self::UUID_V1    => '~^[0-9a-f]{8}-[0-9a-f]{4}-1[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i',
        self::UUID_V2    => '~^[0-9a-f]{8}-[0-9a-f]{4}-2[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i',
        self::UUID_V3    => '~^[0-9a-f]{8}-[0-9a-f]{4}-3[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i',
        self::UUID_V4    => '~^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i',
        self::UUID_V5    => '~^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i',
    ];

    /**
     * An array of names for all the versions
     *
     * @var array
     */
    protected $versionNames = [
        self::UUID_VALID => 'valid format',
        self::UUID_NIL => 'NIL',
        self::UUID_V1 => 'v1',
        self::UUID_V2 => 'v2',
        self::UUID_V3 => 'v3',
        self::UUID_V4 => 'v4',
        self::UUID_V5 => 'v5',
    ];

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_UUID => '{{ name }} must be a valid UUID ({{ version }})'
    ];

    /**
     * The version of the UUID you'd like to check.
     *
     * @var int
     */
    protected $version;

    /**
     * Construct the UUID validation rule.
     *
     * @param int $version
     */
    public function __construct($version = self::UUID_VALID)
    {
        if ($version >= (self::UUID_V5 * 2) || $version < 0) {
            throw new \InvalidArgumentException(
                'Invalid UUID version mask given. Please choose one of the constants on the Uuid class.'
            );
        }

        $this->version = $version;
    }

    /**
     * Validates if the value is a valid UUID of an allowed version.
     *
     * @param string $value
     * @return bool
     */
    public function validate($value)
    {
        foreach ($this->regexes as $version => $regex) {
            if (($version & $this->version) === $version && preg_match($regex, $value) > 0) {
                return true;
            }
        }
        return $this->error(self::INVALID_UUID);
    }

    /**
     * Returns the parameters that may be used in a validation message.
     *
     * @return array
     */
    protected function getMessageParameters()
    {
        $versions = [];
        foreach (array_keys($this->regexes) as $version) {
            if (($version & $this->version) === $version) {
                $versions[] = $this->versionNames[$version];
            }
        }

        return array_merge(parent::getMessageParameters(), [
            'version' => implode(', ', $versions)
        ]);
    }
}
