<?php

/**
 * GpRelationServiceTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\GpRelationService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GpRelationServiceTest extends TestCase
{
    // Ids picked to not collide with any fixture; type1/type2 follow the documented convention
    // (type1 <= type2): 1 = documents, 6 = pnotes.
    private const TYPE1 = 1;
    private const TYPE2 = 6;
    private const ID1 = 987654321;
    private const ID2 = 987654322;
    private const ID1_B = 987654323;
    private const ID2_B = 987654324;

    /**
     * Starts every test from a known state: none of the fixture tuples exists.
     */
    protected function setUp(): void
    {
        $this->deleteFixtureRelations();
    }

    /**
     * Leaves no fixture tuple behind, whatever the test did.
     */
    protected function tearDown(): void
    {
        $this->deleteFixtureRelations();
    }

    /**
     * Removes every gprelations row that mentions one of the fixture ids.
     */
    private function deleteFixtureRelations(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `gprelations` WHERE `id1` IN (?, ?, ?, ?) OR `id2` IN (?, ?, ?, ?)",
            [self::ID1, self::ID2, self::ID1_B, self::ID2_B, self::ID1, self::ID2, self::ID1_B, self::ID2_B]
        );
    }

    /**
     * Writes a gprelations row directly, bypassing the code under test.
     */
    private function insertRelation(int|string $type1, int|string $id1, int|string $type2, int|string $id2): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `gprelations` (`type1`, `id1`, `type2`, `id2`) VALUES (?, ?, ?, ?)",
            [$type1, $id1, $type2, $id2]
        );
    }

    /**
     * Reads the row count directly, bypassing the code under test.
     */
    private function countRelation(int|string $type1, int|string $id1, int|string $type2, int|string $id2): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT COUNT(*) AS c FROM `gprelations` WHERE `type1` = ? AND `id1` = ? AND `type2` = ? AND `id2` = ?",
            [$type1, $id1, $type2, $id2]
        );
        $this->assertIsArray($row);
        $count = $row['c'] ?? null;
        $this->assertIsNumeric($count);
        return (int) $count;
    }

    /**
     * Characterization of library/gprelations.inc.php: isGpRelation() reports no relation when
     * no matching row exists.
     */
    #[Test]
    public function testIsGpRelationIsFalseWhenNoRowExists(): void
    {
        $this->assertSame(0, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
        $this->assertFalse(isGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
    }

    /**
     * Characterization of library/gprelations.inc.php: isGpRelation() reports a relation that
     * was written directly (i.e. not through setGpRelation()).
     */
    #[Test]
    public function testIsGpRelationIsTrueForARowWrittenDirectly(): void
    {
        $this->insertRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2);

        $this->assertTrue(isGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
    }

    /**
     * Characterization of library/gprelations.inc.php: setGpRelation() with $set true inserts
     * exactly one row, and calling it again is a no-op (no duplicate row).
     */
    #[Test]
    public function testSetGpRelationInsertsExactlyOneRowAndIsIdempotent(): void
    {
        setGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2, true);
        $this->assertSame(1, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));

        setGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2, true);
        $this->assertSame(1, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
    }

    /**
     * Characterization of library/gprelations.inc.php: setGpRelation() with $set false deletes
     * an existing row.
     */
    #[Test]
    public function testSetGpRelationFalseDeletesAnExistingRow(): void
    {
        $this->insertRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2);

        setGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2, false);
        $this->assertSame(0, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
    }

    /**
     * Characterization of library/gprelations.inc.php: setGpRelation() with $set false on a row
     * that does not exist does nothing and raises no exception.
     */
    #[Test]
    public function testSetGpRelationFalseOnAMissingRowDoesNothing(): void
    {
        $this->assertSame(0, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));

        setGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2, false);
        $this->assertSame(0, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
    }

    /**
     * Characterization of library/gprelations.inc.php: setGpRelation() only ever addresses the
     * exact (type1, id1, type2, id2) tuple it is called with, leaving an unrelated row alone.
     */
    #[Test]
    public function testSetGpRelationOnlyTouchesTheAddressedRow(): void
    {
        $this->insertRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2);
        $this->insertRelation(self::TYPE1, self::ID1_B, self::TYPE2, self::ID2_B);

        setGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2, false);

        $this->assertSame(0, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
        $this->assertSame(1, $this->countRelation(self::TYPE1, self::ID1_B, self::TYPE2, self::ID2_B));
    }

    /**
     * The service methods the shims delegate to return the same values as the shims, for both
     * the read and the write path.
     */
    #[Test]
    public function testStaticServiceMethodsMatchTheLegacyFunctions(): void
    {
        $this->assertSame(
            isGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2),
            GpRelationService::isGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2)
        );

        GpRelationService::setGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2, true);
        $this->assertSame(1, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
        $this->assertTrue(GpRelationService::isGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
        $this->assertSame(
            isGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2),
            GpRelationService::isGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2)
        );

        GpRelationService::setGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2, false);
        $this->assertSame(0, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
    }

    /**
     * The legacy shims accept anything, as they always did. A non-scalar key must keep yielding
     * "no relation" / "do nothing" instead of reaching the typed service methods and raising a
     * TypeError. Covers a non-scalar in the first argument and in a later one, for both shims.
     */
    #[Test]
    public function testLegacyShimsTreatNonScalarKeysAsNoRelation(): void
    {
        $this->assertFalse(isGpRelation([1], self::ID1, self::TYPE2, self::ID2));
        $this->assertFalse(isGpRelation(null, self::ID1, self::TYPE2, self::ID2));
        $this->assertFalse(isGpRelation(self::TYPE1, self::ID1, [self::TYPE2], self::ID2));

        setGpRelation(null, self::ID1, self::TYPE2, self::ID2);
        $this->assertSame(0, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));

        setGpRelation(self::TYPE1, self::ID1, self::TYPE2, [self::ID2]);
        $this->assertSame(0, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
    }

    /**
     * Characterization of library/gprelations.inc.php: setGpRelation() treats $set by PHP
     * truthiness, not by strict boolean identity — 0 is falsy (deletes), a non-empty string like
     * 'yes' is truthy (inserts).
     */
    #[Test]
    public function testShimKeepsLegacyTruthinessOfSet(): void
    {
        $this->insertRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2);
        setGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2, 0);
        $this->assertSame(0, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));

        setGpRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2, 'yes');
        $this->assertSame(1, $this->countRelation(self::TYPE1, self::ID1, self::TYPE2, self::ID2));
    }
}
