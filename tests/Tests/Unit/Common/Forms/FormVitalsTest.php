<?php

/**
 * FormVitalsTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * FormVitalsTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Forms;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Forms\FormVitalDetails;
use OpenEMR\Common\Forms\FormVitals;
use OpenEMR\Services\ListService;
use PHPUnit\Framework\TestCase;

class FormVitalsTest extends TestCase
{
    const NOTE_UNIT_TEST = "OEUnitTest";
    const VITAL_DETAILS_COLUMN = "bps";

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM " . FormVitalDetails::TABLE_NAME
            . " WHERE form_id IN (select id FROM " . FormVitals::TABLE_NAME . " WHERE note=?)", [self::NOTE_UNIT_TEST]);
        QueryUtils::sqlStatementThrowException("DELETE FROM " . FormVitals::TABLE_NAME . " WHERE note=?", [self::NOTE_UNIT_TEST]);
    }

    public function test__construct()
    {
        // make sure we can construct the object and there are no errors
        $formVitals = new FormVitals();
        $this->assertNotEmpty($formVitals->get_date(), "Date should be populated");
        $this->assertIsArray($formVitals->get_vital_details(), "Vital details should be array");
    }

    public function test_set_details_for_column()
    {
        $details = new FormVitalDetails();
        $formVitals = new FormVitals();

        // verify column get / set are being done correctly.
        $formVitals->set_details_for_column(self::VITAL_DETAILS_COLUMN, $details);
        $this->assertEquals($details, $formVitals->get_details_for_column(self::VITAL_DETAILS_COLUMN));
    }

    public function test_persist()
    {
        $formVitals = new FormVitals();
        $formVitals->note = self::NOTE_UNIT_TEST;
        $formVitals->persist();

        $this->assertNotNull($formVitals->get_id(), "Form vitals id should be populated");

        $records = $this->getVitalFormRecords($formVitals->get_id());
        $this->assertNotEmpty($records, "Vital records should have been found");
    }

    public function test_persist_vitals_with_details()
    {
        $formVitals = new FormVitals();
        $formVitals->note = self::NOTE_UNIT_TEST;

        $details = new FormVitalDetails();
        $details->set_vitals_column(self::VITAL_DETAILS_COLUMN);
        $formVitals->set_details_for_column($details->get_vitals_column(), $details);
        $formVitals->persist();

        $this->assertNotNull($formVitals->get_id(), "Form vitals id should be populated");
        $vitalRecords = $this->getVitalFormRecords($formVitals->get_id());
        $this->assertNotEmpty($vitalRecords, "Vital records should have been found");

        $this->assertNotNull($details->get_id(), "Form vital details id should be populated");
        $this->assertNotNull($details->get_form_id(), "Form vital details form id should be populated");

        $records = QueryUtils::fetchRecords("SELECT * FROM " . FormVitalDetails::TABLE_NAME . " WHERE id=?", [$details->get_id()]);
        $this->assertNotEmpty($records, "Vital detail records should have been found");

        $tableDetails = $records[0];
        $this->assertEquals($formVitals->get_id(), $tableDetails['form_id'], "Form id should match with vitals form id");
        $this->assertEquals(self::VITAL_DETAILS_COLUMN, $tableDetails['vitals_column'], "Vital detail column should have been persisted");
    }

    public function test_persist_vitals_with_details_interpretation()
    {
        $formVitals = new FormVitals();
        $formVitals->note = self::NOTE_UNIT_TEST;

        $listService = new ListService();
        $options = $listService->getOptionsByListName(FormVitals::LIST_OPTION_VITALS_INTERPRETATION);
        $this->assertNotEmpty($options, "Vital options should be populated");

        $details = new FormVitalDetails();
        $details->set_vitals_column(self::VITAL_DETAILS_COLUMN);
        $details->set_interpretation_option_id($options[0]['option_id']);
        $details->set_interpretation_list_id(FormVitals::LIST_OPTION_VITALS_INTERPRETATION);
        $details->set_interpretation_title($options[0]['title']);
        $details->set_interpretation_codes($options[0]['codes']);
        $formVitals->set_details_for_column($details->get_vitals_column(), $details);
        $formVitals->persist();

        $this->assertNotNull($formVitals->get_id(), "Form vitals id should be populated");
        $vitalRecords = $this->getVitalFormRecords($formVitals->get_id());
        $this->assertNotEmpty($vitalRecords, "Vital records should have been found");

        $this->assertNotNull($details->get_id(), "Form vital details id should be populated");
        $this->assertNotNull($details->get_form_id(), "Form vital details form id should be populated");

        $records = QueryUtils::fetchRecords("SELECT * FROM " . FormVitalDetails::TABLE_NAME . " WHERE id=?", [$details->get_id()]);
        $this->assertNotEmpty($records, "Vital detail records should have been found");

        $tableDetails = $records[0];
        $this->assertEquals($formVitals->get_id(), $tableDetails['form_id'], "Form id should match with vitals form id");
        $this->assertEquals($details->get_interpretation_list_id(), $tableDetails['interpretation_list_id'], "details object should match with form_vital_details column");
        $this->assertEquals($details->get_interpretation_option_id(), $tableDetails['interpretation_option_id'], "details object should match with form_vital_details column");
        $this->assertEquals($details->get_interpretation_title(), $tableDetails['interpretation_title'], "details object should match with form_vital_details column");
        $this->assertEquals($details->get_interpretation_codes(), $tableDetails['interpretation_codes'], "details object should match with form_vital_details column");

        $this->assertEquals(self::VITAL_DETAILS_COLUMN, $tableDetails['vitals_column'], "Vital detail column should have been persisted");
    }

    public function test_populate_array()
    {
        $vitalsArray = [
            'id' => 5
            ,"uuid" => 0x33333333333333
            ,"pulse" => 120
            ,'details' => [
                "pulse" => [
                    'id' => 7
                    ,'interpretation_codes' => 'A'
                    ,'interpretation_title' => 'Abnormal'
                    ,'vitals_column' => 'pulse'
                ]
            ]
        ];

        $vitals = new FormVitals();
        $vitals->populate_array($vitalsArray);
        foreach ($vitalsArray as $key => $value) {
            if ($key == 'details') {
                continue;
            }
            $function = "get_" . $key;
            $this->assertEquals($vitals->$function(), $value, FormVitals::class . " property '" . $key
                . "' should have been populated with array value " . $value);
        }

        $details = $vitals->get_details_for_column('pulse');
        $this->assertNotEmpty($details, "Details column for pulse should have been populated");

        foreach ($vitalsArray['details']['pulse'] as $key => $value) {
            $function = "get_" . $key;
            $this->assertEquals($details->$function(), $value, FormVitalDetails::class . " property '" . $key
                . "' should have been populated with array value " . $value);
        }
    }

    private function getVitalFormRecords($form_id)
    {
        return QueryUtils::fetchRecords("SELECT * FROM " . FormVitals::TABLE_NAME . " WHERE id=?", [$form_id]);
    }
}
