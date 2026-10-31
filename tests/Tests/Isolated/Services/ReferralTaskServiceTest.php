<?php

/**
 * The FHIR Task for a referral the Transactions screen saved: ReferralTaskService (#14710).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Services\ReferralTaskService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReferralTaskServiceTest extends TestCase
{
    /**
     * @return array<string, array{mixed, mixed, mixed, ?array{int, ?int}}>
     */
    public static function saves(): array
    {
        // form, transaction id, user id => createForReferral()'s arguments, or null for no call
        return [
            'a referral' => ['LBTref', 12, '3', [12, 3]],
            'a referral, its id as a string' => ['LBTref', '12', 3, [12, 3]],
            'no user' => ['LBTref', 12, null, [12, null]],
            'a user id that is not a number' => ['LBTref', 12, 'admin', [12, null]],
            'another transaction type' => ['LBTptreq', 12, 3, null],
            'no transaction id' => ['LBTref', '', 3, null],
            'a null transaction id' => ['LBTref', null, 3, null],
        ];
    }

    /**
     * @param ?array{int, ?int} $expected
     */
    #[DataProvider('saves')]
    public function testOnlyASavedReferralGetsATask(mixed $formId, mixed $transactionId, mixed $userId, ?array $expected): void
    {
        $service = new class extends ReferralTaskService {
            /** @var list<array{int, ?int}> */
            public array $calls = [];

            public function createForReferral(int $transactionId, ?int $userId): string
            {
                $this->calls[] = [$transactionId, $userId];
                return 'task-uuid';
            }
        };

        $uuid = $service->createForSavedTransaction($formId, $transactionId, $userId);

        $this->assertSame($expected === null ? [] : [$expected], $service->calls);
        $this->assertSame($expected === null ? null : 'task-uuid', $uuid);
    }

    /**
     * The referral is saved before its Task is made, so a Task that can't be made is logged and
     * the save still succeeds, from the screen and from the REST API alike.
     */
    public function testATaskThatFailsDoesNotFailTheSavedReferral(): void
    {
        $service = new class extends ReferralTaskService {
            public function createForReferral(int $transactionId, ?int $userId): string
            {
                throw new SqlQueryException('', 'the database went away');
            }
        };

        $this->assertNull($service->createAfterSave(12, 3), 'the REST API\'s path');
        $this->assertNull($service->createForSavedTransaction('LBTref', '12', '3'), 'the screen\'s path');
    }

    /**
     * With audit logging on, each query's audit row is written through DBAL, which throws its own
     * exceptions; one of those, after the save, is contained too.
     */
    public function testAnAuditLogFailureDoesNotFailTheSavedReferral(): void
    {
        $service = new class extends ReferralTaskService {
            public function createForReferral(int $transactionId, ?int $userId): string
            {
                throw new class ('the audit log could not be written') extends \RuntimeException implements DbalException {
                };
            }
        };

        $this->assertNull($service->createAfterSave(12, 3));
    }
}
