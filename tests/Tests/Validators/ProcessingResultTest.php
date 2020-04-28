<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Validators\ProcessingResult;

/**
 * Processing Result Tests
 *
 * @coversDefaultClass OpenEMR\Services\ServiceResult
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixon.whitmire@ibm.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixon.whitmire@ibm.com>
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

    /**
     * @cover ::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals(0, count($this->processingResult->getValidationMessages()));
        $this->assertEquals(0, count($this->processingResult->getProcessingErrors()));
        $this->assertTrue($this->processingResult->isValid());
        $this->assertFalse($this->processingResult->hasErrors());
        $this->assertNull($this->processingResult->getData());
    }

    /**
     * @#cover ::setValidationMessages
     * @#cover ::getValidationMessages
     * @#cover ::setProcessingErrors
     * @#cover ::getProcessingErrors
     * @#cover ::addProcessingError
     */
    public function testGetSetOperations()
    {
        $this->processingResult->setValidationMessages(array("foo" => "bar"));
        $this->processingResult->setProcessingErrors(array("bar", "baz"));
        $this->processingResult->addProcessingError("boz");

        $this->assertEquals(array("foo" => "bar"), $this->processingResult->getValidationMessages());
        $this->assertEquals(array("bar", "baz", "boz"), $this->processingResult->getProcessingErrors());
    }

    /**
     * @cover ::isValid
     */
    public function testIsValid()
    {
        $this->assertTrue($this->processingResult->isValid());

        $this->processingResult->setValidationMessages(array("foo" => "bar"));
        $this->assertFalse($this->processingResult->isValid());
    }

    /**
     * @cover ::hasError
     */
    public function testHasError()
    {
        // no validation or processing errors
        $this->assertFalse($this->processingResult->hasErrors());

        // single validation error
        $this->processingResult->setValidationMessages(array("foo" => "bar"));
        $this->assertTrue($this->processingResult->hasErrors());

        // single processing error
        $this->processingResult->setValidationMessages(array());
        $this->processingResult->setProcessingErrors(array("foo" => "bar"));
        $this->assertTrue($this->processingResult->hasErrors());

        // validation and processing errors
        $this->processingResult->setValidationMessages(array("foo" => "bar"));
        $this->assertTrue($this->processingResult->hasErrors());
    }
}
