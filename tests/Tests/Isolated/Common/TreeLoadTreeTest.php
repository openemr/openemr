<?php

/**
 * Isolated tests for Tree::load_tree with folders whose parent has a higher id than the folder itself
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common;

use PHPUnit\Framework\TestCase;

class TreeLoadTreeTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../library/classes/Tree.class.php';
    }

    /**
     * @param list<array{id: int, name: string, value: string, parent: int, lft: int, rght: int}> $rows
     * @return list<string>
     */
    private function loadPaths(array $rows): array
    {
        $db = new class ($rows) {
            /** @param list<array{id: int, name: string, value: string, parent: int, lft: int, rght: int}> $table */
            public function __construct(private readonly array $table)
            {
            }

            public function ErrorMsg(): string
            {
                return '';
            }

            /** @param list<int> $binds */
            public function Execute(string $sql, array $binds = []): object
            {
                if (str_contains($sql, 'WHERE id=?')) {
                    $found = array_values(array_filter($this->table, static fn (array $r): bool => $r['id'] === $binds[0]));
                    return $this->result($found);
                }
                $found = array_values(array_filter($this->table, static fn (array $r): bool => $r['lft'] >= $binds[0] && $r['lft'] <= $binds[1]));
                usort($found, static fn (array $a, array $b): int => [$a['parent'], $a['name']] <=> [$b['parent'], $b['name']]);
                return $this->result($found);
            }

            /** @param list<array{id: int, name: string, value: string, parent: int, lft: int, rght: int}> $found */
            private function result(array $found): object
            {
                return new class ($found) {
                    /** @var array<string, mixed> */
                    public array $fields;
                    public bool $EOF;
                    private int $position = 0;

                    /** @param list<array<string, mixed>> $rows */
                    public function __construct(private readonly array $rows)
                    {
                        $this->EOF = $rows === [];
                        $this->fields = $rows[0] ?? [];
                    }

                    public function MoveNext(): void
                    {
                        $this->position++;
                        $this->EOF = $this->position >= count($this->rows);
                        $this->fields = $this->EOF ? [] : $this->rows[$this->position];
                    }
                };
            }
        };

        $tree = (new \ReflectionClass(\Tree::class))->newInstanceWithoutConstructor();
        $tree->_root = 1;
        $tree->_root_type = ROOT_TYPE_ID;
        $tree->_table = 'categories';
        $tree->_db = $db;
        $tree->load_tree();

        $paths = [];
        $walk = static function (array $nodes, string $prefix) use (&$walk, &$paths): void {
            foreach ($nodes as $key => $value) {
                $paths[] = $prefix . $key;
                if (is_array($value)) {
                    $walk($value, $prefix . $key . '/');
                }
            }
        };
        $loaded = $tree->tree;
        $walk(is_array($loaded) ? $loaded : [], '');
        return $paths;
    }

    /**
     * @return array{id: int, name: string, value: string, parent: int, lft: int, rght: int}
     */
    private static function row(int $id, string $name, int $parent, int $lft, int $rght): array
    {
        return ['id' => $id, 'name' => $name, 'value' => '', 'parent' => $parent, 'lft' => $lft, 'rght' => $rght];
    }

    public function testFolderMovedUnderANewerFolderKeepsItsChildren(): void
    {
        $paths = $this->loadPaths([
            self::row(1, 'Categories', 0, 0, 20),
            self::row(6, 'Advance Directive', 45, 3, 10),
            self::row(7, 'Do Not Resuscitate Order', 6, 4, 5),
            self::row(8, 'Durable Power of Attorney', 6, 6, 7),
            self::row(9, 'Living Will', 6, 8, 9),
            self::row(45, 'Consents & Directives', 1, 2, 19),
            self::row(46, 'Consent to Treat', 45, 11, 12),
        ]);

        $this->assertContains('1/45/6/7', $paths);
        $this->assertContains('1/45/6/8', $paths);
        $this->assertContains('1/45/6/9', $paths);
        $this->assertContains('1/45/46', $paths);
        foreach ($paths as $path) {
            $this->assertStringStartsWith('1', $path, 'no node is merged under an empty key');
        }
    }

    public function testTreeWhoseParentsHaveLowerIdsKeepsTheDatabaseOrder(): void
    {
        $paths = $this->loadPaths([
            self::row(1, 'Categories', 0, 0, 11),
            self::row(2, 'Zeta', 1, 1, 2),
            self::row(3, 'Alpha', 1, 3, 6),
            self::row(4, 'Child', 3, 4, 5),
            self::row(5, 'Beta', 1, 7, 8),
        ]);

        $top = array_values(array_filter($paths, static fn (string $p): bool => substr_count($p, '/') === 1 && $p !== '1/0'));
        $this->assertSame(['1/3', '1/5', '1/2'], $top, 'siblings stay in the order of ORDER BY parent, name (Alpha, Beta, Zeta)');
        $this->assertContains('1/3/4', $paths);
    }
}
