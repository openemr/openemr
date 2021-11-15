<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Rule;

use Exception;
use Particle\Validator\Rule;

/**
 * This rule is for validating if a value is a valid cryptographic hash.
 *
 * @package Particle\Validator\Rule
 */
class Hash extends Rule
{
    const ALGO_MD5 = 'md5';
    const ALGO_SHA1 = 'sha1';
    const ALGO_SHA256 = 'sha256';
    const ALGO_SHA512 = 'sha512';
    const ALGO_CRC32 = 'crc32';

    /**
     * A constant that will be used when the value is not a valid cryptographic hash.
     */
    const INVALID_FORMAT = 'Hash::INVALID_FORMAT';

    /**
     * constant to set the allow uppercase to true
     */
    const ALLOW_UPPERCASE = true;

    /**
     * constant to set the allow uppercase to false
     */
    const DISALLOW_UPPERCASE = false;

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_FORMAT => '{{ name }} must be a valid hash'
    ];

    /**
     * @var string
     */
    protected $hashAlgorithm;

    /**
     * @var bool
     */
    protected $allowUppercase;

    /**
     * Construct the Hash validator.
     *
     * @param string $hashAlgorithm
     * @param bool $allowUppercase
     */
    public function __construct($hashAlgorithm, $allowUppercase = self::DISALLOW_UPPERCASE)
    {
        $this->hashAlgorithm = $hashAlgorithm;
        $this->allowUppercase = $allowUppercase;

        $this->messageTemplates = [
            self::INVALID_FORMAT => sprintf('{{ name }} must be a valid %s hash', $hashAlgorithm)
        ];
    }

    /**
     * Validates if the value is a valid cryptographic hash.
     *
     * @param mixed $value
     * @return bool
     * @throws Exception
     */
    public function validate($value)
    {
        $algorithmsLengths = [
            self::ALGO_MD5 => 32,
            self::ALGO_SHA1 => 40,
            self::ALGO_SHA256 => 64,
            self::ALGO_SHA512 => 128,
            self::ALGO_CRC32 => 8,
        ];

        if (!isset($algorithmsLengths[$this->hashAlgorithm])) {
            throw new Exception('an invalid hashAlgorithm has been provided.');
        }

        if ($this->validateHexString($value, $algorithmsLengths[$this->hashAlgorithm])) {
            return true;
        }

        return $this->error(self::INVALID_FORMAT);
    }

    /**
     * @param string $value
     * @param int $length
     *
     * @return bool
     */
    private function validateHexString($value, $length)
    {
        $caseSensitive = $this->allowUppercase ? 'i' : '';

        return preg_match(sprintf('/^[0-9a-f]{%s}$/%s', $length, $caseSensitive), $value) === 1;
    }
}
