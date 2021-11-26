<?php

/**
 * FhirVitalsServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;
use PHPUnit\Framework\TestCase;

class FhirVitalsServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        $bindValues = ['test-vital-signs'];

        $sql = "DELETE FROM uuid_mapping WHERE target_uuid IN (select uuid FROM form_vitals WHERE external_id = ?)";
        QueryUtils::sqlStatementThrowException($sql, $bindValues);

        $sql = "DELETE FROM uuid_registry WHERE table_name='form_vitals' AND uuid IN (select uuid FROM form_vitals WHERE external_id = ?)";
        QueryUtils::sqlStatementThrowException($sql, $bindValues);

        $sql = "DELETE FROM form_vitals WHERE external_id = ?";
        QueryUtils::sqlStatementThrowException($sql, $bindValues);
    }

    public function testConstructor()
    {
        // insert something into the db for a form_service and make sure things are populated
        $sql = "INSERT INTO form_vitals(id,external_id) "
            . "VALUES(?,?)";
        $id = generate_id();
        $values = [$id, 'test-vital-signs'];
        QueryUtils::sqlStatementThrowException($sql, $values);

        $service = new FhirObservationVitalsService();

        // now let's make sure we have a bunch of uuid's generated
        $uuid = QueryUtils::fetchSingleValue("select `uuid` FROM form_vitals WHERE id=?", 'uuid', [$id]);
        $this->assertNotNull($uuid, "UUID was not populated in form_values");
    }
}
