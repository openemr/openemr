<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\View;

use IntlDateFormatter;

// @codingStandardsIgnoreStart

/**
 * Helper trait for auto-completion of code in modern IDEs.
 *
 * The trait provides convenience methods for view helpers,
 * defined by the laminas-i18n component. It is designed to be used
 * for type-hinting $this variable inside laminas-view templates via doc blocks.
 *
 * The base class is PhpRenderer, followed by the helper trait from
 * the laminas-i18n component. However, multiple helper traits from different
 * Laminas components can be chained afterwards.
 *
 * @example @var \Laminas\View\Renderer\PhpRenderer|\Laminas\I18n\View\HelperTrait $this
 *
 * @method string currencyFormat(float $number, string|null $currencyCode = null, bool|null $showDecimals = null, string|null $locale = null, string|null $pattern = null)
 * @method string dateFormat(\DateTimeInterface|\IntlCalendar|int|array $date, int $dateType = IntlDateFormatter::NONE, int $timeType = IntlDateFormatter::NONE, string|null $locale = null, string|null $pattern = null)
 * @method string numberFormat(int|float $number, int|null $formatStyle = null, int|null $formatType = null, string|null $locale = null, int|null $decimals = null, array|null $textAttributes = null)
 * @method string plural(array|string $strings, int $number)
 * @method string translate(string $message, string|null $textDomain = null, string|null $locale = null)
 * @method string translatePlural(string $singular, string $plural, int $number, string|null $textDomain = null, string|null $locale = null)
 */
trait HelperTrait
{
}
// @codingStandardsIgnoreEnd
