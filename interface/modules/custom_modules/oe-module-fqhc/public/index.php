<?php

/**
 * FQHC module — host page (Step 1).
 *
 * Renders the design-system showcase: the server-rendered OpenEMR shell with
 * the FQHC Twig content and Web Component islands. Demonstrates the
 * certification-safe UI pattern (additive module page, no certified code
 * touched) and is the seed for the UDS Patient Snapshot (#14).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../../../globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FQHC\DesignSystem\DesignSystemAssets;
use OpenEMR\Common\Twig\TwigContainer;

if (!AclMain::aclCheckCore('patients', 'demo')) {
    echo xlt('Access denied');
    exit;
}

$globals = OEGlobalsBag::getInstance();
$publicBaseUrl = $globals->get('webroot') . '/interface/modules/custom_modules/oe-module-fqhc/public';
$assets = new DesignSystemAssets(__DIR__, $publicBaseUrl);

$twigContainer = new TwigContainer(__DIR__ . '/../templates', $globals->getKernel());
$content = $twigContainer->getTwig()->render('fqhc/showcase.html.twig', [
    'heading' => xl('FQHC'),
    'subheading' => xl('UDS data capture in a modern, responsive interface'),
    // Sample demographics so the layout reads as real; the live Snapshot (#14)
    // pulls these from patient_data.
    'demographics' => [
        ['label' => xl('Age / sex'), 'value' => '47 · Female'],
        ['label' => xl('Race'), 'value' => xl('Black or African American')],
        ['label' => xl('Ethnicity'), 'value' => xl('Not Hispanic or Latino')],
        ['label' => xl('Preferred language'), 'value' => xl('Spanish')],
        ['label' => xl('ZIP code'), 'value' => '78207'],
    ],
]);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('FQHC'); ?></title>
    <?php Header::setupHeader(['common']); ?>
    <?php foreach ($assets->styleUrls() as $styleUrl) { ?>
        <link rel="stylesheet" href="<?php echo attr($styleUrl); ?>">
    <?php } ?>
</head>
<body class="body_top">
    <?php echo $content; ?>
    <?php foreach ($assets->scriptUrls() as $scriptUrl) { ?>
        <script type="module" src="<?php echo attr($scriptUrl); ?>"></script>
    <?php } ?>
</body>
</html>
