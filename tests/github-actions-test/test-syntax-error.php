<?php
/**
 * Test file for GitHub Actions PHP syntax checker
 * This file intentionally contains a syntax error to test the problem matcher
 * 
 * To fix: Uncomment the line below
 */

// Intentional syntax error: missing closing quote
$testVariable = "This is a test string that is missing its closing quote;

echo "This line should never be reached due to syntax error above";
