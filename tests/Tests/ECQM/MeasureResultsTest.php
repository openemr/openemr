<?php

/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Tests\ECQM;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7;
use OpenEMR\Cqm\CqmServiceManager;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRequestOne;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class MeasureResultsTest extends TestCase
{
    protected $client;
    protected $measureOptions = [];
    protected $measure_result_map = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->client = CqmServiceManager::makeCqmClient();
        $serviceHealth = $this->client->getHealth();
        if ($serviceHealth['uptime'] <= 0) {
            $this->client->start();
            sleep(2); // give cpu a rest
        }
        if ($serviceHealth['uptime'] <= 0) {
            $msg = xlt("Can not complete measure results test. Node Service is not running.");
            throw new \Exception($msg);
        }

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

    public function testAllPatients()
    {
        $failures = [];
        foreach ($this->measure_result_map as $measureResult) {
            $measure = $measureResult['measure'];

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

            $effectiveDate = $measureResult['effectiveDate'];
            $effectiveEndDate = $measureResult['effectiveEndDate'];

            // We're going to build a request for a single PID
            $request = new QdmRequestOne($pid);
            $builder = new QdmBuilder();
            $models = $builder->build($request);
            $json_models = json_encode($models);
            $patientStream = Psr7\Utils::streamFor($json_models);
            $measurePath = $this->measureOptions[$measure];
            $measureFiles = MeasureService::fetchMeasureFiles($measurePath);
            $measureFileStream = new LazyOpenStream($measureFiles['measure'], 'r');
            $valueSetFileStream = new LazyOpenStream($measureFiles['valueSets'], 'r');
            $options = [
                'doPretty' => true,
                'includeClauseResults' => true,
                'requestDocument' => true,
                'effectiveDate' => $effectiveDate,
                'effectiveDateEnd' => $effectiveEndDate
            ];
            $optionsStream = Psr7\Utils::streamFor(json_encode($options));

            $response = $this->client->calculate(
                $patientStream,
                $measureFileStream,
                $valueSetFileStream,
                $optionsStream
            );

            // Check response result against our measure map
            foreach ($response as $id => $populationSets) {
                foreach ($populationSets as $setName => $populationSet) {
                    $parts = explode('_', $setName);
                    $setNumber = $parts[1];
                    // Only check results if the population set is correct
                    if ($measureResult['pop_set'] == $setNumber) {
                        if (!isset($populationSet['DENEXCEP'])) {
                            $populationSet['DENEXCEP'] = 0;
                        }
                        if (!isset($populationSet['DENEX'])) {
                            $populationSet['DENEX'] = 0;
                        }
                        if (!isset($populationSet['NUMEX'])) {
                            $populationSet['NUMEX'] = 0;
                        }

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
