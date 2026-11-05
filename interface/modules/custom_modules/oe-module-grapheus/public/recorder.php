<?php

/**
 * Grapheus recorder window (a top-level window, because browsers block the
 * microphone inside OpenEMR's frames).
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once dirname(__FILE__, 5) . "/globals.php";
require_once dirname(__DIR__) . '/src/Compat.php';
(new \OpenEMR\Core\ModulesClassLoader(\Exetazo\Grapheus\Compat::fileroot()))->registerNamespaceIfNotExists('Exetazo\\Grapheus\\', dirname(__DIR__) . '/src');

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('encounters', 'notes', '', 'write') && !AclMain::aclCheckCore('encounters', 'notes_a', '', 'write')) {
    echo xlt('Not authorized');
    exit;
}
$base = \Exetazo\Grapheus\Compat::moduleUrl();
$query = \Exetazo\Grapheus\Compat::request()->query;
$mode = \Exetazo\Grapheus\Val::str($query->get('mode')) === 'telehealth' ? 'telehealth' : 'in_person';
$ptype = \Exetazo\Grapheus\Val::str($query->get('ptype')) === 'new' ? 'new' : 'established';
$prep = max(0, min(240, \Exetazo\Grapheus\Val::int($query->get('prep'))));
$sessPid = \Exetazo\Grapheus\Val::int(\Exetazo\Grapheus\Compat::get('pid', 0));
$patient = $sessPid > 0 ? \Exetazo\Grapheus\Val::map(getPatientData($sessPid, 'fname, lname')) : [];
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo xlt('Grapheus recorder'); ?></title>
    <link rel="stylesheet" href="<?php echo attr($base); ?>/assets/grapheus.css?v=1">
</head>
<body class="g-rec">
<main id="rec" data-api="<?php echo attr($base . '/api.php'); ?>" data-csrf="<?php echo attr(\Exetazo\Grapheus\Compat::csrfToken()); ?>"
      data-mode="<?php echo attr($mode); ?>" data-ptype="<?php echo attr($ptype); ?>" data-prep="<?php echo attr((string) $prep); ?>">
    <div class="g-rec-head"><b>Grapheus</b> <span><?php echo text(trim(\Exetazo\Grapheus\Val::str($patient['fname'] ?? '') . ' ' . \Exetazo\Grapheus\Val::str($patient['lname'] ?? ''))); ?></span></div>
    <div class="g-rec-state"><span class="g-dot" id="dot"></span><span id="state"><?php echo xlt('Starting…'); ?></span></div>
    <div class="g-timer" id="timer">0:00</div>
    <div class="g-meter"><div id="level"></div></div>
    <div class="g-rec-buttons"><button id="pause" disabled><?php echo xlt('Pause'); ?></button><button id="stop" class="g-stop" disabled><?php echo xlt('Stop & draft'); ?></button></div>
    <p class="g-small" id="msg"><?php echo xlt('Keep this window open. It stops by itself after 5 silent minutes or 90 minutes.'); ?></p>
</main>
<script src="<?php echo attr($base); ?>/assets/grapheus-recorder.js?v=1"></script>
</body>
</html>
