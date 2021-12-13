<?php

/**
 * ExportMemoryStreamWriterTest
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\FHIR\Export;

use OpenEMR\FHIR\Export\ExportMemoryStreamWriter;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ExportMemoryStreamWriterTest extends TestCase
{
    public function testAppend()
    {
        $patient = new FHIRPatient(['id' => Uuid::uuid4()]);
        $id = new FHIRId();
        $id->setValue(Uuid::uuid4());
        $patient->setId($id);

        $patient2 = new FHIRPatient();
        $id = new FHIRId();
        $id->setValue(Uuid::uuid4());
        $patient2->setId($id);

        $shutDownTime = new \DateTime();
        $shutDownTime->add(new \DateInterval("PT1H"));

        $exportWriter = new ExportMemoryStreamWriter($shutDownTime);
        $contents = "";
        try {
            $exportWriter->append($patient);
            $exportWriter->append($patient2);
            $contents = $exportWriter->getContents();
        } finally {
            $exportWriter->close();
        }

        $this->assertEquals(2, $exportWriter->getRecordsWritten());
        $this->assertNotEmpty($contents, "Writer should have written out records");

        $jsonRecords = explode("\n", $contents);
        $this->assertEquals(2, count($jsonRecords), "Records in JSON should match records appended");
        $patientCheck = json_decode($jsonRecords[0]);
        $this->assertEquals($patient->getId(), $patientCheck->id, "Exported patient should have same id");

        $patient2Check = json_decode($jsonRecords[1]);
        $this->assertEquals($patient2->getId(), $patient2Check->id, "Exported patient should have same id");
    }
}
