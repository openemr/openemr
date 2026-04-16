<?php

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

$siteId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_GET['site'] ?? 'default'));
if ($siteId === '') {
    $siteId = 'default';
}

header('Location: index.php?site=' . urlencode($siteId) . '&cloud=1');
exit;
