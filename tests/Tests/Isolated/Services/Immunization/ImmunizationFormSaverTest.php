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

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Immunization\ImmunizationFormSaver;
use PHPUnit\Framework\TestCase;

final class ImmunizationFormSaverTest extends TestCase
{
    private const PID = 7;
    private const USER = 3;
    private const UUID = '9f6d8b5e-2c1a-4e0b-8d3f-6a7b8c9d0e1f';

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
            'reason_code' => '',
            'reason_description' => '',
            'ordered_by_id' => '5',
            'encounter_id' => '41',
        ];
    }

    /**
     * The form's binds, in the order of the shared column list.
     *
     * @return list<string>
     */
    private static function formBinds(): array
    {
        return [
            '2026-10-20 09:30', '2026-10-20 09:30',
            '12', '115', 'Sanofi', 'AB123',
            '5', '5', '', '',
            '2026-10-20', '2026-10-20', '2021-08-06', '2021-08-06',
            'left deltoid', '0.5', 'mL',
            '2027-01-31', '2027-01-31',
            'intramuscular', 'left_arm', 'Completed', 'new_immunization_record', '',
            '', '', '5', '41',
        ];
    }

    /** A saver whose database records what it is asked, and owns immunization 25 for patient 7. */
    private function saver(): ImmunizationFormSaver
    {
        return new ImmunizationFormSaver(
            belongsToPatient: function (int $immunizationId, int $pid): bool {
                $this->ownershipAsked[] = [$immunizationId, $pid];
                return $immunizationId === 25 && $pid === self::PID;
            },
            update: function (string $sql, array $binds): void {
                $this->updates[] = [$sql, $binds];
            },
            insert: function (string $sql, array $binds): int {
                $this->inserts[] = [$sql, $binds];
                return 99;
            },
        );
    }

    public function testANewImmunizationIsInsertedWithItsUuidAndCreator(): void
    {
        $this->assertSame(99, $this->saver()->save(self::form(''), self::PID, self::USER));

        $this->assertSame([], $this->ownershipAsked, 'nothing to own yet');
        $this->assertSame([], $this->updates);
        $this->assertCount(1, $this->inserts);
        [$sql, $binds] = $this->inserts[0];
        $this->assertStringStartsWith('INSERT INTO immunizations SET uuid = ?, administered_date = if(?,?,NULL),', $sql);
        $this->assertStringContainsString('encounter_id = ?,', $sql);
        $this->assertStringEndsWith('create_date = now()', $sql);
        $this->assertCount(substr_count($sql, '?'), $binds, 'one bind per placeholder');
        $this->assertSame(
            [UuidRegistry::uuidToBytes(self::UUID), ...self::formBinds(), self::PID, self::USER, self::USER],
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
        $this->assertStringStartsWith('UPDATE immunizations SET administered_date = if(?,?,NULL),', $sql);
        $this->assertStringEndsWith('encounter_id = ?, updated_by = ? WHERE id = ? AND patient_id = ?', $sql);
        $this->assertStringNotContainsString('uuid', $sql, 'the uuid identifies it outside OpenEMR, so it stays');
        $this->assertStringNotContainsString('created_by', $sql);
        $this->assertCount(substr_count($sql, '?'), $binds, 'one bind per placeholder');
        $this->assertSame([...self::formBinds(), self::USER, 25, self::PID], $binds);
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
        $this->assertSame(['', ''], array_slice($binds, 1, 2), 'administered_date, missing');
        $this->assertSame('115', $binds[4], 'cvx_code, posted as a number');
        $this->assertSame('', $binds[15], 'note, posted as a list');
    }
}
