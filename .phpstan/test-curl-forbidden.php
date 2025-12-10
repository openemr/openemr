<?php

/**
 * Test file to verify ForbiddenCurlFunctionsRule
 *
 * This file should NOT pass PHPStan analysis because it contains forbidden curl_* functions.
 * Run: vendor/bin/phpstan analyze .phpstan/test-curl-forbidden.php
 *
 * @package   OpenEMR
 */

namespace OpenEMR\PHPStan\Tests;

class TestCurlForbidden
{
    /**
     * This function should trigger PHPStan errors for each curl_* function
     */
    public function testCurlFunctionsAreForbidden()
    {
        // These should all be flagged by PHPStan
        $ch = curl_init('https://example.com'); // Should fail
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Should fail
        $response = curl_exec($ch); // Should fail
        $info = curl_getinfo($ch); // Should fail
        $errno = curl_errno($ch); // Should fail
        $error = curl_error($ch); // Should fail
        curl_close($ch); // Should fail
        
        // Additional curl functions that should also be caught
        $mh = curl_multi_init(); // Should fail
        
        return $response;
    }
}
