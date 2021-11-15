<?php

namespace Aranyasen\HL7\Messages;

use Aranyasen\HL7\Message;
use Aranyasen\HL7\Segment;
use Aranyasen\HL7\Segments\MSA;
use Aranyasen\HL7\Segments\MSH;

class ACK extends Message
{
    protected $ACK_TYPE;

    /**
     * Usage:
     * ```php
     * $ack = new ACK($requestMessage);
     * ```
     *
     * Convenience module implementing an acknowledgement (ACK) message. This can be used in HL7 servers to create an
     * acknowledgement for an incoming message.
     *
     * @param Message|null $req
     * @param MSH|null $reqMsh
     * @param array $hl7Globals Set control characters or HL7 properties. e.g., ['HL7_VERSION' => '2.5']
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function __construct(Message $req = null, MSH $reqMsh = null, array $hl7Globals = null)
    {
        parent::__construct(null, $hl7Globals);

        if ($req) {
            $msh = $req->getSegmentByIndex(0);

            if ($msh) {
                $msh = new MSH($msh->getFields(1));
            }
            else {
                $msh = new MSH();
            }
        }
        else {
            $msh = new MSH();
        }

        $msa = new MSA();

        // Determine acknowledge mode: normal or enhanced
        //
        if ($req && ($msh->getField(15) || $msh->getField(16))) {
            $this->ACK_TYPE = 'E';
            $msa->setAcknowledgementCode('CA');
        }
        else {
            $this->ACK_TYPE = 'N';
            $msa->setAcknowledgementCode('AA');
        }

        $this->addSegment($msh);
        $this->addSegment($msa);

        $msh->setField(9, 'ACK');

        // Construct an ACK based on the request
        if ($req && $reqMsh) {
            $msh->setField(3, $reqMsh->getReceivingApplication());
            $msh->setField(4, $reqMsh->getReceivingFacility());
            $msh->setField(5, $reqMsh->getSendingApplication());
            $msh->setField(6, $reqMsh->getSendingFacility());
            $msa->setMessageControlID($reqMsh->getMessageControlId());
        }
    }

    /**
     * Set the acknowledgement code for the acknowledgement.
     *
     * Code should be one of: A, E, R. Codes can be prepended with C or A, denoting enhanced or normal acknowledge mode.
     * This denotes: accept, general error and reject respectively. The ACK module will determine the right answer mode
     * (normal or enhanced) based upon the request, if not provided. The message provided in $msg will be set in MSA 3.
     *
     * @param string $code Code to use in acknowledgement
     * @param string $msg Acknowledgement message
     * @return boolean
     * @access public
     */
    public function setAckCode(string $code, string $msg = null): bool
    {
        $mode = 'A';

        // Determine acknowledge mode: normal or enhanced
        //
        if ($this->ACK_TYPE === 'E') {
            $mode = 'C';
        }

        if (\strlen($code) === 1) {
            $code = "$mode$code";
        }

        $seg1 = $this->getSegmentByIndex(1);
        if (!empty($seg1)) {
            $seg1->setField(1, $code);
        }
        if ($msg) {
            $seg1->setField(3, $msg);
        }

        return true;
    }

    /**
     * Set the error message for acknowledgement.
     *
     * This will also set the error code to either AE or CE, depending on the mode of the incoming message.
     *
     * @param string $msg Error message
     * @access public
     */
    public function setErrorMessage(string $msg): void
    {
        $this->setAckCode('E', $msg);
    }
}
