<?php

/**
 * @package OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Tests\ECQM;

use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\Measure;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRequestOne;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class MeasureResultsTest extends TestCase
{
    /** @var array<string, string> measure name => measure directory */
    protected array $measureOptions = [];
    protected $measure_result_map = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->measureOptions = MeasureService::fetchMeasureOptions();

        if (($handle = fopen(__DIR__ . "/measure_result_map.csv", "r")) !== false) {
            $head = fgetcsv($handle, 1000);

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // Make sure we have a pubpid, could be a blank line
                if (
                    count($data) == count($head) &&
                    isset($data[1]) &&
                    strlen($data[1]) > 1
                ) {
                    $column = array_combine($head, $data);
                    $this->measure_result_map [] = $column;
                }
            }

            fclose($handle);
        } else {
            throw new \Exception("Could not open measure result file measure_result_map.csv\n");
        }
    }

    public function testAllPatients(): void
    {
        $failures = [];
        foreach ($this->measure_result_map as $measureResult) {
            $measure = $measureResult['measure'];
            if (!is_string($measure)) {
                continue;
            }

            if ($measureResult['skip'] == 1) {
                echo "SKIPPING QRDA=`{$measureResult['qrda_file']}` PUBPID=`{$measureResult['pubpid']}` MEASURE=`$measure`\n";
                continue;
            }

            // Find the PIDs of the patient in the file
            $result = sqlStatement(
                "SELECT pid, pubpid, fname, lname, DOB FROM patient_data WHERE pubpid = ? ORDER BY id DESC LIMIT 1",
                [$measureResult['pubpid']]
            );

            // Try to find a patient that matches the pubpid. The pubpid is the id imported from the <id/> tag in the
            // QRDA XML document  and is how we identify patients in the CSV file
            $patient = null;
            while ($row = sqlFetchArray($result)) {
                $patient = $row;
                break;
            }

            // If we didn't find a patient, print an error and move on, don't kill test
            if ($patient === null) {
                echo "Patient with pubpid = `{$measureResult['pubpid']}` Not found. You may need to import the XML file `{$measureResult['qrda_file']}`.\n";
                continue;
            }

            $pid = $patient['pid'];

            $measurePath = $this->measureOptions[$measure] ?? null;
            if ($measurePath === null) {
                echo "Measure `$measure` is not in the installed measure bundle for the eCQM performance period.\n";
                continue;
            }

            // Calculate as QRDA reporting does: the patient from the database, the Measure model
            // and CqmCalculator. The measurement period runs a year from effectiveDate, as in
            // reporting, so effectiveEndDate is not passed.
            $request = new QdmRequestOne($pid);
            $builder = new QdmBuilder();
            $models = $builder->build($request);
            $measureModel = new Measure(MeasureService::fetchMeasureJson($measurePath));
            $measureModel->measure_path = $measurePath;
            $response = (new CqmCalculator())->calculateMeasure($models, $measureModel, $measureResult['effectiveDate']);

            // Check response result against our measure map
            foreach ($response as $populationSets) {
                foreach ($populationSets as $setName => $populationSet) {
                    $parts = explode('_', (string) $setName);
                    $setNumber = $parts[1];
                    // Only check results if the population set is correct
                    if ($measureResult['pop_set'] == $setNumber) {
                        $populationSet['DENEXCEP'] ??= 0;
                        $populationSet['DENEX'] ??= 0;
                        $populationSet['NUMEX'] ??= 0;

                        try {
                            $this->assertEquals($measureResult['IPP'], $populationSet['IPP'], "IPP Failed: QRDA=`{$measureResult['qrda_file']}` PUBPID=`{$measureResult['pubpid']}` PID=`$pid` MEASURE=`$measure` - $setName");
                            $this->assertEquals($measureResult['NUMER'], $populationSet['NUMER'], "NUMER Failed: QRDA=`{$measureResult['qrda_file']}` PUBPID=`{$measureResult['pubpid']}` PID=`$pid` MEASURE=`$measure` - $setName");
                            $this->assertEquals($measureResult['DENOM'], $populationSet['DENOM'], "DENOM Failed: QRDA=`{$measureResult['qrda_file']}` PUBPID=`{$measureResult['pubpid']}` PID=`$pid` MEASURE=`$measure` - $setName");
                            $this->assertEquals($measureResult['NUMEX'], $populationSet['NUMEX'], "NUMEX Failed: QRDA=`{$measureResult['qrda_file']}` PUBPID=`{$measureResult['pubpid']}` PID=`$pid` MEASURE=`$measure` - $setName");
                            $this->assertEquals($measureResult['DENEX'], $populationSet['DENEX'], "DENEX Failed: QRDA=`{$measureResult['qrda_file']}` PUBPID=`{$measureResult['pubpid']}` PID=`$pid` MEASURE=`$measure` - $setName");
                            $this->assertEquals($measureResult['DENEXCEP'], $populationSet['DENEXCEP'], "DENEXCEP Failed: QRDA=`{$measureResult['qrda_file']}` PUBPID=`{$measureResult['pubpid']}` PID=`$pid` MEASURE=`$measure` - $setName");
                        } catch (ExpectationFailedException $e) {
                            $failures[] = $e->getMessage();
                        }
                    }
                }
            }
        }

        if (count($failures)) {
            $count = 1;
            foreach ($failures as $failure) {
                echo "$count.) $failure\n";
                $count++;
            }
            throw new ExpectationFailedException(count($failures) . " failures");
        }
    }
}
