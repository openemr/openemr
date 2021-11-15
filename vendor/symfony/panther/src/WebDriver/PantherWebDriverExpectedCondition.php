<?php

/*
 * This file is part of the Panther project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\Panther\WebDriver;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;

final class PantherWebDriverExpectedCondition
{
    public static function elementTextNotContains(WebDriverBy $by, string $text): callable
    {
        return static function (WebDriver $driver) use ($by, $text) {
            try {
                $elementText = $driver->findElement($by)->getText();

                return false === strpos($elementText, $text);
            } catch (StaleElementReferenceException $e) {
                return null;
            }
        }
        ;
    }

    public static function elementEnabled(WebDriverBy $by): callable
    {
        return static function (WebDriver $driver) use ($by) {
            try {
                return $driver->findElement($by)->isEnabled();
            } catch (StaleElementReferenceException $e) {
                return null;
            }
        }
        ;
    }

    public static function elementDisabled(WebDriverBy $by): callable
    {
        return static function (WebDriver $driver) use ($by) {
            try {
                return !$driver->findElement($by)->isEnabled();
            } catch (StaleElementReferenceException $e) {
                return null;
            }
        }
        ;
    }

    public static function elementAttributeContains(WebDriverBy $by, string $attribute, string $text): callable
    {
        return static function (WebDriver $driver) use ($by, $attribute, $text) {
            try {
                $attributeValue = $driver->findElement($by)->getAttribute($attribute);

                return null !== $attributeValue && false !== strpos($attributeValue, $text);
            } catch (StaleElementReferenceException $e) {
                return null;
            }
        }
        ;
    }

    public static function elementAttributeNotContains(WebDriverBy $by, string $attribute, string $text): callable
    {
        return static function (WebDriver $driver) use ($by, $attribute, $text) {
            try {
                $attributeValue = $driver->findElement($by)->getAttribute($attribute);

                return null !== $attributeValue && false === strpos($attributeValue, $text);
            } catch (StaleElementReferenceException $e) {
                return null;
            }
        }
        ;
    }
}
