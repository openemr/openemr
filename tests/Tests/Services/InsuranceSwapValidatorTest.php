<?php

/**
 * The swap validator (CoverageValidator's swap context, which InsuranceService::swapInsurance() runs
 * first): a swap that would put two policies with one start date at a level is refused, whether the
 * source policy has a start date or not.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\CoverageValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InsuranceSwapValidatorTest extends TestCase
{
    private const PID = 99995701;

    protected function setUp(): void
    {
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * An undated secondary made primary sends the primary (dated 2025-01-01) to the secondary level,
     * which already holds a policy from 2025-01-01: the swap is refused for the source level.
     */
    #[Test]
    public function aSwapThatWouldDoubleASourceLevelsStartIsRefused(): void
    {
        $this->addPolicy('primary', '101', '2025-01-01');
        $undated = $this->addPolicy('secondary', '102', null);
        $this->addPolicy('secondary', '103', '2025-01-01');

        $messages = $this->swapMessages('primary', $undated);

        self::assertStringContainsString('Source coverage type already has a policy', $messages);
    }

    /**
     * A dated source too: making the 2025 primary secondary sends the secondary (2024-01-01) to the
     * primary level, which already holds a policy from 2024-01-01.
     */
    #[Test]
    public function aSwapThatWouldDoubleASourceLevelsStartIsRefusedForADatedSource(): void
    {
        $this->addPolicy('primary', '101', '2024-01-01');
        $dated = $this->addPolicy('primary', '102', '2025-01-01');
        $this->addPolicy('secondary', '103', '2024-01-01');

        self::assertStringContainsString('Source coverage type already has a policy', $this->swapMessages('secondary', $dated));
    }

    /**
     * Without that conflict the same swap is allowed.
     */
    #[Test]
    public function aSwapWithNoConflictingStartIsAllowed(): void
    {
        $this->addPolicy('primary', '101', '2025-01-01');
        $undated = $this->addPolicy('secondary', '102', null);
        $dated = $this->addPolicy('secondary', '103', '2024-01-01');

        self::assertSame('', $this->swapMessages('primary', $undated));
        self::assertSame('', $this->swapMessages('primary', $dated));
    }

    /**
     * @return string the validation messages, flattened ('' when the swap is valid)
     */
    private function swapMessages(string $targetType, string $sourceUuid): string
    {
        $result = (new CoverageValidator())->validate(
            ['pid' => self::PID, 'type' => $targetType, 'uuid' => $sourceUuid],
            CoverageValidator::DATABASE_SWAP_CONTEXT
        );
        return $result->isValid() ? '' : (string) json_encode($result->getValidationMessages());
    }

    /**
     * @return string the policy's uuid
     */
    private function addPolicy(string $type, string $provider, ?string $start): string
    {
        $uuid = (new UuidRegistry(['table_name' => 'insurance_data']))->createUuid();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (uuid, pid, type, provider, date, plan_name) VALUES (?, ?, ?, ?, ?, 'test-fixture')",
            [$uuid, self::PID, $type, $provider, $start]
        );
        return UuidRegistry::uuidToString($uuid);
    }

    private function cleanUp(): void
    {
        foreach (QueryUtils::fetchTableColumn('SELECT uuid FROM insurance_data WHERE pid = ? AND uuid IS NOT NULL', 'uuid', [self::PID]) as $uuid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'insurance_data' AND uuid = ?", [$uuid]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
    }
}
