<?php

/**
 * Alias for PantherTestCase to resolve PHPStan static analysis issues.
 *
 * PantherTestCase in Symfony dynamically inherits from either WebTestCase
 * or PHPUnit's TestCase depending on the runtime context. This dynamic
 * inheritance causes PHPStan to not understand the inheritance chain and
 * reject inherited methods from classes that extend PantherTestCase.
 *
 * This alias provides a static type hint that PHPStan can understand,
 * allowing proper static analysis of inherited methods while maintaining
 * the same functionality as the original PantherTestCase.
 */

class_alias(\PHPUnit\Framework\TestCase::class, '\Symfony\Bundle\FrameworkBundle\Test\WebTestCase');
