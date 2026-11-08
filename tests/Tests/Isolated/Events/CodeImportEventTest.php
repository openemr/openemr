<?php

/**
 * Isolated tests for the CodeImportEvent DTO
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2026 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Events;

use InvalidArgumentException;
use OpenEMR\Events\Codes\CodeImportEvent;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class CodeImportEventTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $event = new CodeImportEvent('LOINC', '/tmp/loinc.csv', true);

        $this->assertSame('LOINC', $event->getCodeType());
        $this->assertSame('/tmp/loinc.csv', $event->getFilePath());
        $this->assertTrue($event->isReplace());
        $this->assertFalse($event->isHandled());
        $this->assertSame([], $event->getMessages());
    }

    public function testSetHandledStopsPropagation(): void
    {
        $event = new CodeImportEvent('LOINC', '/tmp/loinc.csv', false);
        $this->assertFalse($event->isPropagationStopped());

        $event->setHandled(true);

        $this->assertTrue($event->isHandled());
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testSetHandledFalseDoesNotStopPropagation(): void
    {
        $event = new CodeImportEvent('LOINC', '/tmp/loinc.csv', false);

        $event->setHandled(false);

        $this->assertFalse($event->isHandled());
        $this->assertFalse($event->isPropagationStopped());
    }

    public function testMessagesAreGroupedByTypeAndKeepInsertionOrder(): void
    {
        $event = new CodeImportEvent('LOINC', '/tmp/loinc.csv', false);
        $event->addMessage(CodeImportEvent::MESSAGE_TYPE_SUCCESS, 'first');
        $event->addMessage(CodeImportEvent::MESSAGE_TYPE_ERROR, 'oops');
        $event->addMessage(CodeImportEvent::MESSAGE_TYPE_SUCCESS, 'second');

        $this->assertSame(
            [
                CodeImportEvent::MESSAGE_TYPE_SUCCESS => ['first', 'second'],
                CodeImportEvent::MESSAGE_TYPE_ERROR => ['oops'],
            ],
            $event->getMessages()
        );
        $this->assertSame(['first', 'second'], $event->getMessages(CodeImportEvent::MESSAGE_TYPE_SUCCESS));
        $this->assertSame(['oops'], $event->getMessages(CodeImportEvent::MESSAGE_TYPE_ERROR));
    }

    public function testGetMessagesForUnusedTypeReturnsEmptyList(): void
    {
        $event = new CodeImportEvent('LOINC', '/tmp/loinc.csv', false);

        $this->assertSame([], $event->getMessages(CodeImportEvent::MESSAGE_TYPE_ERROR));
    }

    public function testAddMessageRejectsUnknownType(): void
    {
        $event = new CodeImportEvent('LOINC', '/tmp/loinc.csv', false);

        $this->expectException(InvalidArgumentException::class);
        $event->addMessage('warning', 'not a supported type');
    }

    public function testCoreFallbackPriorityIsBelowTheDefaultListenerPriority(): void
    {
        // Modules register at the default priority of 0; core must sort after them so a module can
        // claim a code type core also supports.
        $this->assertLessThan(0, CodeImportEvent::PRIORITY_CORE_FALLBACK);
    }
}
