<?php

/**
 * ExportJobTest tests that the ExportJob class is functioning properly.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\FHIR\Export;

use OpenEMR\FHIR\Export\ExportJob;
use PHPUnit\Framework\TestCase;

class ExportJobTest extends TestCase
{
    public function testSetStatus()
    {
        $statii = ExportJob::ALLOWED_STATII;
        foreach ($statii as $status) {
            $job = new ExportJob();
            $job->setStatus($status);
            $this->assertEquals($status, $job->getStatus());
        }
    }

    public function testSetStatusInvalidThrowsException()
    {
        $job = new ExportJob();
        $this->expectException(\InvalidArgumentException::class);
        $job->setStatus("some random status");
    }

    public function testIsComplete()
    {
        $job = new ExportJob();
        $this->assertFalse($job->isComplete(), "new job should not be complete");

        $job->setStatus(ExportJob::STATUS_COMPLETED);
        $this->assertTrue($job->isComplete(), "completed status job should be complete");
    }

    public function testSetOutput()
    {
        $job = new ExportJob();
        $outputFormats = ExportJob::ALLOWED_OUTPUT_FORMATS;
        foreach ($outputFormats as $format) {
            $job->setOutputFormat($format);
            $this->assertEquals($format, $job->getOutputFormat());
        }
    }

    public function testSetOutputInvalidThrowsException()
    {
        $job = new ExportJob();
        $this->expectException(\InvalidArgumentException::class);
        $job->setOutputFormat("text\html");
    }
}
