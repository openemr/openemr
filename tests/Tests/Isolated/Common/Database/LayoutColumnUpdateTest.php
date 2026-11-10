<?php

/**
 * A layout column is refused when it would rewrite the row key or break the quote.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Database;

use OpenEMR\Common\Database\LayoutColumnUpdate;
use OpenEMR\Common\Database\SqlQueryException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LayoutColumnUpdateTest extends TestCase
{
    /**
     * A patient identity column is refused before a statement is built.
     */
    #[Test]
    #[DataProvider('refusedPatientColumns')]
    public function testAPatientIdentityColumnIsRefused(string $column): void
    {
        $this->expectException(SqlQueryException::class);
        LayoutColumnUpdate::patientStatement($column);
    }

    /**
     * A visit identity column is refused before a statement is built.
     */
    #[Test]
    #[DataProvider('refusedVisitColumns')]
    public function testAVisitIdentityColumnIsRefused(string $column): void
    {
        $this->expectException(SqlQueryException::class);
        LayoutColumnUpdate::encounterStatement($column);
    }

    /**
     * A field id that already contains a backtick is refused.
     */
    #[Test]
    public function testABacktickInTheFieldIdIsRefused(): void
    {
        $this->expectException(SqlQueryException::class);
        LayoutColumnUpdate::encounterStatement('reason` = 1; --');
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedPatientColumns(): array
    {
        return [
            'id' => ['id'],
            'uuid' => ['uuid'],
            'pid' => ['pid'],
            'encounter' => ['encounter'],
            'empty' => [''],
        ];
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedVisitColumns(): array
    {
        return [
            'id' => ['id'],
            'uuid' => ['uuid'],
            'pid' => ['pid'],
            'encounter' => ['encounter'],
            'empty' => [''],
        ];
    }
}
