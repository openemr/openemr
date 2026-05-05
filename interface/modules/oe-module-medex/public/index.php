<?php
/**
 * MedEx Module - Public Entry Point
 * Redirects to settings page
 */

require_once(__DIR__ . '/../../../../globals.php');

$webroot = $GLOBALS['web_root'] ?? '';
$redirect = $webroot . '/interface/modules/custom_modules/oe-module-medex/admin/settings.php';

header('Location: ' . $redirect);
exit;
