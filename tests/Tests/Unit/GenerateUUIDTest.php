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

    private function createTable()
    {
        $this->dropTable();
        $sql = "CREATE TABLE `facility_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `fax` varchar(30) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `postal_code` varchar(11) DEFAULT NULL,
  `country_code` varchar(30) NOT NULL DEFAULT '',
  `federal_ein` varchar(15) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `service_location` tinyint(1) NOT NULL DEFAULT 1,
  `billing_location` tinyint(1) NOT NULL DEFAULT 1,
  `accepts_assignment` tinyint(1) NOT NULL DEFAULT 1,
  `pos_code` tinyint(4) DEFAULT NULL,
  `x12_sender_id` varchar(25) DEFAULT NULL,
  `attn` varchar(65) DEFAULT NULL,
  `domain_identifier` varchar(60) DEFAULT NULL,
  `facility_npi` varchar(15) DEFAULT NULL,
  `facility_taxonomy` varchar(15) DEFAULT NULL,
  `tax_id_type` varchar(31) NOT NULL DEFAULT '',
  `color` varchar(7) NOT NULL DEFAULT '',
  `primary_business_entity` int(10) NOT NULL DEFAULT 1 COMMENT '0-Not Set as business entity 1-Set as business entity',
  `facility_code` varchar(31) DEFAULT NULL,
  `extra_validation` tinyint(1) NOT NULL DEFAULT 1,
  `mail_street` varchar(30) DEFAULT NULL,
  `mail_street2` varchar(30) DEFAULT NULL,
  `mail_city` varchar(50) DEFAULT NULL,
  `mail_state` varchar(3) DEFAULT NULL,
  `mail_zip` varchar(10) DEFAULT NULL,
  `oid` varchar(255) NOT NULL DEFAULT '' COMMENT 'HIEs CCDA and FHIR an OID is required/wanted',
  `iban` varchar(50) DEFAULT NULL,
  `info` text DEFAULT NULL,
  `weno_id` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8";
        sqlStatementNoLog($sql);
    }

    private function dropTable()
    {
        sqlStatementNoLog("DROP TABLE IF EXISTS `facility_test`");
    }

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
        $this->createTable();
        $seedCount = [0, 1, 2, 3, 4, 5, 10, 50, 100, 200];
//        $seedCount = [0, 1, 2,3,4,5,6,7,8,9,10];//, 20, 50, 100];
        $results = ['new' => [], 'orig' => []];
        $iterations = 5;
        foreach ($seedCount as $seed)
        {
            $iteration = ['new' => [], 'orig' => []];
            for ($runs = 0; $runs < $iterations; $runs++) {

                $registry = (new UuidRegistry(['table_name' => 'facility_test']));
                $iteration['new'][] = $this->runTestWithFunction($registry, 'createMissingUuidsImproved', $seed);

                $registry = (new UuidRegistry(['table_name' => 'facility_test']));
                $iteration['orig'][] = $this->runTestWithFunction($registry, 'createMissingUuids', $seed);
            }
            $sum = array_reduce($iteration['new'], function($carry, $val) {
                return $carry + $val['run'];
            }, 0);
            $results['new'][] = round($sum / $iterations, 4);
            $sum = array_reduce($iteration['orig'], function($carry, $val) {
                return $carry + $val['run'];
            }, 0);
            $results['orig'][] = round($sum / $iterations, 4);
        }

        $this->dropTable();

        echo "\n\nRun Results\n";
        for ($i = 0; $i < count($seedCount); $i++)
        {
            echo str_pad("Orig", 15, " ")
                . str_pad("New", 15, " ")
                . str_pad("Records", 15, " ")
                . str_pad("Iterations", 15, " ")
                . "\n";
            echo str_pad($results['orig'][$i], 15, " ")
                . str_pad($results['new'][$i], 15, " ")
                . str_pad($seedCount[$i], 15, " ")
                . str_pad($iterations, 15, " ")
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