<?php

namespace OpenEMR\Tests\Validators;

use PHPUnit\Framework\TestCase;
use OpenEMR\Validators\ProcessingResult;

/**
 * Processing Result Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class ProcessingResultTest extends TestCase
{
    private $processingResult;

    protected function setUp(): void
    {
        $this->processingResult = new ProcessingResult();
    }

    public function testConstruct(): void
    {
        $this->assertEquals(0, count($this->processingResult->getValidationMessages()));
        $this->assertEquals(0, count($this->processingResult->getInternalErrors()));
        $this->assertEquals(0, count($this->processingResult->getData()));

        $this->assertTrue($this->processingResult->isValid());
        $this->assertFalse($this->processingResult->hasInternalErrors());
        $this->assertFalse($this->processingResult->hasErrors());
    }

    public function testGetSetOperations(): void
    {
        $this->assertEquals(0, count($this->processingResult->getValidationMessages()));
        $this->processingResult->setValidationMessages(["foo" => "bar"]);
        $this->assertEquals(1, count($this->processingResult->getValidationMessages()));

        $this->assertEquals(0, count($this->processingResult->getInternalErrors()));
        $this->processingResult->addInternalError("internal error occurred");
        $this->assertEquals(1, count($this->processingResult->getInternalErrors()));

        $this->assertEquals(0, count($this->processingResult->getData()));
        $this->processingResult->addData(["fname" => "John", "lname" => "Doe"]);
        $this->assertEquals(1, count($this->processingResult->getData()));
    }

    public function testIsValid(): void
    {
        $this->assertTrue($this->processingResult->isValid());

        $this->processingResult->setValidationMessages(["foo" => "bar"]);
        $this->assertFalse($this->processingResult->isValid());
    }

    public function testHasErrors(): void
    {
        // no validation or processing errors
        $this->assertFalse($this->processingResult->hasErrors());

        // single validation error
        $this->processingResult->setValidationMessages(["foo" => "bar"]);
        $this->assertTrue($this->processingResult->hasErrors());

        // single processing error
        $this->processingResult->setValidationMessages([]);
        $this->processingResult->addInternalError("internal error");
        $this->assertTrue($this->processingResult->hasErrors());

        // validation and processing errors
        $this->processingResult->setValidationMessages(["foo" => "bar"]);
        $this->assertTrue($this->processingResult->hasErrors());
    }

    public function testHasInternalErrors(): void
    {
        $this->assertFalse($this->processingResult->hasInternalErrors());

        $this->processingResult->addInternalError("error");
        $this->assertTrue($this->processingResult->hasInternalErrors());
        $this->assertEquals(1, count($this->processingResult->getInternalErrors()));
    }
}
