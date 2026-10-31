<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Immunization;

use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Immunization\ImmunizationFormSaver;
use PHPUnit\Framework\TestCase;

final class ImmunizationFormSaverTest extends TestCase
{
    private const PID = 7;
    private const USER = 3;
    private const UUID = '9f6d8b5e-2c1a-4e0b-8d3f-6a7b8c9d0e1f';
    private const NOW = '2026-10-31 14:05:00';

    /** @var list<array{int, int}> */
    private array $ownershipAsked = [];

    /** @var list<array{string, list<mixed>}> */
    private array $updates = [];

    /** @var list<array{string, list<mixed>}> */
    private array $inserts = [];

    /**
     * The form as the page posts it, every field filled.
     *
     * @return array<string, string>
     */
    private static function form(string $id): array
    {
        return [
            'id' => $id,
            'uuid' => self::UUID,
            'administered_date' => ' 2026-10-20 09:30 ',
            'form_immunization_id' => '12',
            'cvx_code' => '115',
            'manufacturer' => 'Sanofi',
            'lot_number' => 'AB123',
            'administered_by_id' => '5',
            'administered_by' => '',
            'education_date' => '2026-10-20',
            'vis_date' => '2021-08-06',
            'note' => 'left deltoid',
            'immuniz_amt_adminstrd' => '0.5',
            'form_drug_units' => 'mL',
            'immuniz_exp_date' => '2027-01-31',
            'immuniz_route' => 'intramuscular',
            'immuniz_admin_ste' => 'left_arm',
            'immuniz_completion_status' => 'Completed',
            'immunization_informationsource' => 'new_immunization_record',
            'immunization_refusal_reason' => '',
            'reason_code' => 'SNOMED-CT:429060002',
            'reason_code_text' => 'Procedure to meet occupational requirement',
            'ordered_by_id' => '5',
            'encounter_id' => '41',
        ];
    }

    /**
     * The form's binds, in the order of the shared column list, after the
     * custom list's immunization_id and the CVX code.
     *
     * @return list<string>
     */
    private static function formBinds(): array
    {
        return [
            '12', '115',
            '2026-10-20 09:30', '2026-10-20 09:30',
            'Sanofi', 'AB123',
            '5', '5', '', '',
            '2026-10-20', '2026-10-20', '2021-08-06', '2021-08-06',
            'left deltoid', '0.5', 'mL',
            '2027-01-31', '2027-01-31',
            'intramuscular', 'left_arm', 'Completed', 'new_immunization_record', '',
            'SNOMED-CT:429060002', 'Procedure to meet occupational requirement', '5', '41',
        ];
    }

    /**
     * A saver whose database records what it is asked, and owns immunization 25 for patient 7, stored with
     * the custom list's immunization 12 and CVX code 115.
     */
    private function saver(): ImmunizationFormSaver
    {
        return new ImmunizationFormSaver(
            storedRow: function (int $immunizationId, int $pid): ?array {
                $this->ownershipAsked[] = [$immunizationId, $pid];
                return $immunizationId === 25 && $pid === self::PID ? ['immunization_id' => 12, 'cvx_code' => '115'] : null;
            },
            update: function (string $sql, array $binds): void {
                $this->updates[] = [$sql, $binds];
            },
            insert: function (string $sql, array $binds): int {
                $this->inserts[] = [$sql, $binds];
                return 99;
            },
            clock: new FrozenClock(new DateTimeImmutable(self::NOW)),
        );
    }

    public function testANewImmunizationIsInsertedWithItsUuidAndCreator(): void
    {
        $this->assertSame(99, $this->saver()->save(self::form(''), self::PID, self::USER));

        $this->assertSame([], $this->ownershipAsked, 'nothing to own yet');
        $this->assertSame([], $this->updates);
        $this->assertCount(1, $this->inserts);
        [$sql, $binds] = $this->inserts[0];
        $this->assertStringStartsWith('INSERT INTO immunizations SET uuid = ?, immunization_id = ?, cvx_code = ?, administered_date = if(?,?,NULL),', $sql);
        $this->assertStringContainsString('encounter_id = ?,', $sql);
        $this->assertStringEndsWith('create_date = ?', $sql);
        $this->assertCount(substr_count($sql, '?'), $binds, 'one bind per placeholder');
        $this->assertSame(
            [UuidRegistry::uuidToBytes(self::UUID), ...self::formBinds(), self::PID, self::USER, self::USER, self::NOW],
            $binds
        );
    }

    public function testANewImmunizationWithoutAValidUuidGetsNone(): void
    {
        $form = self::form('');
        $form['uuid'] = 'not-a-uuid';
        $this->saver()->save($form, self::PID, self::USER);

        $this->assertNull($this->inserts[0][1][0]);
    }

    public function testAnEditUpdatesThePatientsOwnRowInPlace(): void
    {
        $this->assertSame(25, $this->saver()->save(self::form(' 25 '), self::PID, self::USER));

        $this->assertSame([[25, self::PID]], $this->ownershipAsked);
        $this->assertSame([], $this->inserts, 'an edit never inserts');
        $this->assertCount(1, $this->updates);
        [$sql, $binds] = $this->updates[0];
        // The immunization and CVX code are as stored, so neither is written.
        $this->assertStringStartsWith('UPDATE immunizations SET administered_date = if(?,?,NULL),', $sql);
        $this->assertStringEndsWith('encounter_id = ?, updated_by = ? WHERE id = ? AND patient_id = ?', $sql);
        $this->assertStringNotContainsString('uuid', $sql, 'the uuid identifies it outside OpenEMR, so it stays');
        $this->assertStringNotContainsString('created_by', $sql);
        $this->assertCount(substr_count($sql, '?'), $binds, 'one bind per placeholder');
        $this->assertSame([...array_slice(self::formBinds(), 2), self::USER, 25, self::PID], $binds);
    }

    /**
     * The page shows the custom list or the CVX code, not both, so the one it
     * doesn't show isn't posted. An unchanged edit leaves both as stored.
     */
    public function testAnEditLeavesTheImmunizationFieldThePageDidNotShow(): void
    {
        $customList = self::form('25');
        unset($customList['cvx_code']);
        $this->saver()->save($customList, self::PID, self::USER);
        $cvx = self::form('25');
        unset($cvx['form_immunization_id']);
        $this->saver()->save($cvx, self::PID, self::USER);

        foreach ($this->updates as [$sql, $binds]) {
            $this->assertStringStartsWith('UPDATE immunizations SET administered_date = if(?,?,NULL),', $sql);
            $this->assertStringNotContainsString('cvx_code', $sql);
            $this->assertStringNotContainsString('immunization_id', $sql);
            $this->assertCount(substr_count($sql, '?'), $binds, 'one bind per placeholder');
        }
        $this->assertCount(2, $this->updates);
    }

    /**
     * Once the field the page showed changes, the other no longer describes the shot: it's emptied, as master
     * did and as the page's own script does, rather than kept naming the old vaccine.
     */
    public function testChangingTheImmunizationFieldShownEmptiesTheOther(): void
    {
        $customList = self::form('25');
        unset($customList['cvx_code']);
        $customList['form_immunization_id'] = '13';
        $this->saver()->save($customList, self::PID, self::USER);
        $cvx = self::form('25');
        unset($cvx['form_immunization_id']);
        $cvx['cvx_code'] = ' 141 ';
        $this->saver()->save($cvx, self::PID, self::USER);

        [$sql, $binds] = $this->updates[0];
        $this->assertStringStartsWith('UPDATE immunizations SET immunization_id = ?, cvx_code = ?, administered_date = if(?,?,NULL),', $sql);
        $this->assertSame(['13', ''], array_slice($binds, 0, 2));
        $this->assertCount(substr_count($sql, '?'), $binds, 'one bind per placeholder');
        [$sql, $binds] = $this->updates[1];
        $this->assertStringStartsWith('UPDATE immunizations SET immunization_id = ?, cvx_code = ?, administered_date = if(?,?,NULL),', $sql);
        $this->assertSame(['', '141'], array_slice($binds, 0, 2));
    }

    /**
     * A stored immunization_id of 0 or NULL is none, and a posted blank is none too: no change.
     */
    public function testNoImmunizationStoredAndNonePostedIsNoChange(): void
    {
        $saver = new ImmunizationFormSaver(
            storedRow: fn(int $immunizationId, int $pid): array => ['immunization_id' => 0, 'cvx_code' => '115'],
            update: function (string $sql, array $binds): void {
                $this->updates[] = [$sql, $binds];
            },
        );
        $form = self::form('25');
        unset($form['cvx_code']);
        $form['form_immunization_id'] = '';
        $saver->save($form, self::PID, self::USER);

        [$sql] = $this->updates[0];
        $this->assertStringStartsWith('UPDATE immunizations SET administered_date', $sql);
        $this->assertStringNotContainsString('cvx_code', $sql, 'a stored 0 and a posted blank are both none');
    }

    /**
     * Two windows open on one shot: one picks a custom-list entry (stored as 13, the CVX code emptied), then
     * the other saves what it showed, an unassigned entry. Judged against what that page showed, its save
     * changes no code, so it neither undoes the entry nor leaves the shot with no code at all.
     */
    public function testAWindowOpenedBeforeAnotherChangedTheCodeLeavesItAlone(): void
    {
        $saver = new ImmunizationFormSaver(
            storedRow: fn(int $immunizationId, int $pid): array => ['immunization_id' => 13, 'cvx_code' => ''],
            update: function (string $sql, array $binds): void {
                $this->updates[] = [$sql, $binds];
            },
        );
        $form = self::form('25');
        unset($form['cvx_code']);
        $form['form_immunization_id'] = '';
        $form['shown_code_original'] = '';
        $saver->save($form, self::PID, self::USER);

        [$sql] = $this->updates[0];
        $this->assertStringStartsWith('UPDATE immunizations SET administered_date', $sql);
        $this->assertStringNotContainsString('cvx_code', $sql);
    }

    public function testAChangeFromWhatThePageShowedIsWritten(): void
    {
        $saver = new ImmunizationFormSaver(
            storedRow: fn(int $immunizationId, int $pid): array => ['immunization_id' => 0, 'cvx_code' => '141'],
            update: function (string $sql, array $binds): void {
                $this->updates[] = [$sql, $binds];
            },
        );
        $form = self::form('25');
        unset($form['cvx_code']);
        $form['form_immunization_id'] = '13';
        $form['shown_code_original'] = '';
        $saver->save($form, self::PID, self::USER);

        [$sql, $binds] = $this->updates[0];
        $this->assertStringStartsWith('UPDATE immunizations SET immunization_id = ?, cvx_code = ?,', $sql);
        $this->assertSame(['13', ''], array_slice($binds, 0, 2));
    }

    /**
     * Picking the blank choice empties the custom list's entry and keeps the CVX code, as the page's own
     * script clears the CVX code only when an immunization is picked.
     */
    public function testChangingTheShownFieldToNoneEmptiesItAlone(): void
    {
        $form = self::form('25');
        unset($form['cvx_code']);
        $form['form_immunization_id'] = '';
        $form['shown_code_original'] = '12';
        $this->saver()->save($form, self::PID, self::USER);

        [$sql, $binds] = $this->updates[0];
        $this->assertStringStartsWith('UPDATE immunizations SET immunization_id = ?, administered_date', $sql);
        $this->assertStringNotContainsString('cvx_code', $sql);
        $this->assertSame('', $binds[0]);
    }

    public function testANewImmunizationSavesBothImmunizationFieldsEvenIfOneIsNotPosted(): void
    {
        $form = self::form('');
        unset($form['cvx_code']);
        $this->saver()->save($form, self::PID, self::USER);

        [$sql, $binds] = $this->inserts[0];
        $this->assertStringStartsWith('INSERT INTO immunizations SET uuid = ?, immunization_id = ?, cvx_code = ?,', $sql);
        $this->assertSame(['12', ''], array_slice($binds, 1, 2));
    }

    public function testAnotherPatientsImmunizationIsRefusedAndNothingIsWritten(): void
    {
        $this->assertNull($this->saver()->save(self::form('26'), self::PID, self::USER));
        $this->assertNull($this->saver()->save(self::form('25'), 8, self::USER), "patient 7's immunization, saved for patient 8");

        $this->assertSame([[26, self::PID], [25, 8]], $this->ownershipAsked);
        $this->assertSame([], $this->updates);
        $this->assertSame([], $this->inserts);
    }

    public function testAMalformedIdIsRefusedAndNothingIsWritten(): void
    {
        foreach (['abc', '25abc', '-5', '1e3', ['25']] as $id) {
            $this->assertNull($this->saver()->save(['id' => $id] + self::form(''), self::PID, self::USER), var_export($id, true));
        }

        $this->assertSame([], $this->ownershipAsked);
        $this->assertSame([], $this->updates);
        $this->assertSame([], $this->inserts);
    }

    public function testMissingOrMalformedFieldsAreSavedEmpty(): void
    {
        $this->saver()->save(['id' => '', 'note' => ['x'], 'cvx_code' => 115], self::PID, self::USER);

        $this->assertSame([], $this->ownershipAsked, 'a blank id is a new immunization');
        [, $binds] = $this->inserts[0];
        $this->assertNull($binds[0], 'no uuid');
        $this->assertSame(['', '115'], array_slice($binds, 1, 2), 'immunization_id missing, cvx_code posted as a number');
        $this->assertSame(['', ''], array_slice($binds, 3, 2), 'administered_date, missing');
        $this->assertSame('', $binds[15], 'note, posted as a list');
    }
}
