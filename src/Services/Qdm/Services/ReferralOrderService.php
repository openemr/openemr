<?php

/**
 * Referrals made on the Transactions screen (the LBTref form), as QDM Intervention, Order.
 *
 * CMS50 (Closing the Referral Loop) counts a patient's first referral of the year's first ten
 * months, by its author date, and looks for a consultant report whose relatedTo names it
 * (#14745). Each referral is a transactions row with its fields in lbt_data: the Referral Date is
 * the order's author date, and the Requested Service its codes, so only a referral with both is
 * read. The measure's Referral value set is SNOMED CT, so a Requested Service picked from the
 * SNOMED-CT code type can match it and a billing code can't.
 *
 * The order's QDM id comes from the referral itself (qdmId()), so ConsultantReportService can name
 * it in a report's relatedTo; the builder's running count would give the same referral another id
 * in every run.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\InterventionOrder;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\ReferralService;

class ReferralOrderService extends AbstractQdmService implements QdmServiceInterface
{
    /**
     * A Referral or Reply Date as the form stores it: a date, or a date and time where a site's
     * layout records one. The SQL keeps other text out; date() checks the day exists.
     */
    public const DATE_PATTERN = '^[0-9]{4}-[0-9]{2}-[0-9]{2}( [0-9]{2}:[0-9]{2}(:[0-9]{2})?)?$';

    public function getSqlStatement(): string
    {
        // lbt_data's key is (form_id, field_id), so each join finds one value or none. Wrapped so that
        // the patient filter executeQuery() appends applies to the referrals' pid.
        return "SELECT `pid`, `id`, `refer_date`, `refer_related_code` FROM (
                SELECT t.`pid`, t.`id`, TRIM(referral_date.`field_value`) AS `refer_date`,
                    TRIM(requested_service.`field_value`) AS `refer_related_code`
                FROM `transactions` AS t
                JOIN `lbt_data` AS referral_date ON referral_date.`form_id` = t.`id` AND referral_date.`field_id` = 'refer_date'
                JOIN `lbt_data` AS requested_service ON requested_service.`form_id` = t.`id` AND requested_service.`field_id` = 'refer_related_code'
                WHERE t.`title` = '" . ReferralService::FORM_ID . "'
            ) AS referral_orders
            WHERE `refer_date` REGEXP '" . self::DATE_PATTERN . "' AND `refer_related_code` <> ''";
    }

    /**
     * A referral as an Intervention, Order on its Referral Date, coded with each code of its Requested
     * Service. Null without a Referral Date or a transaction id.
     */
    public function makeQdmModel(QdmRecord $recordObj): ?InterventionOrder
    {
        $record = $recordObj->getData();
        $date = self::date($record['refer_date'] ?? null);
        $id = self::qdmId($record['id'] ?? null);
        if ($date === null || $id === null) {
            return null;
        }
        $model = new InterventionOrder([
            '_id' => parent::convertToObjectIdBSONFormat($recordObj->getEntityCount()),
            'id' => $id,
            'authorDatetime' => new DateTime(['date' => $date]),
        ]);
        foreach ($this->explodeAndMakeCodeArray($record['refer_related_code'] ?? null) as $code) {
            if ($code instanceof Code) {
                $model->addCode($code);
            }
        }
        return $model;
    }

    /**
     * A referral's QDM id, the one its order and every report related to it carry: "referral-" and
     * its transaction id, which no other data element's id can be. Not its uuid: a referral saved
     * while referral Tasks are off has none until FHIR first reads it, which can happen between
     * this service's query and ConsultantReportService's in the same calculation.
     */
    public static function qdmId(mixed $transactionId): ?string
    {
        $id = filter_var($transactionId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return is_int($id) ? 'referral-' . $id : null;
    }

    /**
     * The day a Referral or Reply Date names, as Y-m-d, or null when it names none: the day has to
     * exist, so 2026-02-29 isn't read as March 1. A time a layout records with it is left out, as the
     * referral's FHIR ServiceRequest leaves it out of authoredOn.
     */
    public static function date(mixed $value): ?string
    {
        if (!is_string($value) || preg_match('/' . self::DATE_PATTERN . '/', $value) !== 1) {
            return null;
        }
        foreach (['!Y-m-d', '!Y-m-d H:i', '!Y-m-d H:i:s'] as $format) {
            $parsed = \DateTimeImmutable::createFromFormat($format, $value);
            if ($parsed !== false && $parsed->format(substr($format, 1)) === $value) {
                return $parsed->format('Y-m-d');
            }
        }
        return null;
    }
}
