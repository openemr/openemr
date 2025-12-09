#!/usr/bin/env php
<?php
/**
 * Standalone test runner for PostCalendar tests
 * This script runs the tests without requiring a database connection
 */

// Autoload composer dependencies
require_once __DIR__ . '/../../../../vendor/autoload.php';

// Mock global functions that might be needed
if (!function_exists('xl')) {
    function xl($text) {
        return $text;
    }
}

if (!function_exists('xlt')) {
    function xlt($text) {
        return $text;
    }
}

if (!function_exists('xla')) {
    function xla($text) {
        return $text;
    }
}

if (!function_exists('attr')) {
    function attr($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES);
    }
}

if (!function_exists('text')) {
    function text($text) {
        return htmlspecialchars($text ?? '', ENT_NOQUOTES);
    }
}

if (!function_exists('attr_js')) {
    function attr_js($text) {
        return json_encode($text);
    }
}

if (!function_exists('js_escape')) {
    function js_escape($text) {
        return json_encode($text);
    }
}

if (!function_exists('is_weekend_day')) {
    function is_weekend_day($day) {
        return ($day == 0 || $day == 6);
    }
}

if (!function_exists('is_holiday')) {
    function is_holiday($date) {
        return false; // Simple mock
    }
}

// Set global webroot
$GLOBALS['webroot'] = '/openemr';
$GLOBALS['fileroot'] = __DIR__ . '/../../../../';

// Run PHPUnit
$phpunit = __DIR__ . '/../../../../vendor/bin/phpunit';
$testFile = __DIR__ . '/PostCalendarTwigExtensionsTest.php';

passthru("$phpunit --no-configuration --bootstrap " . __FILE__ . " $testFile", $returnCode);
exit($returnCode);
