<?php

/**
 * Isolated tests for DuplicateAclMerger
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Acl;

use OpenEMR\Common\Acl\DuplicateAclMerger;
use OpenEMR\Gacl\GaclApi;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DuplicateAclMergerTest extends TestCase
{
    private const MED = ['patients' => ['med'], 'camos' => ['administer']];
    private const APPT = ['patients' => ['appt'], 'admin' => ['drugs']];

    public function testMergesDuplicateIntoLowestId(): void
    {
        $gacl = $this->gaclWith(['17', '12'], [12 => self::acl(12, self::APPT), 17 => self::acl(17, self::MED)]);
        $gacl->expects($this->once())->method('append_acl')
            ->with(12, null, null, null, null, self::MED)
            ->willReturn(true);
        $gacl->expects($this->once())->method('del_acl')->with(17)->willReturn(true);

        $this->assertSame([17], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    public function testSearchesByGroupAndReturnValue(): void
    {
        $gacl = $this->createMock(GaclApi::class);
        $gacl->expects($this->once())->method('search_acl')
            ->with(false, false, false, false, 'Clinicians', false, false, false, 'write')
            ->willReturn(['12']);
        $gacl->expects($this->never())->method('append_acl');
        $gacl->expects($this->never())->method('del_acl');

        $this->assertSame([], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    public function testNoAclsFound(): void
    {
        $gacl = $this->gaclWith([], []);
        $gacl->expects($this->never())->method('get_acl');

        $this->assertSame([], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    public function testSearchFailure(): void
    {
        $gacl = $this->createMock(GaclApi::class);
        $gacl->method('search_acl')->willReturn(false);
        $gacl->expects($this->never())->method('get_acl');

        $this->assertSame([], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    public function testMissingKeeperLeavesDuplicates(): void
    {
        $gacl = $this->gaclWith(['12', '17'], [17 => self::acl(17, self::MED)]);
        $gacl->expects($this->never())->method('del_acl');

        $this->assertSame([], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    public function testSkipsDuplicateThatAlsoGrantsAnotherGroup(): void
    {
        $wider = self::acl(17, self::MED);
        $wider['aro_groups'] = ['3', '5'];
        $gacl = $this->gaclWith(['12', '17'], [12 => self::acl(12, self::APPT), 17 => $wider]);
        $gacl->expects($this->never())->method('append_acl');
        $gacl->expects($this->never())->method('del_acl');

        $this->assertSame([], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    public function testSkipsDisabledDuplicate(): void
    {
        $disabled = self::acl(17, self::MED);
        $disabled['enabled'] = '0';
        $gacl = $this->gaclWith(['12', '17'], [12 => self::acl(12, self::APPT), 17 => $disabled]);
        $gacl->expects($this->never())->method('del_acl');

        $this->assertSame([], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    public function testKeepsDuplicateWhenAppendFails(): void
    {
        $gacl = $this->gaclWith(['12', '17'], [12 => self::acl(12, self::APPT), 17 => self::acl(17, self::MED)]);
        $gacl->method('append_acl')->willReturn(false);
        $gacl->expects($this->never())->method('del_acl');

        $this->assertSame([], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    public function testOmitsDuplicateWhenDeleteFails(): void
    {
        $gacl = $this->gaclWith(['12', '17'], [12 => self::acl(12, self::APPT), 17 => self::acl(17, self::MED)]);
        $gacl->method('append_acl')->willReturn(true);
        $gacl->method('del_acl')->willReturn(false);

        $this->assertSame([], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    public function testDeletesEmptyDuplicateWithoutAppending(): void
    {
        $gacl = $this->gaclWith(['12', '17'], [12 => self::acl(12, self::APPT), 17 => self::acl(17, [])]);
        $gacl->expects($this->never())->method('append_acl');
        $gacl->expects($this->once())->method('del_acl')->with(17)->willReturn(true);

        $this->assertSame([17], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    public function testMergesEveryDuplicate(): void
    {
        $gacl = $this->gaclWith(['12', '17', '30'], [
            12 => self::acl(12, self::APPT),
            17 => self::acl(17, self::MED),
            30 => self::acl(30, ['groups' => ['glog']]),
        ]);
        $gacl->expects($this->exactly(2))->method('append_acl')->willReturn(true);
        $gacl->method('del_acl')->willReturn(true);

        $this->assertSame([17, 30], (new DuplicateAclMerger($gacl))->merge('Clinicians', 'write'));
    }

    /**
     * @param list<string> $found search_acl() result
     * @param array<int, array<string, mixed>> $acls get_acl() results by ID
     */
    private function gaclWith(array $found, array $acls): GaclApi&MockObject
    {
        $gacl = $this->createMock(GaclApi::class);
        $gacl->method('search_acl')->willReturn($found);
        $gacl->method('get_acl')->willReturnCallback(static fn(int $id): array|false => $acls[$id] ?? false);
        return $gacl;
    }

    /**
     * @param array<string, list<string>> $aco
     * @return array<string, mixed> shaped like a get_acl() result
     */
    private static function acl(int $id, array $aco): array
    {
        return [
            'acl_id' => (string) $id,
            'allow' => '1',
            'enabled' => '1',
            'return_value' => 'write',
            'note' => 'Things that clinicians can read and modify',
            'aco' => $aco,
            'aro' => [],
            'axo' => [],
            'aro_groups' => ['3'],
            'axo_groups' => [],
        ];
    }
}
