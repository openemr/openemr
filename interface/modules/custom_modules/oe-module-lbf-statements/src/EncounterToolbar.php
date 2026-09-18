<?php

/**
 * Form statements button on the encounter form list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Encounter\EncounterFormsListRenderEvent;

class EncounterToolbar
{
    /**
     * Add a Form statements button next to each eligible encounter LBF.
     */
    public function onFormsListRender(EncounterFormsListRenderEvent $event): void
    {
        if (!AclMain::aclCheckCore('encounters', 'notes')) {
            return;
        }
        $pid = $event->getPid() ?? 0;
        $encounter = $event->getEncounter() ?? 0;
        if ($pid <= 0 || $encounter <= 0) {
            return;
        }
        $formIds = (new StatementRepository())->formIdsWithRules();
        $items = (new LbfReader())->instancesOnEncounter($pid, $encounter, $formIds);
        if ($items === []) {
            return;
        }
        $base = OEGlobalsBag::getInstance()->getWebRoot() . Bootstrap::MODULE_INSTALLATION_PATH
            . Bootstrap::MODULE_NAME . '/public/index.php';
        $payload = [];
        foreach ($items as $item) {
            $payload[] = [
                'holder' => $item['form_id'] . '~' . $item['instance_id'],
                'url' => $base . '?form_id=' . rawurlencode($item['form_id'])
                    . '&pid=' . $pid . '&instance_id=' . $item['instance_id'],
            ];
        }
        $flags = JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        $json = json_encode(
            [
                'items' => $payload,
                'label' => xl('Form statements'),
            ],
            $flags
        );
        $src = OEGlobalsBag::getInstance()->getWebRoot() . Bootstrap::MODULE_INSTALLATION_PATH
            . Bootstrap::MODULE_NAME . '/public/assets/toolbar.js';
        echo "<script>window.lbfStatementsToolbar = " . $json . ";</script>\n"
            . "<script src=\"" . attr($src) . "\"></script>\n";
    }
}
