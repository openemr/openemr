<?php

/**
 * Grapheus Assistant: set up OpenEMR, "show me how", and daily tasks, by chat.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once dirname(__FILE__, 5) . "/globals.php";
require_once dirname(__DIR__) . '/src/Compat.php';
(new \OpenEMR\Core\ModulesClassLoader(\Exetazo\Grapheus\Compat::fileroot()))->registerNamespaceIfNotExists('Exetazo\\Grapheus\\', dirname(__DIR__) . '/src');

use OpenEMR\Core\Header;

$base = \Exetazo\Grapheus\Compat::moduleUrl();
?>
<!doctype html>
<html>
<head>
    <title><?php echo xlt('Grapheus Assistant'); ?></title>
    <?php Header::setupHeader(); ?>
    <link rel="stylesheet" href="<?php echo attr($base); ?>/assets/grapheus.css?v=2">
</head>
<body class="body_top">
<div id="grapheus" class="container py-3" data-api="<?php echo attr($base . '/api.php'); ?>" data-csrf="<?php echo attr(\Exetazo\Grapheus\Compat::csrfToken()); ?>">
    <div class="d-flex align-items-center mb-2">
        <h4 class="mb-0 mr-2">Grapheus Assistant</h4><small class="text-muted"><?php echo xlt('by Exetazo'); ?></small>
        <span class="ml-auto small text-muted" id="a-role"></span>
    </div>
    <div id="g-msg" class="alert d-none" role="status"></div>
    <div id="a-connect" class="card mb-3 d-none"><div class="card-body">
        <p class="mb-2" id="a-connect-text"></p>
        <button class="btn btn-primary d-none" id="a-connect-btn"><?php echo xlt('Connect the practice Grapheus account'); ?></button>
    </div></div>
    <div id="a-chat" class="d-none">
        <p class="small text-muted mb-2"><?php echo xlt('Examples: "Set it up like a small family-practice telehealth clinic in Missouri" · "Show me how to print a superbill" · "Schedule Molly Smith with Dr. Bob tomorrow at 2:30"'); ?></p>
        <div id="a-thread" class="g-thread mb-2"></div>
        <form id="a-form" class="d-flex">
            <textarea id="a-input" class="form-control mr-2" rows="2" placeholder="<?php echo xla('What would you like?'); ?>"></textarea>
            <button class="btn btn-primary" id="a-send"><?php echo xlt('Send'); ?></button>
        </form>
        <p class="small text-muted mt-1"><?php echo xlt('Nothing changes until you approve it. Each request is billed to the practice\'s Grapheus account.'); ?></p>
    </div>
    <div id="a-admin" class="d-none mt-4">
        <h6><?php echo xlt('Changes made with Grapheus'); ?></h6>
        <div class="table-responsive"><table class="table table-sm small" id="a-log"></table></div>
        <label class="small"><input type="checkbox" id="a-admins-only"> <?php echo xlt('Only administrators may use the Assistant'); ?></label>
        <button class="btn btn-sm btn-link" id="a-disconnect"><?php echo xlt('Disconnect the practice account'); ?></button>
    </div>
</div>
<script src="<?php echo attr($base); ?>/assets/grapheus-assistant.js?v=1"></script>
</body>
</html>
