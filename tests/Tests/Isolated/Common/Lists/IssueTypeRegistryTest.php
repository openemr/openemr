<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Lists;

use InvalidArgumentException;
use OpenEMR\Common\Lists\IssueTypeRegistry;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

/**
 * Covers only the two Registry methods that do no database work:
 * issueTypeStyles() and issueClassifications(), plus the code-driven
 * registration API (registerStyle()/registerClassification()). The other two
 * getters hit the `issue_types` table and belong in the DB-backed test suite.
 *
 * disable_translation short-circuits xl() past its cache/DB path so these
 * pure helpers can be exercised without a database connection.
 */
#[Small]
#[BackupGlobals(true)]
class IssueTypeRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['disable_translation'] = true;
        IssueTypeRegistry::reset();
    }

    protected function tearDown(): void
    {
        // Registrations live in static state; clear them so they cannot leak
        // into other tests sharing this process.
        IssueTypeRegistry::reset();
    }

    public function testIssueTypeStylesReturnsIntKeyedLabels(): void
    {
        $styles = IssueTypeRegistry::issueTypeStyles();

        self::assertSame(
            [0, 1, 2, 3, 4],
            array_keys($styles),
            'issueTypeStyles must return the five hard-coded style ids in order',
        );
        self::assertSame('Standard', $styles[0], 'style 0 is the Standard label');
        self::assertSame('IPPF Contraception', $styles[4], 'style 4 is the IPPF Contraception label');
    }

    public function testIssueTypeStylesWritesLegacyGlobalForBcReaders(): void
    {
        $styles = IssueTypeRegistry::issueTypeStyles();

        self::assertSame(
            $styles,
            $GLOBALS['ISSUE_TYPE_STYLES'] ?? null,
            '$GLOBALS[ISSUE_TYPE_STYLES] must mirror the returned array for legacy `global` readers',
        );
    }

    public function testIssueClassificationsReturnsIntKeyedLabels(): void
    {
        $classifications = IssueTypeRegistry::issueClassifications();

        self::assertSame(
            [0, 1, 2],
            array_keys($classifications),
            'issueClassifications must return the three hard-coded classification ids in order',
        );
        self::assertSame('Unknown or N/A', $classifications[0], 'classification 0 is the Unknown label');
        self::assertSame('Trauma', $classifications[1], 'classification 1 is the Trauma label');
        self::assertSame('Overuse', $classifications[2], 'classification 2 is the Overuse label');
    }

    public function testIssueClassificationsWritesLegacyGlobalForBcReaders(): void
    {
        $classifications = IssueTypeRegistry::issueClassifications();

        self::assertSame(
            $classifications,
            $GLOBALS['ISSUE_CLASSIFICATIONS'] ?? null,
            '$GLOBALS[ISSUE_CLASSIFICATIONS] must mirror the returned array for legacy `global` readers',
        );
    }

    public function testRegisterStyleAppendsAndKeepsNumericOrder(): void
    {
        IssueTypeRegistry::registerStyle(5, 'Problem');

        $styles = IssueTypeRegistry::issueTypeStyles();

        self::assertSame([0, 1, 2, 3, 4, 5], array_keys($styles), 'registered style slots in by id order');
        self::assertSame('Problem', $styles[5], 'registered label is returned');
        self::assertSame(
            $styles,
            $GLOBALS['ISSUE_TYPE_STYLES'] ?? null,
            'registered style is mirrored to the legacy global',
        );
    }

    public function testRegisterStyleRejectsCollisionWithoutOverwrite(): void
    {
        $this->expectException(InvalidArgumentException::class);

        IssueTypeRegistry::registerStyle(0, 'Not Standard');
    }

    public function testRegisterStyleOverwriteReplacesLabel(): void
    {
        IssueTypeRegistry::registerStyle(2, 'Sports Injury', true);

        self::assertSame('Sports Injury', IssueTypeRegistry::issueTypeStyles()[2]);
    }

    public function testRegisterStyleRejectsDuplicateRegistrationWithoutOverwrite(): void
    {
        IssueTypeRegistry::registerStyle(5, 'Problem');

        $this->expectException(InvalidArgumentException::class);

        IssueTypeRegistry::registerStyle(5, 'Problem Again');
    }

    public function testRegisterStyleRejectsEmptyLabel(): void
    {
        $this->expectException(InvalidArgumentException::class);

        IssueTypeRegistry::registerStyle(5, '   ');
    }

    public function testRegisterClassificationAppendsAndKeepsNumericOrder(): void
    {
        IssueTypeRegistry::registerClassification(3, 'Repetitive Strain');

        $classifications = IssueTypeRegistry::issueClassifications();

        self::assertSame([0, 1, 2, 3], array_keys($classifications));
        self::assertSame('Repetitive Strain', $classifications[3]);
    }

    public function testResetClearsRegistrations(): void
    {
        IssueTypeRegistry::registerStyle(5, 'Problem');
        IssueTypeRegistry::reset();

        self::assertSame([0, 1, 2, 3, 4], array_keys(IssueTypeRegistry::issueTypeStyles()));
    }
}
