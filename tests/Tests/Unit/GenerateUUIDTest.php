<?php
/**
 * GenerateUUIDTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit;

use PHPUnit\Framework\TestCase;

use OpenEMR\Common\Uuid\UuidRegistry;

class GenerateUUIDTest extends TestCase
{
    const SEED_COUNT = 0;

    public function generate_facility_test($prefix, $number)
    {
        // nothing to do here.
        if ($number < 1)
        {
            return;
        }

        $sqlColumns = [];
        $sql = "INSERT INTO facility_test(`country_code`, `tax_id_type`,`color`,`oid`, `name`) VALUES ";
        $bindValues = [];
        for ($i = 0; $i < $number; $i++) {
            $bindValues[] = "USA";
            $bindValues[] = "";
            $bindValues[] = "";
            $bindValues[] = "";
            $bindValues[] = "Company $prefix $i";
            $sqlColumns[] = "(?,?,?,?,?)";
        }
        $sql .= implode(",", $sqlColumns);
        sqlStatement($sql, $bindValues);
    }

    public function testGenerateUUID()
    {
        $seedCount = [0, 1, 100, 500, 1000];
        $origResults = [];
        $newResults = [];
        foreach ($seedCount as $seed)
        {
            $registry = (new UuidRegistry(['table_name' => 'facility_test']));
            $newResults[] = $this->runTestWithFunction($registry, 'createMissingUuidsImproved', $seed);

            $registry = (new UuidRegistry(['table_name' => 'facility_test']));
            $origResults[] = $this->runTestWithFunction($registry, 'createMissingUuids', $seed);
        }

        echo "\n\nRun Results\n";
        for ($i = 0; $i < count($seedCount); $i++)
        {
            echo str_pad("Orig", 15, " ")
                . str_pad("New", 15, " ")
                . str_pad("Records", 15, " ")
                . "\n";
            echo str_pad($origResults[$i]['run'], 15, " ")
                . str_pad($newResults[$i]['run'], 15, " ")
                . str_pad($seedCount[$i], 15, " ")
                . "\n";
//            echo "!Vert\t\t" . $origResults[$i]['run'] . "\t\t" . $newResults[$i]['run'] . "\t\t" . $seedCount[$i] . "\n";
        }
        $this->assertTrue(true, "test passed");
    }

    private function runTestWithFunction(UuidRegistry $registry, $function, $seedCount)
    {
        $results = [];

        $time1 = hrtime(true);
        sqlStatement("TRUNCATE TABLE `facility_test`");
        $this->generate_facility_test("batch 1", $seedCount);
        $results['seed'] = ((hrtime(true) - $time1) / 1e+6);

        // now try to populate the ids
        $time1 = hrtime(true);
        $registry->$function();
        $results['run'] = ((hrtime(true) - $time1) / 1e+6);
        return $results;
    }

}