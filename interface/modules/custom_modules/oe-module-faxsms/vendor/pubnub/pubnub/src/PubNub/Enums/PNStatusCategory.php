<?php

namespace PubNub\Enums;


class PNStatusCategory
{
    const PNUnknownCategory = 1;
    const PNAcknowledgmentCategory = 2;
    const PNAccessDeniedCategory = 3;
    const PNTimeoutCategory = 4;
    const PNNetworkIssuesCategory = 5;
    const PNConnectedCategory = 6;
    const PNReconnectedCategory = 7;
    const PNDisconnectedCategory = 8;
    const PNUnexpectedDisconnectCategory = 9;
    const PNCancelledCategory = 10;
    const PNBadRequestCategory = 11;
    const PNMalformedFilterExpressionCategory = 12;
    const PNMalformedResponseCategory = 13;
    const PNDecryptionErrorCategory = 14;
    const PNTLSConnectionFailedCategory = 15;
    const PNTLSUntrustedCertificateCategory = 16;
    const PNRequestMessageCountExceededCategory = 17;
    const PNNoStubMatchedCategory = 18;
}