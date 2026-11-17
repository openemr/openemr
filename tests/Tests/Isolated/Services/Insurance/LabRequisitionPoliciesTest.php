<?php

/**
 * LabRequisitionPolicies: the primary and secondary a paper lab requisition prints.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Insurance;

use OpenEMR\Services\Insurance\EffectiveInsurance;
use OpenEMR\Services\Insurance\EffectiveInsuranceRow;
use OpenEMR\Services\Insurance\InsuranceLevel;
use OpenEMR\Services\Insurance\LabRequisitionPolicies;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LabRequisitionPoliciesTest extends TestCase
{
    private static function row(InsuranceLevel $level, int $id, int $companyId, ?string $companyName): EffectiveInsuranceRow
    {
        return new EffectiveInsuranceRow(
            new EffectiveInsurance($level, $id, null, $companyId, null, null),
            $companyName,
            ['id' => $id, 'type' => $level->value, 'provider' => (string) $companyId, 'policy_number' => "P-$id"]
        );
    }

    /**
     * The primary and secondary in force come back as rows whose 'name' and 'id' are their company's; a tertiary isn't printed.
     */
    #[Test]
    public function thePrimaryAndSecondaryInForceAreTheirRowsWithTheirCompanys(): void
    {
        $rows = [
            self::row(InsuranceLevel::Primary, 7, 101, 'Acme Health'),
            self::row(InsuranceLevel::Secondary, 8, 102, 'Second Mutual'),
            self::row(InsuranceLevel::Tertiary, 9, 103, 'Third Plan'),
        ];

        $printed = LabRequisitionPolicies::fromRows($rows);

        self::assertSame(['id' => 101, 'type' => 'primary', 'provider' => '101', 'policy_number' => 'P-7', 'name' => 'Acme Health'], $printed['primary']);
        self::assertSame(['id' => 102, 'type' => 'secondary', 'provider' => '102', 'policy_number' => 'P-8', 'name' => 'Second Mutual'], $printed['secondary']);
    }

    /**
     * A level with nothing in force prints nothing; a company with no row keeps its id and has no name.
     */
    #[Test]
    public function aLevelWithNothingInForceIsEmpty(): void
    {
        $printed = LabRequisitionPolicies::fromRows([self::row(InsuranceLevel::Secondary, 8, 102, null)]);

        self::assertSame([], $printed['primary']);
        self::assertSame(102, $printed['secondary']['id']);
        self::assertNull($printed['secondary']['name']);
    }

    /**
     * No policy in force (or no lab day to ask about) prints none.
     */
    #[Test]
    public function noPolicyInForcePrintsNone(): void
    {
        self::assertSame(['primary' => [], 'secondary' => []], LabRequisitionPolicies::fromRows([]));
    }
}
