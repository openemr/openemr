<?php

namespace Laminas\Code;

use Laminas\Code\Exception\InvalidArgumentException;

class DeclareStatement
{
    public const TICKS = 'ticks';
    public const STRICT_TYPES = 'strict_types';
    public const ENCODING = 'encoding';

    private const ALLOWED = [
        self::TICKS        => 'integer',
        self::STRICT_TYPES => 'integer',
        self::ENCODING     => 'string',
    ];

    /**
     * @var string
     */
    protected $directive;

    /**
     * @var int|string
     */
    protected $value;

    private function __construct(string $directive, $value)
    {
        $this->directive = $directive;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getDirective(): string
    {
        return $this->directive;
    }

    /**
     * @return int|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return self
     */
    public static function ticks(int $value): self
    {
        return new self(self::TICKS, $value);
    }

    /**
     * @param int $value
     * @return self
     */
    public static function strictTypes(int $value): self
    {
        return new self(self::STRICT_TYPES, $value);
    }

    /**
     * @param string $value
     * @return self
     */
    public static function encoding(string $value): self
    {
        return new self(self::ENCODING, $value);
    }

    public static function fromArray(array $config): self
    {
        $directive = key($config);
        $value = $config[$directive];

        if (! isset(self::ALLOWED[$directive])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Declare directive must be one of: %s.',
                    implode(', ', array_keys(self::ALLOWED))
                )
            );
        }

        if (gettype($value) !== self::ALLOWED[$directive]) {
            throw new InvalidArgumentException(
                sprintf(
                    'Declare value invalid. Expected %s, got %s.',
                    self::ALLOWED[$directive],
                    gettype($value)
                )
            );
        }

        $method = str_replace('_', '', lcfirst(ucwords($directive, '_')));

        return self::{$method}($value);
    }

    /**
     * @return string
     */
    public function getStatement(): string
    {
        $value = is_string($this->value) ? '\'' . $this->value . '\'' : $this->value;

        return sprintf('declare(%s=%s);', $this->directive, $value);
    }
}
