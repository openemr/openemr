<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\Strategy;

use DateTime;
use DateTimeInterface;
use DateTimeZone;

use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function preg_replace;
use function sprintf;

final class DateTimeFormatterStrategy implements StrategyInterface
{
    /**
     * Format to use during hydration.
     *
     * @var string
     */
    private $format;

    /**
     * @var DateTimeZone|null
     */
    private $timezone;

    /**
     * Format to use during extraction.
     *
     * Removes any special anchor characters used to ensure that creation of a
     * `DateTime` instance uses the formatted time string (which is useful
     * during hydration).  These include `!` at the beginning of the string and
     * `|` at the end.
     *
     * @var string
     */
    private $extractionFormat;

    /**
     * Whether or not to allow hydration of values that do not follow the format exactly.
     *
     * @var bool
     */
    private $dateTimeFallback;

    /**
     * @param bool $dateTimeFallback try to parse with DateTime when createFromFormat fails
     * @throws Exception\InvalidArgumentException for invalid $format values
     */
    public function __construct(
        string $format = DateTime::RFC3339,
        ?DateTimeZone $timezone = null,
        bool $dateTimeFallback = false
    ) {
        $this->format           = $format;
        $this->timezone         = $timezone;
        $this->dateTimeFallback = $dateTimeFallback;

        $extractionFormat = preg_replace('/(?<![\\\\])[+|!\*]/', '', $this->format);
        if (null === $extractionFormat) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Format provided (%s) contains invalid characters; please verify the format',
                $format
            ));
        }

        $this->extractionFormat = $extractionFormat;
    }

    /**
     * {@inheritDoc}
     *
     * Converts to date time string
     *
     * @param mixed|DateTimeInterface $value
     * @return mixed|string If a non-DateTimeInterface $value is provided, it
     *     will be returned unmodified; otherwise, it will be extracted to a
     *     string.
     */
    public function extract($value, ?object $object = null)
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format($this->extractionFormat);
        }

        return $value;
    }

    /**
     * Converts date time string to DateTime instance for injecting to object
     *
     * {@inheritDoc}
     *
     * @param mixed|string $value
     * @return mixed|DateTimeInterface
     * @throws Exception\InvalidArgumentException if $value is not null, not a
     *     string, nor a DateTimeInterface.
     */
    public function hydrate($value, ?array $data = null)
    {
        if ($value === '' || $value === null || $value instanceof DateTimeInterface) {
            return $value;
        }

        if (! is_string($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unable to hydrate. Expected null, string, or DateTimeInterface; %s was given.',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        $hydrated = $this->timezone
            ? DateTime::createFromFormat($this->format, $value, $this->timezone)
            : DateTime::createFromFormat($this->format, $value);

        if ($hydrated === false && $this->dateTimeFallback) {
            $hydrated = $this->timezone
                ? new DateTime($value, $this->timezone)
                : new DateTime($value);
        }

        return $hydrated ?: $value;
    }
}
