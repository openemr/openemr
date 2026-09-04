<?php

/**
 * Layout, LBF I/O, and rule repository tests against a Queries fake.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

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

    use OpenEMR\Modules\LbfStatements\BandLockException;
    use OpenEMR\Modules\LbfStatements\BandOverlapException;
    use OpenEMR\Modules\LbfStatements\InvertedBoundsException;
    use OpenEMR\Modules\LbfStatements\LayoutCatalog;
    use OpenEMR\Modules\LbfStatements\LbfReader;
    use OpenEMR\Modules\LbfStatements\LbfWriter;
    use OpenEMR\Modules\LbfStatements\Queries;
    use OpenEMR\Modules\LbfStatements\RuleNotFoundException;
    use OpenEMR\Modules\LbfStatements\StatementRepository;
    use PHPUnit\Framework\TestCase;

    final class FakeQueries extends Queries
    {
        /** @var list<mixed> */
        public array $queue = [];
        /** @var list<array{op:string,sql:string,binds:mixed}> */
        public array $calls = [];
        public int $insertId = 42;
        public int $transactions = 0;
        public bool $lockSucceeds = true;
        /** @var list<array{op:string,name:string}> */
        public array $locks = [];

        /**
         * @param array<int|string, mixed> $binds
         * @return list<array<mixed>>
         */
        public function fetchRecords(string $sql, array $binds = []): array
        {
            $this->calls[] = ['op' => 'fetch', 'sql' => $sql, 'binds' => $binds];
            $next = array_shift($this->queue);
            if (!is_array($next)) {
                return [];
            }
            $out = [];
            foreach ($next as $row) {
                if (is_array($row)) {
                    $out[] = $row;
                }
            }
            return $out;
        }

        /**
         * @param array<int|string, mixed> $params
         */
        public function querySingleRow(string $sql, array $params = []): mixed
        {
            $this->calls[] = ['op' => 'one', 'sql' => $sql, 'binds' => $params];
            if ($this->queue === []) {
                return null;
            }
            return array_shift($this->queue);
        }

        /**
         * @param array<int|string, mixed> $binds
         */
        public function sqlStatementThrowException(string $sql, array $binds = []): mixed
        {
            $this->calls[] = ['op' => 'exec', 'sql' => $sql, 'binds' => $binds];
            return true;
        }

        /**
         * @param array<int|string, mixed> $binds
         */
        public function sqlInsert(string $sql, array $binds = []): int
        {
            $this->calls[] = ['op' => 'insert', 'sql' => $sql, 'binds' => $binds];
            return $this->insertId;
        }

        /**
         * @template T
         * @param callable(): T $action
         * @return T
         */
        public function inTransaction(callable $action): mixed
        {
            $this->transactions++;
            $this->calls[] = ['op' => 'tx', 'sql' => 'inTransaction', 'binds' => []];
            return $action();
        }

        /**
         * Record GET_LOCK in the fake and return the configured result.
         */
        public function acquireLock(string $name, int $timeoutSeconds = 10): bool
        {
            $this->calls[] = ['op' => 'lock', 'sql' => 'GET_LOCK', 'binds' => [$name, $timeoutSeconds]];
            $this->locks[] = ['op' => 'get', 'name' => $name];
            return $this->lockSucceeds;
        }

        /**
         * Record RELEASE_LOCK in the fake.
         */
        public function releaseLock(string $name): void
        {
            $this->calls[] = ['op' => 'unlock', 'sql' => 'RELEASE_LOCK', 'binds' => [$name]];
            $this->locks[] = ['op' => 'release', 'name' => $name];
        }
    }

    final class DataAccessTest extends TestCase
    {
        private FakeQueries $sql;

        /**
         * Create fixtures for this test case.
         */
        protected function setUp(): void
        {
            $this->sql = new FakeQueries();
        }

        /**
         * List LBF layouts and skip empty field ids.
         */
        public function testListAndFieldMeta(): void
        {
            $this->sql->queue[] = [
                ['grp_form_id' => 'LBFecho', 'grp_title' => 'Echo'],
                [0 => 'skip-me'],
            ];
            $catalog = new LayoutCatalog($this->sql);
            $this->assertSame(
                [['form_id' => 'LBFecho', 'title' => 'Echo']],
                $catalog->listLbfForms()
            );

            $this->sql->queue[] = [
                ['field_id' => 'findings', 'data_type' => 3, 'title' => 'Findings', 'list_id' => '', 'seq' => 1, 'group_id' => '1'],
                ['field_id' => '', 'data_type' => 3, 'title' => 'No', 'list_id' => '', 'seq' => 2, 'group_id' => '1'],
            ];
            $meta = $catalog->fieldMeta('LBFecho');
            $this->assertSame(3, $meta['findings']['data_type']);
            $this->assertArrayNotHasKey('', $meta);
        }

        /**
         * Prefer the configured field, then summary_comments.
         */
        public function testParagraphFieldPrefersSummaryThenTextarea(): void
        {
            $catalog = new LayoutCatalog($this->sql);
            $this->sql->queue[] = ['paragraph_field_id' => 'custom_box'];
            $this->assertSame('custom_box', $catalog->paragraphField('LBFecho'));

            $this->sql->queue[] = null;
            $this->sql->queue[] = [
                ['field_id' => 'summary_comments', 'data_type' => 3, 'title' => 'Sum', 'list_id' => '', 'seq' => 1, 'group_id' => '1'],
            ];
            $this->assertSame('summary_comments', $catalog->paragraphField('LBFecho'));
        }

        /**
         * Reject a non-textarea destination field.
         */
        public function testSaveParagraphFieldRejectsNonTextarea(): void
        {
            $catalog = new LayoutCatalog($this->sql);
            $this->sql->queue[] = [
                ['field_id' => 'num', 'data_type' => 2, 'title' => 'N', 'list_id' => '', 'seq' => 1, 'group_id' => '1'],
            ];
            $this->expectException(\InvalidArgumentException::class);
            $catalog->saveParagraphField('LBFecho', 'num');
        }

        /**
         * Persist a textarea as the paragraph field.
         */
        public function testSaveParagraphFieldWritesTextarea(): void
        {
            $catalog = new LayoutCatalog($this->sql);
            $this->sql->queue[] = [
                ['field_id' => 'notes', 'data_type' => 3, 'title' => 'N', 'list_id' => '', 'seq' => 1, 'group_id' => '1'],
            ];
            $catalog->saveParagraphField('LBFecho', 'notes');
            $ops = array_column($this->sql->calls, 'op');
            $this->assertSame('exec', $ops[1] ?? null);
        }

        /**
         * Insert stmt_paragraph when the layout has none.
         */
        public function testEnsureParagraphFieldInsertsWhenMissing(): void
        {
            $catalog = new LayoutCatalog($this->sql);
            $this->sql->queue[] = null;
            $this->sql->queue[] = ['grp_group_id' => '2'];
            $this->sql->queue[] = [
                ['field_id' => 'stmt_paragraph', 'data_type' => 3, 'title' => 'Generated statements', 'list_id' => '', 'seq' => 1, 'group_id' => '2'],
            ];
            $this->assertSame('stmt_paragraph', $catalog->ensureParagraphField('LBFecho'));
        }

        /**
         * Load a patient name and check encounter ownership.
         */
        public function testReaderPatientAndOwnership(): void
        {
            $reader = new LbfReader($this->sql);
            $this->assertNull($reader->patientName(0));
            $this->sql->queue[] = ['pid' => 1, 'fname' => 'Ted', 'lname' => 'Shaw'];
            $this->assertSame(['pid' => 1, 'name' => 'Shaw, Ted'], $reader->patientName(1));
            $this->sql->queue[] = ['encounter' => 9];
            $this->assertTrue($reader->encounterOwnedBy(1, 9));
            $this->assertFalse($reader->encounterOwnedBy(1, 9));
        }

        /**
         * Load encounter instances and lbf_data values.
         */
        public function testReaderInstancesAndValues(): void
        {
            $reader = new LbfReader($this->sql);
            $this->assertSame([], $reader->instancesOnEncounter(0, 1, ['LBFecho']));
            $this->assertSame([], $reader->formdirsForPatient(1, []));
            $this->sql->queue[] = [
                ['formdir' => 'LBFecho', 'form_id' => 12, 'form_name' => 'Echo'],
                [0 => 'x'],
            ];
            $onEnc = $reader->instancesOnEncounter(1, 5, ['LBFecho']);
            $this->assertSame(12, $onEnc[0]['instance_id']);

            $this->sql->queue[] = [
                ['formdir' => 'LBFecho'],
                ['formdir' => 'LBFecho'],
                ['formdir' => ''],
            ];
            $this->assertSame(['LBFecho'], $reader->formdirsForPatient(1, ['LBFecho']));

            $this->sql->queue[] = [
                ['form_id' => 3, 'encounter' => 8, 'date' => '2026-01-02', 'form_name' => 'Echo', 'encounter_date' => '', 'reason' => str_repeat('r', 90)],
            ];
            $list = $reader->instancesForPatient('LBFecho', 1);
            $this->assertSame(3, $list[0]['instance_id']);
            $this->assertSame(8, $list[0]['encounter']);
            $this->assertStringEndsWith('...', $list[0]['reason']);

            $this->sql->queue[] = ['id' => 1, 'pid' => 1, 'encounter' => 8, 'formdir' => 'LBFecho', 'form_id' => 3, 'date' => '2026-01-02'];
            $row = $reader->instanceRow(3, 'LBFecho');
            $this->assertNotNull($row);
            $this->assertSame(1, $row['pid']);

            $this->sql->queue[] = [
                ['field_id' => 'findings', 'field_value' => 'mild'],
                ['field_id' => '', 'field_value' => 'x'],
            ];
            $this->assertSame(['findings' => 'mild'], $reader->readValues(3));
        }

        /**
         * Insert, replace, and delete lbf_data rows.
         */
        public function testWriterInsertUpdateDelete(): void
        {
            $writer = new LbfWriter($this->sql);
            $writer->write(3, ['notes' => 'hi', 'gone' => ''], ['gone' => 'old'], [
                ['field_id' => ''],
                ['field_id' => 'notes'],
                ['field_id' => 'gone'],
            ]);
            $ops = array_column($this->sql->calls, 'op');
            $this->assertContains('exec', $ops);
            $writer->write(3, ['notes' => 'hi'], [], [['field_id' => 'notes']]);
            $last = $this->sql->calls[array_key_last($this->sql->calls)] ?? null;
            $this->assertIsArray($last);
            $this->assertSame('exec', $last['op']);
        }

        /**
         * List, load, insert, and update statement rules.
         */
        public function testRepositoryRulesAndSave(): void
        {
            $repo = new StatementRepository($this->sql);
            $this->sql->queue[] = [['form_id' => 'LBFecho'], [0 => 'x']];
            $this->assertSame(['LBFecho'], $repo->formIdsWithRules());

            $this->sql->queue[] = [['id' => 1, 'form_id' => 'LBFecho', 'source_field_id' => 'n', 'op' => 'band', 'enabled' => 1]];
            $this->assertCount(1, $repo->rulesForForm('LBFecho', true));

            $this->sql->queue[] = ['id' => 2, 'form_id' => 'LBFecho', 'op' => 'band'];
            $rule = $repo->getRule(2);
            $this->assertNotNull($rule);
            $this->assertSame(2, $rule['id']);

            $this->sql->queue[] = [];
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

            $this->sql->queue[] = [
                'id' => 7,
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'op' => 'parse_severity',
                'enabled' => 1,
            ];
            $this->assertSame(7, $repo->saveRule([
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'op' => 'parse_severity',
                'match_token' => 'mild',
                'statement_text' => 'Mild.',
                'enabled' => 1,
            ], 7));

            $this->sql->queue[] = ['id' => 7, 'form_id' => 'LBFecho', 'source_field_id' => 'n', 'op' => 'parse_severity', 'enabled' => 0];
            $repo->setEnabled(7, true);

            $repo->logRun('LBFecho', 1, 3, 'admin', 'overwrite');
            $last = $this->sql->calls[array_key_last($this->sql->calls)] ?? null;
            $this->assertIsArray($last);
            $this->assertSame('insert', $last['op']);
        }

        /**
         * Reject an enabled band that overlaps another.
         */
        public function testRepositoryRejectsOverlap(): void
        {
            $repo = new StatementRepository($this->sql);
            $this->sql->queue[] = [[
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
            $this->expectException(BandOverlapException::class);
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

        /**
         * Reject a band whose minimum is above its maximum.
         */
        public function testRepositoryRejectsInvertedBounds(): void
        {
            $repo = new StatementRepository($this->sql);
            $this->expectException(InvertedBoundsException::class);
            $repo->saveRule([
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'op' => 'band',
                'min_value' => 9,
                'max_value' => 1,
                'min_inclusive' => 1,
                'max_inclusive' => 1,
                'enabled' => 1,
                'statement_text' => 'X',
            ]);
        }

        /**
         * Wrap lbf_data writes in one transaction.
         */
        public function testWriterRunsInsideTransaction(): void
        {
            $writer = new LbfWriter($this->sql);
            $writer->write(3, ['notes' => 'hi', 'gone' => ''], ['gone' => 'old'], [
                ['field_id' => 'notes'],
                ['field_id' => 'gone'],
            ]);
            $this->assertSame(1, $this->sql->transactions);
            $ops = array_column($this->sql->calls, 'op');
            $this->assertSame('tx', $ops[0] ?? null);
            $this->assertContains('exec', $ops);
        }

        /**
         * Hold GET_LOCK across save and enable, and release after overlap.
         */
        public function testSaveAndEnableShareBandLockAndReleaseOnOverlap(): void
        {
            $repo = new StatementRepository($this->sql);
            $this->sql->queue[] = [];
            $repo->saveRule([
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'op' => 'band',
                'min_value' => 0,
                'max_value' => 1,
                'min_inclusive' => 1,
                'max_inclusive' => 1,
                'statement_text' => 'In range.',
                'enabled' => 1,
            ]);
            $expected = StatementRepository::bandLockName('LBFecho', 'n', '');
            $this->assertSame(1, $this->sql->transactions);
            $this->assertSame(
                [['op' => 'get', 'name' => $expected], ['op' => 'release', 'name' => $expected]],
                $this->sql->locks
            );

            $this->sql->locks = [];
            $this->sql->queue[] = [
                'id' => 9,
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'source_field_id_2' => '',
                'op' => 'band',
                'min_value' => 2,
                'max_value' => 3,
                'min_inclusive' => 1,
                'max_inclusive' => 1,
                'enabled' => 0,
            ];
            $this->sql->queue[] = [];
            $repo->setEnabled(9, true);
            $this->assertSame(
                [['op' => 'get', 'name' => $expected], ['op' => 'release', 'name' => $expected]],
                $this->sql->locks
            );

            $this->sql->locks = [];
            $this->sql->queue[] = [[
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
            try {
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
                $this->fail('expected overlap');
            } catch (BandOverlapException) {
                $this->assertSame(
                    [['op' => 'get', 'name' => $expected], ['op' => 'release', 'name' => $expected]],
                    $this->sql->locks
                );
            }
        }

        /**
         * Look up the destination field without writing layout_options.
         */
        public function testParagraphFieldDoesNotCreateLayoutField(): void
        {
            $catalog = new LayoutCatalog($this->sql);
            $this->sql->queue[] = null;
            $this->sql->queue[] = [
                ['field_id' => 'num', 'data_type' => 2, 'title' => 'N', 'list_id' => '', 'seq' => 1, 'group_id' => '1'],
            ];
            $this->assertSame('', $catalog->paragraphField('LBFecho'));
            $ops = array_column($this->sql->calls, 'op');
            $this->assertNotContains('exec', $ops);
            $this->assertNotContains('insert', $ops);
        }

        /**
         * Fail saveRule when the locked rule row is gone.
         */
        public function testSaveRuleRejectsMissingRow(): void
        {
            $repo = new StatementRepository($this->sql);
            $this->sql->queue[] = [];
            $this->expectException(RuleNotFoundException::class);
            $repo->saveRule([
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'op' => 'parse_severity',
                'match_token' => 'mild',
                'statement_text' => 'Mild.',
                'enabled' => 1,
            ], 7);
        }

        /**
         * Fail setEnabled when the locked rule row is gone.
         */
        public function testSetEnabledRejectsMissingRow(): void
        {
            $repo = new StatementRepository($this->sql);
            $this->sql->queue[] = [];
            $this->expectException(RuleNotFoundException::class);
            $repo->setEnabled(7, true);
        }

        /**
         * Fail save when GET_LOCK is not acquired.
         */
        public function testSaveRuleFailsWhenBandLockIsBusy(): void
        {
            $repo = new StatementRepository($this->sql);
            $this->sql->lockSucceeds = false;
            $this->expectException(BandLockException::class);
            $repo->saveRule([
                'form_id' => 'LBFecho',
                'source_field_id' => 'n',
                'op' => 'band',
                'min_value' => 0,
                'max_value' => 1,
                'enabled' => 1,
                'statement_text' => 'X',
            ]);
        }
    }
}
