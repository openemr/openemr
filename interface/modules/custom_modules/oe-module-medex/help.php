<?php
/**
 * Route Module Manager help icon to the guided setup/help flow.
 */

$site = $_GET['site'] ?? 'default';
$target = 'show_help_setup.php?site=' . urlencode((string)$site);
header('Location: ' . $target);
exit;
