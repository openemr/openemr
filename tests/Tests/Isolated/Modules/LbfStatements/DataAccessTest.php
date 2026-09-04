<?php

/**
 * Layout, LBF I/O, and rule repository tests against a QueryUtils stub.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Database {
    if (!class_exists(QueryUtils::class, false)) {
        final class QueryUtils
        {
            /** @var list<mixed> */
            public static array $queue = [];
            /** @var list<array<string, mixed>> */
            public static array $calls = [];
            public static int $insertId = 42;

            public static function reset(): void
            {
                self::$queue = [];
                self::$calls = [];
                self::$insertId = 42;
            }

            public static function fetchRecords($sqlStatement, $binds = [], $noLog = false): array
            {
                self::$calls[] = ['op' => 'fetch', 'sql' => (string) $sqlStatement, 'binds' => $binds];
                $next = array_shift(self::$queue);
                return is_array($next) ? $next : [];
            }

            public static function querySingleRow(string $sql, $params = [], bool $log = true): mixed
            {
                self::$calls[] = ['op' => 'one', 'sql' => $sql, 'binds' => $params];
                if (self::$queue === []) {
                    return null;
                }
                return array_shift(self::$queue);
            }

            public static function sqlStatementThrowException($statement, $binds = [], $noLog = false): bool
            {
                self::$calls[] = ['op' => 'exec', 'sql' => (string) $statement, 'binds' => $binds];
                return true;
            }

            public static function sqlInsert($statement, $binds = []): int
            {
                self::$calls[] = ['op' => 'insert', 'sql' => (string) $statement, 'binds' => $binds];
                return self::$insertId;
            }
        }
    }
}

namespace {
    $moduleSrc = dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-lbf-statements/src/';
    if (!is_dir($moduleSrc)) {
        throw new RuntimeException('LBF statements module source not found at ' . $moduleSrc);
    }
    spl_autoload_register(static function (string $class) use ($moduleSrc): void {
        $prefix = 'OpenEMR\\Modules\\LbfStatements\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = $moduleSrc . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\LbfStatements {

    use OpenEMR\Common\Database\QueryUtils;
    use OpenEMR\Modules\LbfStatements\LayoutCatalog;
    use OpenEMR\Modules\LbfStatements\LbfReader;
    use OpenEMR\Modules\LbfStatements\LbfWriter;
    use OpenEMR\Modules\LbfStatements\StatementRepository;
    use PHPUnit\Framework\TestCase;

    final class DataAccessTest extends TestCase
    {
        protected function setUp(): void
        {
            QueryUtils::reset();
        }

        public function testListAndFieldMeta(): void
        {
            QueryUtils::$queue[] = [
                ['grp_form_id' => 'LBFecho', 'grp_title' => 'Echo'],
                [0 => 'skip-me'],
            ];
            $catalog = new LayoutCatalog();
            $this->assertSame(
                [['form_id' => 'LBFecho', 'title' => 'Echo']],
                $catalog->listLbfForms()
            );

            QueryUtils::$queue[] = [
                ['field_id' => 'findings', 'data_type' => 3, 'title' => 'Findings', 'list_id' => '', 'seq' => 1, 'group_id' => '1'],
                ['field_id' => '', 'data_type' => 3, 'title' => 'No', 'list_id' => '', 'seq' => 2, 'group_id' => '1'],
            ];
            $meta = $catalog->fieldMeta('LBFecho');
            $this->assertSame(3, $meta['findings']['data_type']);
            $this->assertArrayNotHasKey('', $meta);
        }

        public function testParagraphFieldPrefersSummaryThenTextarea(): void
        {
            $catalog = new LayoutCatalog();
            QueryUtils::$queue[] = ['paragraph_field_id' => 'custom_box'];
            $this->assertSame('custom_box', $catalog->paragraphField('LBFecho'));

            QueryUtils::$queue[] = null;
            QueryUtils::$queue[] = [
                ['field_id' => 'summary_comments', 'data_type' => 3, 'title' => 'Sum', 'list_id' => '', 'seq' => 1, 'group_id' => '1'],
            ];
            $this->assertSame('summary_comments', $catalog->paragraphField('LBFecho'));
        }

        public function testSaveParagraphFieldRejectsNonTextarea(): void
        {
            $catalog = new LayoutCatalog();
            QueryUtils::$queue[] = [
                ['field_id' => 'num', 'data_type' => 2, 'title' => 'N', 'list_id' => '', 'seq' => 1, 'group_id' => '1'],
            ];
            $this->expectException(\InvalidArgumentException::class);
            $catalog->saveParagraphField('LBFecho', 'num');
        }

        public function testSaveParagraphFieldWritesTextarea(): void
        {
            $catalog = new LayoutCatalog();
            QueryUtils::$queue[] = [
                ['field_id' => 'notes', 'data_type' => 3, 'title' => 'N', 'list_id' => '', 'seq' => 1, 'group_id' => '1'],
            ];
            $catalog->saveParagraphField('LBFecho', 'notes');
            $this->assertSame('exec', QueryUtils::$calls[1]['op']);
        }

        public function testEnsureParagraphFieldInsertsWhenMissing(): void
        {
            $catalog = new LayoutCatalog();
            QueryUtils::$queue[] = null;
            QueryUtils::$queue[] = ['grp_group_id' => '2'];
            QueryUtils::$queue[] = [
                ['field_id' => 'stmt_paragraph', 'data_type' => 3, 'title' => 'Generated statements', 'list_id' => '', 'seq' => 1, 'group_id' => '2'],
            ];
            $this->assertSame('stmt_paragraph', $catalog->ensureParagraphField('LBFecho'));
        }

        public function testReaderPatientAndOwnership(): void
        {
            $reader = new LbfReader();
            $this->assertNull($reader->patientName(0));
            QueryUtils::$queue[] = ['pid' => 1, 'fname' => 'Ted', 'lname' => 'Shaw'];
            $this->assertSame(['pid' => 1, 'name' => 'Shaw, Ted'], $reader->patientName(1));
            QueryUtils::$queue[] = ['encounter' => 9];
            $this->assertTrue($reader->encounterOwnedBy(1, 9));
            $this->assertFalse($reader->encounterOwnedBy(1, 9));
        }

        public function testReaderInstancesAndValues(): void
        {
            $reader = new LbfReader();
            $this->assertSame([], $reader->instancesOnEncounter(0, 1, ['LBFecho']));
            $this->assertSame([], $reader->formdirsForPatient(1, []));
            QueryUtils::$queue[] = [
                ['formdir' => 'LBFecho', 'form_id' => 12, 'form_name' => 'Echo'],
                [0 => 'x'],
            ];
            $onEnc = $reader->instancesOnEncounter(1, 5, ['LBFecho']);
            $this->assertSame(12, $onEnc[0]['instance_id']);

            QueryUtils::$queue[] = [
                ['formdir' => 'LBFecho'],
                ['formdir' => 'LBFecho'],
                ['formdir' => ''],
            ];
            $this->assertSame(['LBFecho'], $reader->formdirsForPatient(1, ['LBFecho']));

            QueryUtils::$queue[] = [
                ['form_id' => 3, 'encounter' => 8, 'date' => '2026-01-02', 'form_name' => 'Echo', 'encounter_date' => '', 'reason' => str_repeat('r', 90)],
            ];
            $list = $reader->instancesForPatient('LBFecho', 1);
            $this->assertSame(3, $list[0]['instance_id']);
            $this->assertSame(8, $list[0]['encounter']);
            $this->assertStringEndsWith('...', $list[0]['reason']);

            QueryUtils::$queue[] = ['id' => 1, 'pid' => 1, 'encounter' => 8, 'formdir' => 'LBFecho', 'form_id' => 3, 'date' => '2026-01-02'];
            $this->assertSame(1, $reader->instanceRow(3, 'LBFecho')['pid']);

            QueryUtils::$queue[] = [
                ['field_id' => 'findings', 'field_value' => 'mild'],
                ['field_id' => '', 'field_value' => 'x'],
            ];
            $this->assertSame(['findings' => 'mild'], $reader->readValues(3));
        }

        public function testWriterInsertUpdateDelete(): void
        {
            $writer = new LbfWriter();
            $writer->write(3, ['notes' => 'hi', 'gone' => ''], ['gone' => 'old'], [
                ['field_id' => ''],
                ['field_id' => 'notes'],
                ['field_id' => 'gone'],
            ]);
            $ops = array_column(QueryUtils::$calls, 'op');
            $this->assertContains('exec', $ops);
            $writer->write(3, ['notes' => 'hi'], [], [['field_id' => 'notes']]);
            $this->assertSame('exec', QueryUtils::$calls[array_key_last(QueryUtils::$calls)]['op']);
        }

        public function testRepositoryRulesAndSave(): void
        {
            $repo = new StatementRepository();
            QueryUtils::$queue[] = [['form_id' => 'LBFecho'], [0 => 'x']];
            $this->assertSame(['LBFecho'], $repo->formIdsWithRules());

            QueryUtils::$queue[] = [['id' => 1, 'form_id' => 'LBFecho', 'source_field_id' => 'n', 'op' => 'band', 'enabled' => 1]];
            $this->assertCount(1, $repo->rulesForForm('LBFecho', true));

            QueryUtils::$queue[] = ['id' => 2, 'form_id' => 'LBFecho', 'op' => 'band'];
            $this->assertSame(2, $repo->getRule(2)['id']);

            QueryUtils::$queue[] = [];
            $id = $repo->saveRule([
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'op' => 'band',
                'min_value' => 0,
                'max_value' => 1,
                'min_inclusive' => 1,
                'max_inclusive' => 1,
                'statement_text' => 'In range.',
                'seq' => 10,
                'enabled' => 1,
            ]);
            $this->assertSame(42, $id);

            QueryUtils::$queue[] = [];
            $this->assertSame(7, $repo->saveRule([
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'op' => 'parse_severity',
                'match_token' => 'mild',
                'statement_text' => 'Mild.',
                'enabled' => 1,
            ], 7));

            QueryUtils::$queue[] = ['id' => 7, 'form_id' => 'LBFecho', 'source_field_id' => 'n', 'op' => 'parse_severity', 'enabled' => 0];
            $repo->setEnabled(7, true);

            $repo->logRun('LBFecho', 1, 3, 'admin', 'overwrite');
            $this->assertSame('insert', QueryUtils::$calls[array_key_last(QueryUtils::$calls)]['op']);
        }

        public function testRepositoryRejectsOverlapAndBadMode(): void
        {
            $repo = new StatementRepository();
            QueryUtils::$queue[] = [[
                'id' => 1,
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'source_field_id_2' => '',
                'op' => 'band',
                'min_value' => 0,
                'max_value' => 5,
                'min_inclusive' => 1,
                'max_inclusive' => 1,
                'enabled' => 1,
            ]];
            $this->expectException(\InvalidArgumentException::class);
            $repo->saveRule([
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'op' => 'band',
                'min_value' => 4,
                'max_value' => 9,
                'min_inclusive' => 1,
                'max_inclusive' => 1,
                'enabled' => 1,
                'statement_text' => 'X',
            ]);
        }
    }
}
