<?php

/**
 * Handles the display of the telecom list datatype in LBF
 * Updated to use ContactService and ContactTelecomService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\ContactService;
use OpenEMR\Services\ContactTelecomService;
use OpenEMR\Common\Twig\TwigContainer;

// Initialize services
$contactService = new ContactService();
$telecomService = new ContactTelecomService();
// Get contact for entity
$foreign_table ??= '';
$foreign_id ??= 0;
$contact = $contactService->getOrCreateForEntity($foreign_table, $foreign_id);
// Transfer records to array
$telecoms = [];
if ($contact) {
// Get all telecoms for this contact
    $telecomRecords = $telecomService->getTelecomsForContact($contact->get_id(), true);
    foreach ($telecomRecords as $record) {
        $telecoms[] = [
            'id' => $record['id'],
            'contact_id' => $record['contact_id'],
            'system' => $record['system'] ?? 'phone',
            'use' => $record['use'] ?? 'home',
            'value' => $record['value'] ?? '',
            'rank' => $record['rank'] ?? 1,
            'notes' => $record['notes'] ?? '',
            'status' => $record['status'] ?? 'A',
            'is_primary' => $record['is_primary'] ?? 'N',
            'period_start' => $record['period_start'] ?? '',
            'period_end' => $record['period_end'] ?? ''
        ];
    }
}

// Get list options for dropdowns
$list_telecom_systems = generate_list_map("telecom_systems");
$list_telecom_uses = generate_list_map("telecom_uses");
// Generate unique table ID
$table_id = uniqid("table_text_telecoms_");
// Prepare template variables
$templateVars = [
    'foreign_table' => $foreign_table,
    'table_id' => $table_id,
    'telecoms' => $telecoms,
    'list_telecom_systems' => $list_telecom_systems,
    'list_telecom_uses' => $list_telecom_uses,
    'has_telecoms' => !empty($telecoms)
];
// Render Twig template
$twigContainer = new TwigContainer(null, $GLOBALS['kernel']);
$twig = $twigContainer->getTwig();
echo $twig->render('patient/demographics/telecom_display.html.twig', $templateVars);
