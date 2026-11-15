<?php

/**
 * Isolated ProcessingResult::addValidationError() Test
 *
 * setValidationMessages() replaces the whole set, so a service that wants to
 * record one message for one field had no way to do it without knowing every
 * other message already present.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;

final class ProcessingResultValidationErrorTest extends TestCase
{
    public function testRecordsTheMessageUnderItsField(): void
    {
        $result = new ProcessingResult();
        $result->addValidationError('relationship', 'Relationship already exists');
        self::assertSame(['relationship' => ['Relationship already exists']], $result->getValidationMessages());
    }

    public function testTheResultIsNoLongerValid(): void
    {
        $result = new ProcessingResult();
        self::assertTrue($result->isValid());
        self::assertFalse($result->hasErrors());

        $result->addValidationError('id', 'Relationship not found');

        self::assertFalse($result->isValid());
        self::assertTrue($result->hasErrors());
        // A validation error is not an internal error.
        self::assertFalse($result->hasInternalErrors());
    }

    public function testKeepsEarlierMessagesForTheSameField(): void
    {
        $result = new ProcessingResult();
        $result->addValidationError('id', 'Relationship not found');
        $result->addValidationError('id', 'Relationship belongs to another contact');
        self::assertSame(
            ['id' => ['Relationship not found', 'Relationship belongs to another contact']],
            $result->getValidationMessages()
        );
    }

    public function testKeepsMessagesForOtherFields(): void
    {
        $result = new ProcessingResult();
        $result->addValidationError('relationship', 'Relationship already exists');
        $result->addValidationError('id', 'Relationship not found');
        self::assertSame(
            ['relationship' => ['Relationship already exists'], 'id' => ['Relationship not found']],
            $result->getValidationMessages()
        );
    }

    public function testAddsToMessagesAValidatorAlreadySet(): void
    {
        // The shape the validators produce: [field => [ruleKey => message]].
        $result = new ProcessingResult();
        $result->setValidationMessages(['uuid' => ['invalid or nonexisting value' => ' value abc']]);
        $result->addValidationError('relationship', 'Relationship already exists');

        $messages = $result->getValidationMessages();
        self::assertIsArray($messages);
        self::assertArrayHasKey('uuid', $messages);
        self::assertSame(['invalid or nonexisting value' => ' value abc'], $messages['uuid']);
        self::assertSame(['Relationship already exists'], $messages['relationship']);
    }

    public function testReplacesANonArrayValueForTheField(): void
    {
        // PractitionerService::setValidationMessages("Invalid Data") shows a
        // string reaching this structure, so the method must not append to one.
        $result = new ProcessingResult();
        $result->setValidationMessages(['id' => 'Invalid Data']);
        $result->addValidationError('id', 'Relationship not found');
        self::assertSame(['id' => ['Relationship not found']], $result->getValidationMessages());
    }
}
