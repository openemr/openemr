<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm;

use OpenEMR\Cqm\PhpCqmCalculation;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\MedicationOrder;
use OpenEMR\Cqm\Qdm\SubstanceOrder;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;
use OpenEMR\Services\Qrda\Util\DateHelper;

class CqmCalculator
{
    protected $measure;

    protected function findCodeByOid($valueSetArray, $oid_code)
    {
        $code = null;
        foreach ($valueSetArray as $component) {
            if ($component['oid'] == $oid_code) {
                $first_concept = $component['concepts'][0];
                $code = new Code([
                    "code" => $first_concept['code'],
                    "system" => $first_concept['code_system_oid']
                ]);
                break;
            }
        }

        return $code;
    }

    /**
     * @param  QdmRequestInterface $request
     * @param  Measure $measure
     * @param  $effectiveDate the measurement period start; the period is the year from it, as with the Node service
     * @return array<string, array<string, array<string, mixed>>> results by patient id and population set id
     * @throws MeasureCalculationException when the engine cannot calculate the measure
     * @throws \JsonException|\RuntimeException when the measure files or patients cannot be read
     */
    public function calculateMeasure($patients, Measure $measure, $effectiveDate)
    {
        $this->measure = $measure;
        $measureFiles = MeasureService::fetchMeasureFiles($measure->measure_path);
        $valueSetsJson = file_get_contents($measureFiles['valueSets']);
        if ($valueSetsJson === false) {
            throw new \RuntimeException('Unable to read the measure value sets');
        }
        $valueSetArray = json_decode($valueSetsJson, true);
        // Fix somethings that the cqm calculator needs before we create JSON out of the patient models.
        foreach ($patients as $patient) {
            $patient->birthDatetime = DateHelper::format_datetime_cqm($patient->birthDatetime);
            $data_elements_to_add = [];
            foreach ($patient->dataElements as $dataElement) {
                // We need to look up OIDs and add the first code concept from the value set.
                // We do this first in case it's in a dataElement that we need to clone for the calculator below
                if (isset($dataElement->negationRationale) && $dataElement->negationRationale !== null) {
                    $to_add = [];
                    foreach ($dataElement->dataElementCodes as $dataElementCode) {
                        if (empty($dataElementCode->system)) {
                            // placeholder for "oid" codes that calculator likes
                            $dataElementCode->system = "1.2.3.4.5.6.7.8.9.10";
                            // Look up OID code in measure
                            $code = $this->findCodeByOid($valueSetArray, $dataElementCode->code);
                            if ($code !== null) {
                                $to_add[] = $code;
                            }
                        }
                    }
                    foreach ($to_add as $item) {
                        $dataElement->dataElementCodes [] = $item;
                    }
                }

                // The calculator seems to be confused whether it needs a substance order or medication order, so Cypress
                // sends both... so we do too.
                if ($dataElement instanceof SubstanceOrder) {
                    $medOrder = new MedicationOrder([
                        'authorDatetime' => $dataElement->authorDatetime,
                        'dataElementCodes' => $dataElement->dataElementCodes,
                        'negationRationale' => $dataElement->negationRationale,
                        'relevantPeriod' => $dataElement->relevantPeriod,
                        'frequency' => $dataElement->frequency
                    ]);
                    $data_elements_to_add[] = $medOrder;
                }
            }

            foreach ($data_elements_to_add as $data_elem) {
                $patient->dataElements[] = $data_elem;
            }
        }

        $patientsJson = json_encode($patients, JSON_THROW_ON_ERROR);
        // The measure as the JSON object the calculator reads
        $measureData = json_decode(json_encode($measure, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($measureData)) {
            throw new \UnexpectedValueException('The measure is not a JSON object');
        }
        $effectiveTime = strtotime((string) $effectiveDate);
        if ($effectiveTime === false) {
            throw new \UnexpectedValueException('Unreadable measurement period start');
        }
        try {
            return (new PhpCqmCalculation())->calculate($patientsJson, $measureData, $valueSetsJson, date('YmdHi', $effectiveTime) . '00');
        } catch (\RuntimeException | \LogicException $e) {
            // e.g. CQL the engine cannot run, as cqm-execution cannot: "no function with matching signature"
            throw new MeasureCalculationException($measure->cms_id !== '' ? $measure->cms_id : $measure->hqmf_id, $e);
        }
    }

    public function getMeasure()
    {
        return $this->measure;
    }
}
