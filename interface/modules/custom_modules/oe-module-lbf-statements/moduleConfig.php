<?php

/**
 * Module Manager configure page.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

require_once dirname(__DIR__, 4) . "/globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\OEGlobalsBag;

$module_config = 1;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo xlt('Not authorized');
    exit;
}

$url = OEGlobalsBag::getInstance()->getWebRoot()
    . '/interface/modules/custom_modules/oe-module-lbf-statements/public/admin.php';
?>
<div class="container-fluid p-3">
    <p><?php echo xlt('This module has no extra site settings. Rules and the paragraph field are edited from Modules → Form statements.'); ?></p>
    <p><a href="<?php echo attr($url); ?>"><?php echo xlt('Open Form statement rules'); ?></a></p>
</div>
