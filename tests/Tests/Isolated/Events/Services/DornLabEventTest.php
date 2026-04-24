<?php

/**
 * Isolated tests for DornLabEvent DTO
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Events\Services;

use OpenEMR\Events\Services\DornLabEvent;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class DornLabEventTest extends TestCase
{
    public function testConstructorSetsFormidAndPpid(): void
    {
        $event = new DornLabEvent(42, 7);

        $this->assertSame(42, $event->getFormid());
        $this->assertSame(7, $event->getPpid());
    }

    public function testHl7ReferenceGetter(): void
    {
        $hl7 = 'MSH|^~\\&|...';
        $event = new DornLabEvent(1, 2, $hl7);

        $ref = &$event->getHl7();
        $this->assertSame('MSH|^~\\&|...', $ref);

        // Modify via reference and verify the change propagates
        $ref = 'MODIFIED';
        $this->assertSame('MODIFIED', $event->getHl7());
    }

    public function testReqStrReferenceGetter(): void
    {
        $reqStr = 'some request';
        $event = new DornLabEvent(1, 2, reqStr: $reqStr);

        $ref = &$event->getReqStr();
        $this->assertSame('some request', $ref);

        $ref = 'changed';
        $this->assertSame('changed', $event->getReqStr());
    }

    public function testHl7DefaultsToNull(): void
    {
        $event = new DornLabEvent(1, 2);
        $this->assertNull($event->getHl7());
    }

    public function testAddMessageAndGetMessagesAsString(): void
    {
        $event = new DornLabEvent(1, 2);
        $event->addMessage('Error A');
        $event->addMessage('Error B');

        $result = $event->getMessagesAsString('Prefix: ');
        $this->assertStringContainsString('Prefix: ', $result);
        $this->assertStringContainsString('Error A', $result);
        $this->assertStringContainsString('Error B', $result);
    }

    public function testAddEmptyMessageIsIgnored(): void
    {
        $event = new DornLabEvent(1, 2);
        $event->addMessage('');

        $this->assertSame([], $event->getMessages());
    }

    public function testGetMessagesAsStringWithNoPrefixAndNoMessages(): void
    {
        $event = new DornLabEvent(1, 2);
        $this->assertSame('', $event->getMessagesAsString());
    }

    public function testGetMessagesAsStringClearsWhenRequested(): void
    {
        $event = new DornLabEvent(1, 2);
        $event->addMessage('msg1');

        $result = $event->getMessagesAsString('', true);
        $this->assertStringContainsString('msg1', $result);
        $this->assertSame([], $event->getMessages());
    }

    public function testSendOrderResponse(): void
    {
        $event = new DornLabEvent(1, 2);
        $response = ['status' => 'ok'];
        $event->setSendOrderResponse($response);

        $this->assertSame($response, $event->getSendOrderResponse());
    }
}
