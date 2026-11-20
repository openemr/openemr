<?php

/**
 * The onsite_portal_activity -> review queue mapping: one case per row of the state mapping
 * in #14337, plus payloads, targets, reviewers, card secrets and skipped rows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use DateTimeImmutable;
use OpenEMR\Services\PatientReview\Migration\LegacyActivityMapping;
use OpenEMR\Services\PatientReview\Migration\LegacyActivityPlan;
use OpenEMR\Services\PatientReview\PayloadFormat;
use OpenEMR\Services\PatientReview\ReviewActorType;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegacyActivityMappingIsolatedTest extends TestCase
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function row(array $overrides = []): array
    {
        return array_merge([
            'id' => '501',
            'date' => '2026-09-01 08:00:00',
            'patient_id' => '7',
            'activity' => 'profile',
            'require_audit' => '1',
            'pending_action' => 'review',
            'action_taken' => '',
            'status' => 'waiting',
            'narrative' => 'Patient request changes to demographics.',
            'table_action' => '',
            'table_args' => serialize(['fname' => 'Ann']),
            'action_user' => '0',
            'action_taken_time' => null,
            'checksum' => '0',
        ], $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function plan(array $overrides = []): LegacyActivityPlan
    {
        $plan = (new LegacyActivityMapping())->plan($this->row($overrides), new DateTimeImmutable('2026-11-01 00:00:00'));
        $this->assertNotNull($plan);
        return $plan;
    }

    /**
     * One case per row of the #14337 state mapping, for each type it applies to.
     *
     * @return array<string, array{string, string, ReviewStatus, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function stateProvider(): array
    {
        return [
            'profile waiting -> pending' => ['profile', 'waiting', ReviewStatus::Pending, false],
            'payment waiting -> pending' => ['payment', 'waiting', ReviewStatus::Pending, false],
            'document waiting -> pending' => ['document', 'waiting', ReviewStatus::Pending, false],
            'invoice waiting transaction -> awaiting_payment' => ['invoice', 'waiting transaction', ReviewStatus::AwaitingPayment, false],
            'profile closed -> completed' => ['profile', 'closed', ReviewStatus::Completed, false],
            'payment closed -> completed' => ['payment', 'closed', ReviewStatus::Completed, false],
            'document closed -> completed' => ['document', 'closed', ReviewStatus::Completed, false],
            'invoice closed -> completed' => ['invoice', 'closed', ReviewStatus::Completed, false],
            'document deleted -> cancelled' => ['document', 'deleted', ReviewStatus::Cancelled, false],
            'payment deleted -> cancelled' => ['payment', 'deleted', ReviewStatus::Cancelled, false],
            'document editing -> cancelled' => ['document', 'editing', ReviewStatus::Cancelled, false],
            'profile new (code default) -> pending, flagged' => ['profile', 'new', ReviewStatus::Pending, true],
            'payment unknown -> pending, flagged' => ['payment', 'review', ReviewStatus::Pending, true],
            'profile editing -> pending, flagged' => ['profile', 'editing', ReviewStatus::Pending, true],
            'empty status -> pending, flagged' => ['document', '', ReviewStatus::Pending, true],
            'invoice unknown -> awaiting_payment, flagged' => ['invoice', 'accept', ReviewStatus::AwaitingPayment, true],
        ];
    }

    #[DataProvider('stateProvider')]
    public function testMapsEachLegacyState(string $activity, string $status, ReviewStatus $expected, bool $flagged): void
    {
        $plan = $this->plan(['activity' => $activity, 'status' => $status, 'table_args' => $activity === 'document' ? '11' : '']);

        $this->assertSame(ReviewType::from($activity), $plan->type);
        $this->assertSame($expected, $plan->status);
        $this->assertSame($flagged, $plan->unrecognisedStatus !== null);

        // The history starts at the type's first state and, if it moved, records the move.
        $this->assertNull($plan->events[0]->from);
        $this->assertSame($plan->type->initialStatus(), $plan->events[0]->to);
        $last = $plan->events[count($plan->events) - 1];
        $this->assertSame($expected, $last->to);
        $this->assertTrue($last->from === null || $last->from->canTransitionTo($last->to), 'the recorded move is an allowed transition');
        if ($flagged) {
            $this->assertStringStartsWith(LegacyActivityMapping::NOTE_UNRECOGNISED, (string) $last->note);
        }
    }

    public function testAPendingProfileKeepsItsSerializedPayload(): void
    {
        $plan = $this->plan();

        $this->assertSame(501, $plan->legacyId);
        $this->assertSame(7, $plan->pid);
        $this->assertSame(PayloadFormat::PhpSerialized, $plan->payloadFormat);
        $this->assertSame(serialize(['fname' => 'Ann']), $plan->payload);
        $this->assertSame(['patient_data', '7'], [$plan->targetTable, $plan->targetId]);
        $this->assertSame('2026-09-01 08:00:00', $plan->createdAt->format('Y-m-d H:i:s'));
        $this->assertCount(1, $plan->events);
        $this->assertSame(ReviewActorType::Patient, $plan->events[0]->actor->type);
        $this->assertNull($plan->reviewedBy);
    }

    public function testAClosedProfileRecordsItsReviewerAndAction(): void
    {
        $plan = $this->plan([
            'status' => 'closed',
            'pending_action' => 'completed',
            'action_taken' => 'accept',
            'action_user' => '3',
            'action_taken_time' => '2026-09-02 10:00:00',
        ]);

        $this->assertSame(ReviewStatus::Completed, $plan->status);
        $this->assertSame(3, $plan->reviewedBy);
        $this->assertSame('2026-09-02 10:00:00', $plan->reviewedAt?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-02 10:00:00', $plan->updatedAt->format('Y-m-d H:i:s'));
        $this->assertSame('accept', $plan->reviewNote);
        $this->assertCount(2, $plan->events);
        $this->assertSame(ReviewActorType::User, $plan->events[1]->actor->type);
        $this->assertSame(3, $plan->events[1]->actor->id);
        $this->assertSame('accept', $plan->events[1]->note);
    }

    public function testAClosedRowWithoutAUserIsClosedByTheSystem(): void
    {
        $plan = $this->plan(['status' => 'closed', 'action_user' => '0']);

        $this->assertNull($plan->reviewedBy);
        $this->assertSame(ReviewActorType::System, $plan->events[1]->actor->type);
        $this->assertSame(LegacyActivityMapping::SYSTEM_ACTOR, $plan->events[1]->actor->name);
    }

    public function testAnOpenPaymentMovesItsCardDataToASecret(): void
    {
        $plan = $this->plan(['activity' => 'payment', 'table_args' => '{"1001":"10.00"}', 'checksum' => 'v6:ciphertext']);

        $this->assertSame('v6:ciphertext', $plan->cardCiphertext);
        $this->assertSame(PayloadFormat::Text, $plan->payloadFormat);
        $this->assertSame('{"1001":"10.00"}', $plan->payload);
        $this->assertSame([null, null], [$plan->targetTable, $plan->targetId]);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function noSecretProvider(): array
    {
        return [
            'closed payment drops its card data' => ['closed', 'v6:ciphertext'],
            'cancelled payment drops its card data' => ['deleted', 'v6:ciphertext'],
            'the "0" placeholder is not card data' => ['waiting', '0'],
            'empty checksum' => ['waiting', ''],
        ];
    }

    #[DataProvider('noSecretProvider')]
    public function testOnlyAnOpenPaymentWithCardDataGetsASecret(string $status, string $checksum): void
    {
        $this->assertNull($this->plan(['activity' => 'payment', 'status' => $status, 'checksum' => $checksum])->cardCiphertext);
    }

    public function testAProfileChecksumIsNeverASecret(): void
    {
        $this->assertNull($this->plan(['checksum' => 'v6:ciphertext'])->cardCiphertext);
    }

    public function testADocumentTargetsItsDocumentRow(): void
    {
        $plan = $this->plan(['activity' => 'document', 'table_args' => '11', 'narrative' => 'Privacy Notice']);

        $this->assertSame(['onsite_documents', '11'], [$plan->targetTable, $plan->targetId]);
        $this->assertSame('Privacy Notice', $plan->summary);
    }

    public function testADocumentBeingEditedIsCancelledWithANote(): void
    {
        $plan = $this->plan(['activity' => 'document', 'table_args' => '11', 'status' => 'editing']);

        $this->assertSame(ReviewStatus::Cancelled, $plan->status);
        $this->assertSame(LegacyActivityMapping::NOTE_NEVER_SUBMITTED, $plan->events[1]->note);
        $this->assertNull($plan->unrecognisedStatus);
    }

    public function testAnInvoiceIsCreatedByTheSystemAndHasNoReviewer(): void
    {
        $plan = $this->plan([
            'activity' => 'invoice',
            'status' => 'closed',
            'action_user' => '7',
            'table_args' => '[{"encounter":5}]',
        ]);

        $this->assertSame(ReviewActorType::System, $plan->events[0]->actor->type);
        $this->assertNull($plan->reviewedBy, 'an invoice action_user is the patient, not a reviewer');
        $this->assertSame('[{"encounter":5}]', $plan->payload);
    }

    public function testMissingDatesFallBack(): void
    {
        $fromAction = $this->plan(['date' => null, 'action_taken_time' => '2026-09-03 11:00:00']);
        $fromNow = $this->plan(['date' => '0000-00-00 00:00:00', 'action_taken_time' => null]);

        $this->assertSame('2026-09-03 11:00:00', $fromAction->createdAt->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-01 00:00:00', $fromNow->createdAt->format('Y-m-d H:i:s'));
    }

    public function testALongNarrativeIsTruncatedToTheSummaryColumn(): void
    {
        $this->assertSame(255, mb_strlen($this->plan(['narrative' => str_repeat('x', 400)])->summary));
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function skippedProvider(): array
    {
        return [
            'unknown activity' => [['activity' => 'message']],
            'no patient' => [['patient_id' => null]],
            'patient zero' => [['patient_id' => '0']],
            'no id' => [['id' => null]],
            'fhir-write is not a legacy activity' => [['activity' => 'fhir-write']],
            'document without a document id' => [['activity' => 'document', 'table_args' => '']],
            'document with a non-numeric id' => [['activity' => 'document', 'table_args' => 'abc']],
            'document with id zero' => [['activity' => 'document', 'table_args' => '0']],
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     */
    #[DataProvider('skippedProvider')]
    public function testRowsThatCannotBeMappedAreSkipped(array $overrides): void
    {
        $this->assertNull((new LegacyActivityMapping())->plan($this->row($overrides), new DateTimeImmutable()));
    }
}
