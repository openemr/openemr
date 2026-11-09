<?php

/**
 * The reports that came back on referrals, as QDM Communication, Performed.
 *
 * CMS50 (Closing the Referral Loop) counts a referral as closed when a consultant report related
 * to it was received after it, within the measurement period (#14745). Each report's relatedTo is
 * the referral's QDM id (ReferralOrderService::qdmId()), and a referral's reports come two ways:
 *
 * - The reply recorded in the referral's Counter-Referral section: received on its Reply Date (by
 *   the end of that day) and typed as ReferralTaskService files it (LOINC 11488-4 Consult note).
 *   The Reply Date is what marks the referral completed (ReferralService), whether or not the reply
 *   was ever filed as a Task's output, which it isn't when referral Tasks are off, when the
 *   referral was saved with its Reply Date already in, when Refer To isn't a Practitioner or
 *   Organization in FHIR, or when the recipient's Task was final first.
 * - A document one of the referral's own Tasks (ReferralService::OWN_TASK_SQL) returned as a
 *   DocumentReference output, as a recipient adds one through the FHIR API: typed by the output's
 *   type, so the measure's Consultant Report value set decides whether it counts, and received
 *   when the document was filed in the chart (documents.date, the DocumentReference's date). The
 *   reply document ReferralTaskService files for the Reply Date (linked to the referral by
 *   foreign_reference) is that reply, so it isn't read again here. The document has to be in the
 *   referral's patient's chart and not deleted. A Task marked entered-in-error returned nothing that
 *   counts, except one ReferralTaskService set aside because the Reply Date was cleared: what the
 *   recipient attached to it still came back.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\CommunicationPerformed;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\ReferralService;
use OpenEMR\Services\ReferralTaskService;

class ConsultantReportService extends AbstractQdmService implements QdmServiceInterface
{
    /**
     * The consultant reports on referrals: each Reply Date recorded on a referral, and each document a
     * referral's Tasks returned, with when it was received and the report's type.
     */
    public function getSqlStatement(): string
    {
        $form = ReferralService::FORM_ID;
        $replySystem = ReferralTaskService::REPLY_OUTPUT_TYPE['type_system'];
        $replyCode = ReferralTaskService::REPLY_OUTPUT_TYPE['type_code'];
        // Compared byte for byte, as PHP compares it (ReferralService::countsForRecipientSql() does the same).
        $replyCleared = str_replace("'", "''", ReferralTaskService::REASON_REPLY_CLEARED);
        // One report per document a referral's Tasks returned, however many times and under whatever types:
        // typed as a reply when any of its outputs is, else by the byte-smallest system and code, so the
        // choice never varies. A system is a URI, so the first space ends it. Wrapped so that the patient
        // filter executeQuery() appends applies to the referrals' pid.
        return "SELECT `pid`, `id`, `document_uuid`, `received`, `type_system`, `type_code` FROM (
                SELECT t.`pid`, t.`id`, NULL AS `document_uuid`, TRIM(reply.`field_value`) AS `received`,
                    '$replySystem' AS `type_system`, '$replyCode' AS `type_code`
                FROM `transactions` AS t
                JOIN `lbt_data` AS reply ON reply.`form_id` = t.`id` AND reply.`field_id` = 'reply_date'
                WHERE t.`title` = '$form' AND TRIM(reply.`field_value`) REGEXP '" . ReferralOrderService::DATE_PATTERN . "'
                UNION ALL
                SELECT returned.`pid`, returned.`id`, returned.`document_uuid`, returned.`received`,
                    SUBSTRING_INDEX(returned.`type`, ' ', 1), SUBSTRING(returned.`type`, LOCATE(' ', returned.`type`) + 1)
                FROM (
                    SELECT t.`pid`, t.`id`, doc.`uuid` AS `document_uuid`, doc.`date` AS `received`,
                        SUBSTRING(MIN(CAST(CONCAT(
                            IF(CAST(task_out.`type_system` AS BINARY) = '$replySystem' AND CAST(task_out.`type_code` AS BINARY) = '$replyCode', '0', '1'),
                            task_out.`type_system`, ' ', task_out.`type_code`
                        ) AS BINARY)), 2) AS `type`
                    FROM `transactions` AS t
                    JOIN `task` ON task.`focus_type` = 'ServiceRequest' AND task.`focus_uuid` = t.`uuid` AND " . ReferralService::OWN_TASK_SQL . "
                        AND (task.`status` <> 'entered-in-error' OR CAST(COALESCE(task.`status_reason`, '') AS BINARY) = '$replyCleared')
                    JOIN `task_output` AS task_out ON task_out.`task_id` = task.`id`
                        AND task_out.`value_element` = 'valueReference' AND task_out.`value_type` = 'DocumentReference'
                    JOIN `documents` AS doc ON doc.`uuid` = task_out.`value_uuid` AND doc.`foreign_id` = t.`pid` AND doc.`deleted` = 0
                    WHERE t.`title` = '$form' AND doc.`date` IS NOT NULL
                        AND NOT (COALESCE(doc.`foreign_reference_table`, '') = '" . ReferralService::TABLE_NAME . "' AND COALESCE(doc.`foreign_reference_id`, 0) = t.`id`)
                        AND COALESCE(task_out.`type_system`, '') <> '' AND COALESCE(task_out.`type_code`, '') <> ''
                    GROUP BY t.`pid`, t.`id`, doc.`uuid`, doc.`date`
                ) AS returned
            ) AS consultant_reports
            WHERE 1 = 1";
    }

    /**
     * A report as a Communication, Performed related to its referral's order (ReferralOrderService::qdmId()):
     * received when its document was filed, or by the end of its Reply Date. Null when the referral, the
     * time or the type is missing.
     */
    public function makeQdmModel(QdmRecord $recordObj): ?CommunicationPerformed
    {
        $record = $recordObj->getData();
        $referral = ReferralOrderService::qdmId($record['id'] ?? null);
        $received = $record['received'] ?? null;
        if (($record['document_uuid'] ?? null) === null) {
            // A Reply Date names a day. The reply came some time that day, and never before the referral
            // it answers, which CMS50 asks: the end of the day says both, so a reply the day of the
            // referral counts as after it.
            $day = ReferralOrderService::date($received);
            $received = $day === null ? null : $day . ' 23:59:59';
        } elseif (!is_string($received)) {
            $received = null;
        }
        $system = $record['type_system'] ?? null;
        $typeCode = $record['type_code'] ?? null;
        if ($referral === null || $received === null || !is_string($system) || !is_string($typeCode)) {
            return null;
        }
        $id = parent::convertToObjectIdBSONFormat($recordObj->getEntityCount());
        $model = new CommunicationPerformed([
            '_id' => $id,
            'id' => $id,
            'authorDatetime' => new DateTime(['date' => $received]),
            'receivedDatetime' => new DateTime(['date' => $received]),
            'relatedTo' => [$referral],
        ]);
        // The report's type as an OpenEMR code (LOINC:11488-4); a system OpenEMR has no code type for
        // leaves the report uncoded, so no value set matches it.
        $codeTypes = $this->codeTypesService;
        $code = $codeTypes instanceof CodeTypesService
            ? $this->makeQdmCode($codeTypes->getOpenEMRCodeForSystemAndCode($system, $typeCode))
            : null;
        if ($code !== null) {
            $model->addCode($code);
        }
        return $model;
    }
}
