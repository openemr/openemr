<?php

/**
 * Isolated ProcessingResult Test
 *
 * Tests ProcessingResult data container functionality without dependencies.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;

class ProcessingResultTest extends TestCase
{
    public function testEmptyProcessingResultIsValid(): void
    {
        $result = new ProcessingResult();

        $this->assertTrue($result->isValid(), 'Empty ProcessingResult should be valid');
        $this->assertEmpty($result->getValidationMessages(), 'Should have no validation messages');
        $this->assertEmpty($result->getInternalErrors(), 'Should have no internal errors');
        $this->assertEmpty($result->getData(), 'Should have no data');
    }

    public function testSetAndGetValidationMessages(): void
    {
        $result = new ProcessingResult();
        $messages = ['Field is required', 'Invalid format'];

        $result->setValidationMessages($messages);

        $this->assertEquals($messages, $result->getValidationMessages());
        $this->assertFalse($result->isValid(), 'Should be invalid with validation messages');
    }

    public function testSetMultipleValidationMessages(): void
    {
        $result = new ProcessingResult();
        $messages = ['First error', 'Second error'];

        $result->setValidationMessages($messages);

        $retrievedMessages = $result->getValidationMessages();
        $this->assertCount(2, $retrievedMessages);
        $this->assertContains('First error', $retrievedMessages);
        $this->assertContains('Second error', $retrievedMessages);
        $this->assertFalse($result->isValid());
    }

    public function testSetAndGetInternalErrors(): void
    {
        $result = new ProcessingResult();
        $errors = ['Database connection failed', 'Service unavailable'];

        $result->setInternalErrors($errors);

        $this->assertEquals($errors, $result->getInternalErrors());
        $this->assertTrue($result->isValid(), 'isValid() only checks validation messages, not internal errors');
    }

    public function testAddInternalError(): void
    {
        $result = new ProcessingResult();

        $result->addInternalError('First internal error');
        $result->addInternalError('Second internal error');

        $errors = $result->getInternalErrors();
        $this->assertCount(2, $errors);
        $this->assertContains('First internal error', $errors);
        $this->assertContains('Second internal error', $errors);
        $this->assertTrue($result->isValid(), 'isValid() only checks validation messages');
    }

    public function testSetAndGetData(): void
    {
        $result = new ProcessingResult();
        $data = ['id' => 123, 'name' => 'Test Item'];

        $result->setData($data);

        $this->assertEquals($data, $result->getData());
    }

    public function testAddData(): void
    {
        $result = new ProcessingResult();

        $result->addData(['id' => 1, 'name' => 'First']);
        $result->addData(['id' => 2, 'name' => 'Second']);

        $data = $result->getData();
        $this->assertCount(2, $data);
        $this->assertEquals(['id' => 1, 'name' => 'First'], $data[0]);
        $this->assertEquals(['id' => 2, 'name' => 'Second'], $data[1]);
    }

    public function testHasData(): void
    {
        $result = new ProcessingResult();

        $this->assertFalse($result->hasData(), 'Empty result should not have data');

        $result->addData(['test' => 'value']);

        $this->assertTrue($result->hasData(), 'Result with data should return true');
    }

    public function testValidityWithMixedErrorTypes(): void
    {
        $result = new ProcessingResult();

        // Valid with only data
        $result->setData(['success' => true]);
        $this->assertTrue($result->isValid());

        // Invalid with validation messages
        $result->setValidationMessages(['Validation error']);
        $this->assertFalse($result->isValid());

        // Still invalid with internal errors added (but isValid only checks validation messages)
        $result->addInternalError('Internal error');
        $this->assertFalse($result->isValid());
    }

    public function testClearValidationMessages(): void
    {
        $result = new ProcessingResult();
        $result->setValidationMessages(['Error message']);

        $this->assertFalse($result->isValid());

        $result->setValidationMessages([]);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getValidationMessages());
    }

    public function testClearInternalErrors(): void
    {
        $result = new ProcessingResult();
        $result->addInternalError('Internal error');

        $this->assertTrue($result->isValid(), 'Internal errors do not affect isValid()');

        $result->setInternalErrors([]);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getInternalErrors());
    }

    public function testDataArrayStructure(): void
    {
        $result = new ProcessingResult();

        // Test that data maintains array structure
        $item1 = ['id' => 1, 'value' => 'first'];
        $item2 = ['id' => 2, 'value' => 'second'];

        $result->addData($item1);
        $result->addData($item2);

        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        // Verify data integrity
        $this->assertEquals(1, $data[0]['id']);
        $this->assertEquals('first', $data[0]['value']);
        $this->assertEquals(2, $data[1]['id']);
        $this->assertEquals('second', $data[1]['value']);
    }

    public function testGetFirstDataResult(): void
    {
        $result = new ProcessingResult();

        // Test with no data
        $this->assertNull($result->getFirstDataResult());

        // Test with single item
        $item1 = ['id' => 1, 'name' => 'First Item'];
        $result->addData($item1);
        $this->assertEquals($item1, $result->getFirstDataResult());

        // Test with multiple items - should return first
        $item2 = ['id' => 2, 'name' => 'Second Item'];
        $result->addData($item2);
        $this->assertEquals($item1, $result->getFirstDataResult());
    }

    public function testPaginationHandling(): void
    {
        $result = new ProcessingResult();

        // Test initial pagination
        $pagination = $result->getPagination();
        $this->assertInstanceOf(\OpenEMR\Common\Database\QueryPagination::class, $pagination);

        // Test setting pagination
        $customPagination = new \OpenEMR\Common\Database\QueryPagination();
        $customPagination->setLimit(5);
        $result->setPagination($customPagination);

        $retrievedPagination = $result->getPagination();
        $this->assertSame($customPagination, $retrievedPagination);
        $this->assertEquals(5, $retrievedPagination->getLimit());
    }

    public function testClearData(): void
    {
        $result = new ProcessingResult();

        // Add some data
        $result->addData(['id' => 1, 'value' => 'test']);
        $result->addData(['id' => 2, 'value' => 'test2']);
        $this->assertTrue($result->hasData());

        // Clear data
        $result->clearData();
        $this->assertFalse($result->hasData());
        $this->assertEmpty($result->getData());
    }

    public function testAddProcessingResult(): void
    {
        $result1 = new ProcessingResult();
        $result1->setValidationMessages(['Error 1']);
        $result1->addInternalError('Internal Error 1');
        $result1->addData(['id' => 1, 'value' => 'first']);

        $result2 = new ProcessingResult();
        $result2->setValidationMessages(['Error 2']);
        $result2->addInternalError('Internal Error 2');
        $result2->addData(['id' => 2, 'value' => 'second']);

        // Combine results
        $result1->addProcessingResult($result2);

        // Check combined validation messages
        $validationMessages = $result1->getValidationMessages();
        $this->assertCount(2, $validationMessages);
        $this->assertContains('Error 1', $validationMessages);
        $this->assertContains('Error 2', $validationMessages);

        // Check combined internal errors
        $internalErrors = $result1->getInternalErrors();
        $this->assertCount(2, $internalErrors);
        $this->assertContains('Internal Error 1', $internalErrors);
        $this->assertContains('Internal Error 2', $internalErrors);

        // Check combined data
        $data = $result1->getData();
        $this->assertCount(2, $data);
        $this->assertEquals(['id' => 1, 'value' => 'first'], $data[0]);
        $this->assertEquals(['id' => 2, 'value' => 'second'], $data[1]);
    }

    public function testHasInternalErrors(): void
    {
        $result = new ProcessingResult();

        $this->assertFalse($result->hasInternalErrors());

        $result->addInternalError('Test error');
        $this->assertTrue($result->hasInternalErrors());

        $result->setInternalErrors([]);
        $this->assertFalse($result->hasInternalErrors());
    }

    public function testHasErrors(): void
    {
        $result = new ProcessingResult();

        // No errors initially
        $this->assertFalse($result->hasErrors());

        // With validation messages
        $result->setValidationMessages(['Validation error']);
        $this->assertTrue($result->hasErrors());

        // Clear validation messages, add internal error
        $result->setValidationMessages([]);
        $result->addInternalError('Internal error');
        $this->assertTrue($result->hasErrors());

        // With both types of errors
        $result->setValidationMessages(['Validation error']);
        $this->assertTrue($result->hasErrors());
    }

    public function testExtractDataArray(): void
    {
        // Test with empty result
        $emptyResult = new ProcessingResult();
        $this->assertNull(ProcessingResult::extractDataArray($emptyResult));

        // Test with data
        $result = new ProcessingResult();
        $testData = [['id' => 1, 'name' => 'Test']];
        $result->setData($testData);

        $extractedData = ProcessingResult::extractDataArray($result);
        $this->assertEquals($testData, $extractedData);
    }

    public function testPaginationLimiting(): void
    {
        $result = new ProcessingResult();

        // Set a pagination limit
        $pagination = $result->getPagination();
        $pagination->setLimit(2);

        // Add more data than the limit
        $result->addData(['id' => 1]);
        $result->addData(['id' => 2]);
        $result->addData(['id' => 3]); // This should not be added due to limit

        $data = $result->getData();
        $this->assertCount(2, $data);
        $this->assertTrue($pagination->hasMoreData());
    }

    public function testSetDataWithPaginationLimit(): void
    {
        $result = new ProcessingResult();

        // Set pagination limit
        $pagination = $result->getPagination();
        $pagination->setLimit(3);

        // Set data that exceeds limit
        $largeDataSet = [
            ['id' => 1], ['id' => 2], ['id' => 3], ['id' => 4], ['id' => 5]
        ];

        $result->setData($largeDataSet);

        // Should be trimmed to limit
        $data = $result->getData();
        $this->assertCount(3, $data);
        $this->assertTrue($pagination->hasMoreData());

        // Test with empty data
        $result->setData([]);
        $this->assertEquals(0, $pagination->getTotalCount());
    }
}
