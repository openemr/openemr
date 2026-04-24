<?php
/**
 * MedEx Module - Public Entry Point
 * Redirects to the thin SaaS-first admin shell.
 */

require_once(__DIR__ . '/../../../../globals.php');

$siteId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_GET['site'] ?? ($_SESSION['site_id'] ?? 'default')));
if ($siteId === '') {
    $siteId = 'default';
}

$webroot = (string)($GLOBALS['webroot'] ?? ($GLOBALS['web_root'] ?? ''));
$redirect = $webroot . '/interface/modules/custom_modules/oe-module-medex/admin/index.php?site=' . urlencode($siteId);

header('Location: ' . $redirect);
exit;
