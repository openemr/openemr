<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

/**
 * Regression coverage for fixture-manager ownership during cleanup.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FacilityFixtureManagerTest extends TestCase
{
    public function testRemovalPreservesOtherFixtureRowsWithTheSharedPrefix(): void
    {
        $manager = new FacilityFixtureManager();
        $manager->installFacilityFixtures();
        $independentName = 'test-fixture-independent-' . bin2hex(random_bytes(8));
        $independentId = QueryUtils::sqlInsert(
            'INSERT INTO facility (name) VALUES (?)',
            [$independentName]
        );

        try {
            $manager->removeFixtures();

            $remainingFacility = QueryUtils::querySingleRow(
                'SELECT id FROM facility WHERE id = ?',
                [$independentId]
            );
            if (!is_array($remainingFacility) || !isset($remainingFacility['id']) || !is_numeric($remainingFacility['id'])) {
                self::fail('The independent facility fixture was removed.');
            }
            self::assertSame($independentId, (int) $remainingFacility['id']);
        } finally {
            try {
                $manager->removeFixtures();
            } finally {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM facility WHERE id = ?',
                    [$independentId]
                );
            }
        }
    }
}
