<?php

/**
 * Vital signs entered in the Vitals form, as QDM Physical Exam, Performed.
 *
 * The eCQMs read blood pressure, BMI, BMI percentile, height and weight as
 * Physical Exam, Performed (CMS165, CMS69, CMS155 among them). PhysicalExamService
 * reads those from form_observation only, which the encounter's Vitals form never
 * writes, so vitals entered there were not counted. This service reads them from
 * form_vitals, one data element per value, with its LOINC code and UCUM unit.
 *
 * The QRDA import stores each imported physical exam twice: in form_observation and
 * as a Vitals form. A reading with a form_observation physical exam of the same code,
 * on the same day and with the same value, is left to PhysicalExamService, so it is
 * counted once. A different reading the same day is kept: CMS165 takes the lowest
 * blood pressure of the most recent day. Only an exam whose value is a plain number
 * counts: PhysicalExamService reads any other as no result, and CAST would read
 * "128abc" as 128. Values are compared at the precision form_vitals stores them, so
 * two readings that differ only past the hundredths are both kept, while the
 * import's two copies, written from the same source value, still match. Days are
 * compared, not timestamps, because
 * the import's revise-and-approve path writes the date in another form.
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
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Cqm\Qdm\PhysicalExamPerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\QdmRecord;

class VitalSignsService extends AbstractQdmService implements QdmServiceInterface
{
    /**
     * form_vitals column => [LOINC code, UCUM unit]. The Vitals form stores weight in
     * pounds and height in inches whatever the display setting (units_of_measurement).
     */
    public const VITALS = [
        'bps' => ['8480-6', 'mm[Hg]'],
        'bpd' => ['8462-4', 'mm[Hg]'],
        'BMI' => ['39156-5', 'kg/m2'],
        'ped_bmi' => ['59576-9', '%'],
        'height' => ['8302-2', '[in_i]'],
        'weight' => ['29463-7', '[lb_av]'],
    ];

    /**
     * The codes a QRDA-imported physical exam can carry for each vital: the import
     * writes body weight measured (3141-9) and body weight (29463-7) both as weight.
     */
    public const IMPORTED_AS = [
        '29463-7' => ['29463-7', '3141-9'],
    ];

    /**
     * Decimal places form_vitals keeps for each column (DECIMAL(12,6)); the others
     * (blood pressure as text, ped_bmi as DECIMAL(6,2)) are compared at two.
     */
    private const STORED_SCALE = [
        'BMI' => 6,
        'height' => 6,
        'weight' => 6,
    ];

    public function getSqlStatement(): string
    {
        $selects = [];
        foreach (self::VITALS as $column => [$loinc, $unit]) {
            // Blood pressure columns are text; read only plain positive numbers from them.
            $isNumber = in_array($column, ['bps', 'bpd'], true)
                ? "v.`$column` REGEXP '^[0-9]+([.][0-9]+)?$' AND v.`$column` > 0"
                : "v.`$column` > 0";
            $codes = implode(', ', array_map(static fn(string $c): string => "'LOINC:$c'", self::IMPORTED_AS[$loinc] ?? [$loinc]));
            $scale = self::STORED_SCALE[$column] ?? 2;
            $selects[] = "SELECT v.`pid`, v.`date`, 'LOINC:$loinc' AS `code`, v.`$column` AS `value`, '$unit' AS `unit`
                FROM `form_vitals` AS v
                JOIN `forms` AS f ON f.`form_id` = v.`id` AND f.`formdir` = 'vitals' AND f.`deleted` = 0
                WHERE $isNumber
                AND NOT EXISTS (
                    SELECT 1 FROM `form_observation` AS o
                    WHERE o.`pid` = v.`pid` AND o.`ob_type` = '" . AbstractObservationService::OB_TYPE_PHYSICAL_EXAM . "'
                    AND o.`code` IN ($codes) AND DATE(o.`date`) = DATE(v.`date`)
                    AND o.`ob_value` REGEXP '^[0-9]+([.][0-9]+)?$'
                    AND CAST(o.`ob_value` AS DECIMAL(12,$scale)) = CAST(v.`$column` AS DECIMAL(12,$scale))
                )";
        }
        // Wrapped so that the patient filter executeQuery() appends applies to every vital.
        return "SELECT `pid`, `date`, `code`, `value`, `unit` FROM (\n" . implode("\nUNION ALL\n", $selects) . "\n) AS vital_signs WHERE 1 = 1";
    }

    public function makeQdmModel(QdmRecord $recordObj): PhysicalExamPerformed
    {
        $record = $recordObj->getData();
        $id = parent::convertToObjectIdBSONFormat($recordObj->getEntityCount());
        $model = new PhysicalExamPerformed([
            '_id' => $id,
            'id' => $id,
            'relevantDatetime' => new DateTime(['date' => $record['date']]),
            'authorDatetime' => new DateTime(['date' => $record['date']]),
        ]);
        // A number is a quantity; anything else is no result, as in PhysicalExamService. The query
        // returns only numbers above zero, but a quantity with no value would stop the measure
        // from being calculated at all, so this doesn't rely on it.
        $value = $record['value'] ?? null;
        if (is_numeric($value)) {
            $model->result = new Quantity([
                'value' => (float)$value,
                'unit' => $record['unit'],
            ]);
        }
        $code = $this->makeQdmCode($record['code']);
        if ($code !== null) {
            $model->addCode($code);
        }
        return $model;
    }
}
