<?php

/**
 * ReferralTaskService holds a lock on the referral across its check for a Task and the insert, so
 * two saves of the same referral at once make one Task, not two (#14710).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Services\ReferralService;
use OpenEMR\Services\ReferralTaskService;
use OpenEMR\Services\Search\SearchQueryConfig;
use OpenEMR\Services\TaskService;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;

/**
 * The lock is MySQL's GET_LOCK, which the database session holds. A second session can't be
 * opened here, so the test checks the mechanism from inside the insert: this session holds the
 * referral's lock while the Task is made, and nobody holds it afterwards.
 */
class ReferralTaskServiceLockTest extends TestCase
{
    private const REFERRAL_UUID = '9a0b6f2c-5d1e-4c3a-8b7f-0e1d2c3b4a59';
    private const LOCK = 'openemr.referral_task.' . self::REFERRAL_UUID;

    private mixed $globalBefore;

    protected function setUp(): void
    {
        $this->globalBefore = OEGlobalsBag::getInstance()->get(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value);
        OEGlobalsBag::getInstance()->set(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, '1');
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set(GlobalConnectorsEnum::FHIR_REFERRAL_TASKS->value, $this->globalBefore);
    }

    public function testTheTaskIsMadeWhileTheReferralIsLocked(): void
    {
        $tasks = $this->taskService(false);
        $service = $this->service($tasks);

        $this->assertSame('task-uuid', $service->createForReferral(0, 1));
        $this->assertTrue($tasks->heldDuringInsert, 'this session held the referral\'s lock while the Task was made');
        $this->assertSame([true], $this->referrals->reads, 'and read the referral under it, so it acts on the Refer To saved last');
        $this->assertTrue($this->lockIsFree(), 'and let it go after');
    }

    public function testAReferralThatHasATaskMakesNoneAndLetsTheLockGo(): void
    {
        $tasks = $this->taskService(true);
        $service = $this->service($tasks);

        $this->assertNull($service->createForReferral(0, 1));
        $this->assertNull($tasks->heldDuringInsert, 'no insert');
        $this->assertTrue($this->lockIsFree());
    }

    /** @var ReferralService&object{reads: list<bool>} */
    private ReferralService $referrals;

    private function service(TaskService $tasks): ReferralTaskService
    {
        $this->referrals = new class (false) extends ReferralService {
            /** @var list<bool> whether this session held the referral's lock at each read of the referral */
            public array $reads = [];

            public function getReferralUuid(int $transactionId): string
            {
                return '9a0b6f2c-5d1e-4c3a-8b7f-0e1d2c3b4a59';
            }

            /**
             * @return array<string, mixed>
             */
            public function getReferral(int $transactionId): array
            {
                $held = QueryUtils::fetchSingleValue('SELECT IS_USED_LOCK(?) = CONNECTION_ID() AS held', 'held', ['openemr.referral_task.9a0b6f2c-5d1e-4c3a-8b7f-0e1d2c3b4a59']);
                $this->reads[] = $held === 1 || $held === '1';
                return [
                    'uuid' => '9a0b6f2c-5d1e-4c3a-8b7f-0e1d2c3b4a59',
                    'patient_id' => 1,
                    'performer_uuid' => '5f4e3d2c-1b0a-4987-a6b5-c4d3e2f1a0b9',
                    'performer_is_practitioner' => 1,
                ];
            }
        };
        return new ReferralTaskService($this->referrals, $tasks);
    }

    /**
     * @return TaskService&object{heldDuringInsert: ?bool}
     */
    private function taskService(bool $hasTask): TaskService
    {
        return new class ($hasTask) extends TaskService {
            public ?bool $heldDuringInsert = null;

            public function __construct(private readonly bool $hasTask)
            {
                parent::__construct();
            }

            public function search(array $search, $isAndCondition = true, ?SearchQueryConfig $config = null): ProcessingResult
            {
                $result = new ProcessingResult();
                if ($this->hasTask) {
                    $result->addData(['uuid' => 'existing-task', 'code' => 'fulfill', 'part_of_uuid' => null, 'status' => 'requested',
                        'owner_uuid' => '5f4e3d2c-1b0a-4987-a6b5-c4d3e2f1a0b9', 'version' => 1]);
                }
                return $result;
            }

            public function insert(array $fields, int $patientId, ?int $userId, array $inputs = [], array $outputs = []): string
            {
                $held = QueryUtils::fetchSingleValue(
                    'SELECT IS_USED_LOCK(?) = CONNECTION_ID() AS held',
                    'held',
                    ['openemr.referral_task.9a0b6f2c-5d1e-4c3a-8b7f-0e1d2c3b4a59']
                );
                $this->heldDuringInsert = $held === 1 || $held === '1';
                return 'task-uuid';
            }
        };
    }

    private function lockIsFree(): bool
    {
        $free = QueryUtils::fetchSingleValue('SELECT IS_FREE_LOCK(?) AS free', 'free', [self::LOCK]);
        return $free === 1 || $free === '1';
    }
}
