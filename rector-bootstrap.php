<?php

/**
 * Rector Bootstrap File
 *
 * Sets up environment for Rector static analysis.
 */

// Set a flag to indicate we're in a static analysis context
// This prevents code that requires runtime resources (like database connections)
// from executing during static analysis
define('OPENEMR_STATIC_ANALYSIS', true);

// Set default site directory for files that need it
$GLOBALS['OE_SITE_DIR'] = __DIR__ . '/sites/default';
