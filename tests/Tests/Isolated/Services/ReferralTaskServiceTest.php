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
use OpenEMR\Services\ReferralFormLoaded;
use OpenEMR\Services\ReferralTaskService;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\HttpFoundation\Request;

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
        $service = new class (new UserService()) extends ReferralTaskService {
            /** @var list<array{int, ?int}> */
            public array $calls = [];

            public function createForReferral(int $transactionId, ?int $userId, ?ReferralFormLoaded $loaded = null): string
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
     * What the screen's form showed reaches createForReferral() as the screen read it, and a screen
     * that read nothing (null) passes nothing.
     */
    public function testWhatTheFormShowedIsPassedOn(): void
    {
        $service = new class (new UserService()) extends ReferralTaskService {
            /** @var list<?ReferralFormLoaded> */
            public array $loaded = [];

            public function createForReferral(int $transactionId, ?int $userId, ?ReferralFormLoaded $loaded = null): string
            {
                $this->loaded[] = $loaded;
                return 'task-uuid';
            }
        };
        $shown = new ReferralFormLoaded(true, 41);

        $service->createForSavedTransaction('LBTref', 12, 3, $shown);
        $service->createForSavedTransaction('LBTref', 12, 3);

        $this->assertSame([$shown, null], $service->loaded);
    }

    /**
     * @return array<string, array{array<string, ?string>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function putsToAReferralsTask(): array
    {
        // what the PUT changes => the elements it's refused for
        return [
            'nothing it keeps' => [['status' => 'accepted', 'description' => 'Seen'], []],
            'the same uuids in capitals' => [['focus_uuid' => 'AAAAAAAA-0000-4000-8000-000000000001', 'owner_uuid' => 'BBBBBBBB-0000-4000-8000-000000000002'], []],
            'another owner' => [['owner_uuid' => 'cccccccc-0000-4000-8000-000000000003'], ['owner']],
            'an owner of another type' => [['owner_type' => 'PractitionerRole'], ['owner']],
            'the owner dropped' => [['owner_type' => null, 'owner_uuid' => null], ['owner']],
            'another code' => [['code' => 'approve'], ['code']],
            'the code dropped' => [['code' => null], ['code']],
            'another focus' => [['focus_uuid' => 'cccccccc-0000-4000-8000-000000000003'], ['focus']],
            'the focus dropped' => [['focus_type' => null, 'focus_uuid' => null], ['focus']],
            'a parent' => [['part_of_uuid' => 'cccccccc-0000-4000-8000-000000000003'], ['partOf']],
            'everything' => [['code' => 'approve', 'focus_uuid' => 'cccccccc-0000-4000-8000-000000000003', 'owner_uuid' => 'cccccccc-0000-4000-8000-000000000003', 'part_of_uuid' => 'cccccccc-0000-4000-8000-000000000003'], ['code', 'focus', 'owner', 'partOf']],
        ];
    }

    /**
     * @param array<string, ?string> $changes
     * @param list<string> $refused
     */
    #[DataProvider('putsToAReferralsTask')]
    public function testAReferralsTaskKeepsWhatMakesItTheReferrals(array $changes, array $refused): void
    {
        $stored = [
            'code' => 'fulfill',
            'focus_type' => 'ServiceRequest',
            'focus_uuid' => 'aaaaaaaa-0000-4000-8000-000000000001',
            'owner_type' => 'Organization',
            'owner_uuid' => 'bbbbbbbb-0000-4000-8000-000000000002',
            'part_of_uuid' => null,
        ];

        $this->assertSame($refused, array_keys(ReferralTaskService::lockedChanges($stored, $changes + $stored)));
    }

    public function testAnOwnerTheTaskNeverHadCanBeSet(): void
    {
        $stored = ['code' => 'fulfill', 'focus_type' => 'ServiceRequest', 'focus_uuid' => 'aaaaaaaa-0000-4000-8000-000000000001', 'owner_type' => null, 'owner_uuid' => null];

        $this->assertSame([], ReferralTaskService::lockedChanges($stored, ['owner_type' => 'Organization', 'owner_uuid' => 'bbbbbbbb-0000-4000-8000-000000000002'] + $stored));
    }

    /**
     * Only a Task of the referral's own kind is looked at, so a sub-task or a questionnaire's costs
     * no lookup and keeps its freedom, whatever the PUT changes.
     *
     * @return array<string, array{array<string, ?string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function otherKindsOfTask(): array
    {
        return [
            'a sub-task' => [['code' => 'fulfill', 'part_of_uuid' => 'cccccccc-0000-4000-8000-000000000003', 'focus_type' => 'ServiceRequest', 'focus_uuid' => 'aaaaaaaa-0000-4000-8000-000000000001']],
            'a questionnaire assignment' => [['code' => 'complete-questionnaire', 'focus_type' => 'Questionnaire', 'focus_uuid' => 'aaaaaaaa-0000-4000-8000-000000000001']],
            'fulfilling something that is not a ServiceRequest' => [['code' => 'fulfill', 'focus_type' => 'Questionnaire', 'focus_uuid' => 'aaaaaaaa-0000-4000-8000-000000000001']],
        ];
    }

    /**
     * @param array<string, ?string> $stored
     */
    #[DataProvider('otherKindsOfTask')]
    public function testOtherKindsOfTaskAreNotLocked(array $stored): void
    {
        $this->assertSame([], (new ReferralTaskService(new UserService()))->writeProblems($stored, ['code' => 'approve', 'owner_uuid' => 'cccccccc-0000-4000-8000-000000000003']));
    }

    /**
     * @return array<string, array{array<string, string>, ?array{bool, ?int}}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function postedForms(): array
    {
        // the referral screen's POST => whether it showed a Reply Date, and the Refer To it showed, as fromRequest() parses them
        return [
            'the Reply Date cleared' => [['referral_reply_date_loaded' => '2026-10-18', 'referral_refer_to_loaded' => '41', 'form_reply_date' => ''], [true, 41]],
            'a Reply Date entered' => [['referral_reply_date_loaded' => '', 'referral_refer_to_loaded' => '41', 'form_reply_date' => '2026-10-18'], [false, 41]],
            'the Reply Date read-only, so not posted' => [['referral_reply_date_loaded' => '2026-10-18', 'referral_refer_to_loaded' => '41'], null],
            'nothing shown posted' => [['form_reply_date' => ''], [false, null]],
            'a Refer To padded with spaces' => [['referral_reply_date_loaded' => '2026-10-18', 'referral_refer_to_loaded' => ' 41 ', 'form_reply_date' => ''], [true, 41]],
            'a Refer To that is not an id' => [['referral_reply_date_loaded' => '2026-10-18', 'referral_refer_to_loaded' => '41x', 'form_reply_date' => ''], [true, null]],
            'a blank Reply Date' => [['referral_reply_date_loaded' => '  ', 'referral_refer_to_loaded' => '41', 'form_reply_date' => ''], [false, 41]],
        ];
    }

    /**
     * What the form showed counts only when the form posted the Reply Date's own field: a read-only
     * one is disabled, so it isn't posted, and its save mustn't undo a reply.
     *
     * @param array<string, string> $posted
     * @param ?array{bool, ?int} $expected
     */
    #[DataProvider('postedForms')]
    public function testWhatTheFormShowedCountsOnlyWhenTheReplyDateWasPosted(array $posted, ?array $expected): void
    {
        $loaded = ReferralFormLoaded::fromRequest(new Request([], $posted));

        $this->assertSame($expected, $loaded === null ? null : [$loaded->showedReply, $loaded->referTo]);
    }

    /**
     * The referral is saved before its Task is made, so a Task that can't be made is logged and
     * the save still succeeds, from the screen and from the REST API alike.
     */
    public function testATaskThatFailsDoesNotFailTheSavedReferral(): void
    {
        $logger = self::logger();
        $service = new class (new UserService(), logger: $logger) extends ReferralTaskService {
            public function createForReferral(int $transactionId, ?int $userId, ?ReferralFormLoaded $loaded = null): string
            {
                throw new SqlQueryException('', 'the database went away');
            }
        };

        $this->assertNull($service->afterSave(12, 3), 'the REST API\'s path');
        $this->assertNull($service->createForSavedTransaction('LBTref', '12', '3'), 'the screen\'s path');
        $this->assertSame(
            [['error', 'A referral was saved but its FHIR Task was not updated', 12], ['error', 'A referral was saved but its FHIR Task was not updated', 12]],
            $logger->records,
            'each failure is logged, with the referral'
        );
    }

    /**
     * A reply that can't complete the Task, after the Task was made, is contained the same way.
     */
    public function testAReplyThatFailsDoesNotFailTheSavedReferral(): void
    {
        $service = new class (new UserService()) extends ReferralTaskService {
            public function createForReferral(int $transactionId, ?int $userId, ?ReferralFormLoaded $loaded = null): string
            {
                return 'task-uuid';
            }

            public function recordReply(int $transactionId, ?int $userId): string
            {
                throw new SqlQueryException('', 'the Task could not be updated');
            }
        };

        $this->assertNull($service->afterSave(12, 3));
    }

    /**
     * With audit logging on, each query's audit row is written through DBAL, which throws its own
     * exceptions; one of those, after the save, is contained too.
     */
    public function testAnAuditLogFailureDoesNotFailTheSavedReferral(): void
    {
        $service = new class (new UserService()) extends ReferralTaskService {
            public function createForReferral(int $transactionId, ?int $userId, ?ReferralFormLoaded $loaded = null): string
            {
                // As DBAL's own DriverException: an \Exception, not a RuntimeException, so only the catch's DBAL arm holds it.
                throw new class ('the audit log could not be written') extends \Exception implements DbalException {
                };
            }
        };

        $this->assertNull($service->afterSave(12, 3));
    }

    /**
     * @return AbstractLogger&object{records: list<array{mixed, string, mixed}>}
     */
    private static function logger(): AbstractLogger
    {
        return new class extends AbstractLogger {
            /** @var list<array{mixed, string, mixed}> level, message, and the context's transaction id */
            public array $records = [];

            /** @param array<mixed> $context */
            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [$level, (string)$message, $context['transaction_id'] ?? null];
            }
        };
    }
}
