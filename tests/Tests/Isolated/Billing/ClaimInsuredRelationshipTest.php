<?php

/**
 * The patient's relationship to the insured as the claim sends it (Claim::insuredRelationship(): the
 * 837's SBR02 and PAT01, the HCFA 1500's box 6, the UB-04's 59): an X12 individual relationship code.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\Claim;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ClaimInsuredRelationshipTest extends TestCase
{
    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function relationships(): array
    {
        return [
            'self' => ['self', '18'],
            'spouse' => ['spouse', '01'],
            'child' => ['child', '19'],
            'other' => ['other', 'G8'],
            'a stored case other than lower' => ['Spouse', '01'],
            "a site's own word: Other Relationship" => ['grandchild', 'G8'],
            "a site's own X12 code, as stored: life partner" => ['53', '53'],
            "a site's own X12 code, as stored: employee" => ['20', '20'],
            'an X12 code stored in lower case' => ['g8', 'G8'],
            "the new-patient screen's blank choice: Other Relationship" => ['', 'G8'],
            'none stored: Other Relationship' => [null, 'G8'],
        ];
    }

    #[Test]
    #[DataProvider('relationships')]
    public function theStoredRelationshipIsAnX12Code(mixed $stored, string $code): void
    {
        self::assertSame($code, self::claim([['data' => ['subscriber_relationship' => $stored]]])->insuredRelationship());
    }

    /**
     * A position with no payer has no relationship to send.
     */
    #[Test]
    public function aPositionWithNoPayerSendsNone(): void
    {
        $claim = self::claim([['data' => ['subscriber_relationship' => 'grandchild']]]);

        self::assertSame('', $claim->insuredRelationship(1));
        self::assertSame('', self::claim([])->insuredRelationship());
    }

    /**
     * A claim whose constructor (which reads the database) is bypassed: insuredRelationship() reads only
     * its payers.
     *
     * @param list<array<string, mixed>> $payers
     */
    private static function claim(array $payers): Claim
    {
        $claim = new class extends Claim {
            public function __construct()
            {
            }
        };
        $claim->payers = $payers;
        return $claim;
    }
}
