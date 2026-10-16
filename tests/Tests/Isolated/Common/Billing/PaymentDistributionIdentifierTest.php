<?php

/**
 * Isolated tests for payment distribution identifiers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Billing;

use OpenEMR\Common\Billing\PaymentDistributionIdentifier;
use PHPUnit\Framework\TestCase;

class PaymentDistributionIdentifierTest extends TestCase
{
    /**
     * Encoded separators and special characters round-trip without changing field boundaries.
     */
    public function testIdentifierRoundTripsSpecialCharactersInFields(): void
    {
        $identifier = PaymentDistributionIdentifier::encode(
            'payment_1',
            'patient_2',
            'encounter_3',
            'HCPCS/A01',
            'MOD/1+%',
            'HCPCS'
        );

        $this->assertSame(
            'payment_1/patient_2/encounter_3/HCPCS%2FA01/MOD%2F1%2B%25/HCPCS',
            $identifier
        );
        $this->assertSame(
            ['payment_1', 'patient_2', 'encounter_3', 'HCPCS/A01', 'MOD/1+%', 'HCPCS'],
            PaymentDistributionIdentifier::decode($identifier)
        );
    }
}
