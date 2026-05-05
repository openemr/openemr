<?php
/**
 * PDF Template Editor - Redirect to SaaS wrapper
 * 
 * This file now redirects to index.php which loads the MedExBank editor via iframe
 */

require_once(__DIR__ . '/../../../../../globals.php');

// Verify user is authenticated  
if (!isset($_SESSION['authUserID'])) {
    die('Access denied. Please log in to OpenEMR.');
}

// Redirect to index.php with page=editor
$redirectUrl = 'index.php?page=editor';

// Preserve template_id if editing existing
if (!empty($_GET['template_id'])) {
    $redirectUrl .= '&template_id=' . urlencode($_GET['template_id']);
}

header("Location: $redirectUrl");
exit;
