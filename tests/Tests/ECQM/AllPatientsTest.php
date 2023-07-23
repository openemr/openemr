<?php

/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Tests\ECQM;

use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRequestAll;
use PHPUnit\Framework\TestCase;

class AllPatientsTest extends TestCase
{
    /**
     * Not really a test.
     *
     * Builds JSON QDM models for all patients in the database.
     */
    public function testAllPatients()
    {
        $builder = new QdmBuilder();
        $models = [];
        try {
            $models = $builder->build(new QdmRequestAll());
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        foreach ($models as $model) {
            $filename = __DIR__ . '/output/' . date('Y-m-d_His') . '.json';
            file_put_contents($filename, json_encode($model));
        }
    }
}
