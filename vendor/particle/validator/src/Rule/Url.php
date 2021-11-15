<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Rule;

use Particle\Validator\Rule;

/**
 * This rule is for validating if a the value is a valid URL.
 *
 * @package Particle\Validator\Rule
 */
class Url extends Rule
{
    /**
     * A constant that will be used if the value is not a valid URL.
     */
    const INVALID_URL = 'Url::INVALID_URL';

    /**
     * A constant that will be used if the value is not in a white-listed scheme.
     */
    const INVALID_SCHEME = 'Url::INVALID_SCHEME';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_URL => '{{ name }} must be a valid URL',
        self::INVALID_SCHEME => '{{ name }} must have one of the following schemes: {{ schemes }}',
    ];

    /**
     * @var array
     */
    protected $schemes = [];

    /**
     * Construct the URL rule.
     *
     * @param array $schemes
     */
    public function __construct(array $schemes = [])
    {
        $this->schemes = $schemes;
    }

    /**
     * Validates if the value is a valid URL.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        $url = filter_var($value, FILTER_VALIDATE_URL);

        if ($url !== false) {
            return $this->validateScheme($value);
        }
        return $this->error(self::INVALID_URL);
    }

    /**
     * Validates and returns whether or not the URL has a certain scheme.
     *
     * @param string $value
     * @return bool
     */
    protected function validateScheme($value)
    {
        if (count($this->schemes) > 0 && !in_array(parse_url($value, PHP_URL_SCHEME), $this->schemes)) {
            return $this->error(self::INVALID_SCHEME);
        }
        return true;
    }

    /**
     * @return array
     */
    protected function getMessageParameters()
    {
        return array_merge(parent::getMessageParameters(), [
            'schemes' => implode(', ', $this->schemes)
        ]);
    }
}
