<?php

/**
 * The literal layout statements have to keep matching the schema.
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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;

class LayoutColumnCatalogTest extends TestCase
{
    #[Test]
    public function testEveryWritablePatientColumnHasOneLiteralStatement(): void
    {
        $this->assertCatalog('patient_data', 'PATIENT', ['id', 'uuid', 'pid'], 'pid = ?');
    }

    #[Test]
    public function testEveryWritableVisitColumnHasOneLiteralStatement(): void
    {
        $this->assertCatalog(
            'form_encounter',
            'ENCOUNTER',
            ['id', 'uuid', 'pid', 'encounter'],
            'pid = ? AND encounter = ?'
        );
    }

    /**
     * @param list<string> $refused
     */
    private function assertCatalog(string $table, string $constant, array $refused, string $where): void
    {
        $schema = $this->schemaColumns($table);
        $map = $this->statements($constant);
        $writable = array_values(array_diff($schema, $refused));
        sort($writable);
        $known = array_keys($map);
        sort($known);

        $this->assertSame($writable, $known);

        foreach ($refused as $column) {
            $this->assertNotContains($column, $known);
        }

        foreach ($map as $column => $statement) {
            $this->assertSame(
                'UPDATE ' . $table . ' SET `' . $column . '` = ? WHERE ' . $where,
                $statement
            );
            $this->assertStringNotContainsString('``', $statement);
        }
    }

    #[Test]
    public function testRefusedIdentityColumnsThrow(): void
    {
        foreach (['id', 'uuid', 'pid'] as $column) {
            try {
                LayoutColumnUpdate::patientStatement($column);
                $this->fail($column . ' was accepted');
            } catch (SqlQueryException) {
                $this->addToAssertionCount(1);
            }
        }

        foreach (['id', 'uuid', 'pid', 'encounter'] as $column) {
            try {
                LayoutColumnUpdate::encounterStatement($column);
                $this->fail($column . ' was accepted');
            } catch (SqlQueryException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function schemaColumns(string $table): array
    {
        $sql = (string) file_get_contents(dirname(__DIR__, 5) . '/sql/database.sql');
        $matched = preg_match('/CREATE TABLE `' . $table . '` \((.*?)\) ENGINE/s', $sql, $create);
        $this->assertSame(1, $matched);
        preg_match_all('/^\s*`([A-Za-z0-9_]+)`/m', $create[1], $columns);

        return $columns[1];
    }

    /**
     * @return array<string, string>
     */
    private function statements(string $constant): array
    {
        $value = (new ReflectionClassConstant(LayoutColumnUpdate::class, $constant))->getValue();
        $this->assertIsArray($value);
        $statements = [];
        foreach ($value as $column => $statement) {
            $this->assertIsString($column);
            $this->assertIsString($statement);
            $statements[$column] = $statement;
        }

        return $statements;
    }
}
