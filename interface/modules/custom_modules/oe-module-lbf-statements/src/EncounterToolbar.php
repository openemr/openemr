<?php

/**
 * Inject a Generate control on encounter forms that have statement rules.
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
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $label = json_encode(xl('Form statements'), JSON_THROW_ON_ERROR);
        echo "<script>\n"
            . "(function() {\n"
            . "  const items = " . $json . ";\n"
            . "  const label = " . $label . ";\n"
            . "  items.forEach(function(item) {\n"
            . "    const holder = document.getElementById(item.holder);\n"
            . "    if (!holder) { return; }\n"
            . "    const bar = holder.querySelector('.form_header_controls');\n"
            . "    if (!bar || bar.querySelector('.lbf-stmt-btn')) { return; }\n"
            . "    const a = document.createElement('a');\n"
            . "    a.className = 'btn btn-text btn-sm lbf-stmt-btn';\n"
            . "    a.href = item.url;\n"
            . "    a.target = '_blank';\n"
            . "    a.title = label;\n"
            . "    a.textContent = label;\n"
            . "    a.addEventListener('click', function() { if (top.restoreSession) { top.restoreSession(); } });\n"
            . "    bar.appendChild(a);\n"
            . "  });\n"
            . "})();\n"
            . "</script>\n";
    }
}
