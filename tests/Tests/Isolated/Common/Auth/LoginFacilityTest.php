<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth;

use OpenEMR\Common\Auth\LoginFacility;
use OpenEMR\Services\FacilityService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class LoginFacilityTest extends TestCase
{
    public function testAChosenFacilityIsKept(): void
    {
        $facilityService = $this->createMock(FacilityService::class);
        $facilityService->expects($this->never())->method('getFacilityForUser');

        $this->assertSame('3', (new LoginFacility($facilityService))->chosen('3', 7));
    }

    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function userDefaultProvider(): array
    {
        return [
            'integer id' => [['id' => 5, 'name' => 'Main'], '5'],
            'string id' => [['id' => '6', 'name' => 'Branch'], '6'],
            'no facility on the user' => [null, ''],
            'record without an id' => [['name' => 'Main'], ''],
        ];
    }

    #[DataProvider('userDefaultProvider')]
    public function testUserDefaultUsesTheUsersOwnFacility(mixed $record, string $expected): void
    {
        $facilityService = $this->createMock(FacilityService::class);
        $facilityService->expects($this->once())
            ->method('getFacilityForUser')
            ->with(7)
            ->willReturn($record);

        $this->assertSame($expected, (new LoginFacility($facilityService))->chosen(LoginFacility::USER_DEFAULT, 7));
    }
}
