<?php
declare(strict_types=1);

namespace Aranyasen\HL7\Tests;

use Aranyasen\HL7\Message;
use Aranyasen\HL7\Messages\ACK;
use Aranyasen\HL7\Segments\MSH;
use Exception;

class AckTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test()
    {
        $msg = new Message();
        $msg->addSegment(new MSH());

        $msh = $msg->getSegmentByIndex(0);
        $msh->setField(15, 'AL');
        $msh->setField(16, 'NE');

        $ack = new ACK($msg);

        $seg1 = $ack->getSegmentByIndex(1);

        self::assertSame('CA', $seg1->getField(1), 'Error code is CA');

        $msg = new Message();
        $msh = new MSH();
        $msh->setField(15);
        $msh->setField(16, 'NE');
        $msg->addSegment($msh);
        $ack = new ACK($msg);

        $seg1 = $ack->getSegmentByIndex(1);

        self::assertSame('CA', $seg1->getField(1), 'Error code is CA');

        $msg = new Message();
        $msh = new MSH();
        $msh->setField(16);
        $msh->setField(15);
        $msg->addSegment($msh);
        $ack = new ACK($msg);

        $seg1 = $ack->getSegmentByIndex(1);

        self::assertSame('AA', $seg1->getField(1), 'Error code is AA');

        $ack->setAckCode('E');
        self::assertSame('AE', $seg1->getField(1), 'Error code is AE');

        $ack->setAckCode('CR');
        self::assertSame('CR', $seg1->getField(1), 'Error code is CR');

        $ack->setAckCode('CR', 'XX');
        self::assertSame('XX', $seg1->getField(3), 'Set message and code');

        $msg = new Message();
        $msg->addSegment(new MSH());
        $msh = $msg->getSegmentByIndex(0);
        $msh->setField(16, 'NE');
        $msh->setField(11, 'P');
        $msh->setField(12, '2.4');
        $msh->setField(15, 'NE');

        $ack = new ACK($msg);
        $seg0 = $ack->getSegmentByIndex(0);
        self::assertSame('P', $seg0->getField(11), 'Field 11 is P');
        self::assertSame('2.4', $seg0->getField(12), 'Field 12 is 2.4');
        self::assertSame('NE', $seg0->getField(15), 'Field 15 is NE');
        self::assertSame('NE', $seg0->getField(16), 'Field 16 is NE');

        $ack = new ACK($msg);
        $ack->setErrorMessage('Some error');
        $seg1 = $ack->getSegmentByIndex(1);
        self::assertSame('Some error', $seg1->getField(3), 'Setting error message');
        self::assertSame('CE', $seg1->getField(1), 'Code CE after setting message');
    }

    /** @test
     * @throws Exception
     */
    public function a_MSH_can_be_provided_to_get_the_fields_from(): void
    {
        $msg = new Message("MSH|^~\\&|1|\rPV1|1|O|^AAAA1^^^BB|");
        $msh = new MSH(['MSH', '^~\&', 'HL7 Corp', 'HL7 HQ', 'VISION', 'MISYS', '200404061744', '', ['DFT', 'P03'], 'TC-22222', 'T', '2.3']);
        $ack = new ACK($msg, $msh);
        self::assertSame("MSH|^~\&|VISION|MISYS|HL7 Corp|HL7 HQ|||ACK|\nMSA|AA|TC-22222|\n", $ack->toString(true));
    }

    /**
     * @test
     * @throws Exception
     */
    public function globals_can_be_passed_to_constructor(): void
    {
        $msg = new Message("MSH|^~\\&|1|\rPV1|1|O|^AAAA1^^^BB|");
        $ack = new ACK($msg, null, ['SEGMENT_SEPARATOR' => '\r\n']);
        self::assertSame("MSH|^~\&|1||||||ACK|\r\nMSA|AA|\r\n", $ack->toString(true));
    }
}
