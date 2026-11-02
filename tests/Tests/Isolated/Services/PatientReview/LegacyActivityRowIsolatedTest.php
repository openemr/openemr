<?php

/**
 * The onsite_portal_activity-shaped view of a review request that the legacy screens read.
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
use OpenEMR\Services\PatientReview\Legacy\LegacyActivityRow;
use OpenEMR\Services\PatientReview\PayloadFormat;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegacyActivityRowIsolatedTest extends TestCase
{
    /**
     * @param array<array-key, mixed> $payload
     */
    private function request(
        ReviewType $type,
        ReviewStatus $status,
        array $payload = [],
        ?string $targetId = null,
        ?int $reviewedBy = null,
        ?string $reviewNote = null,
    ): ReviewRequest {
        $created = new DateTimeImmutable('2026-10-01 09:30:00');

        return new ReviewRequest(
            id: 42,
            uuid: '00000000-0000-4000-8000-000000000000',
            pid: 7,
            type: $type,
            resourceType: null,
            source: ReviewSource::Portal,
            clientId: null,
            status: $status,
            summary: 'Summary text',
            payload: $payload,
            payloadFormat: PayloadFormat::Json,
            targetTable: $targetId === null ? null : 'onsite_documents',
            targetId: $targetId,
            createdAt: $created,
            updatedAt: $created,
            reviewedBy: $reviewedBy,
            reviewedAt: $reviewedBy === null ? null : new DateTimeImmutable('2026-10-02 10:00:00'),
            reviewNote: $reviewNote,
        );
    }

    /**
     * Every status maps to the legacy status string the screens filter on.
     *
     * @return array<string, array{ReviewStatus, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function statusProvider(): array
    {
        return [
            'pending' => [ReviewStatus::Pending, 'waiting'],
            'awaiting payment' => [ReviewStatus::AwaitingPayment, 'waiting transaction'],
            'approved' => [ReviewStatus::Approved, 'approved'],
            'denied' => [ReviewStatus::Denied, 'denied'],
            'cancelled' => [ReviewStatus::Cancelled, 'deleted'],
            'completed' => [ReviewStatus::Completed, 'closed'],
        ];
    }

    #[DataProvider('statusProvider')]
    public function testMapsEachStatus(ReviewStatus $status, string $legacy): void
    {
        $this->assertSame($legacy, LegacyActivityRow::status($status));
    }

    public function testAPendingProfileChangeLooksLikeTheLegacyRow(): void
    {
        $row = LegacyActivityRow::fromRequest($this->request(ReviewType::Profile, ReviewStatus::Pending, ['fname' => 'Ann']))->toColumns();

        $this->assertSame(42, $row['id']);
        $this->assertSame('2026-10-01 09:30:00', $row['date']);
        $this->assertSame(7, $row['patient_id']);
        $this->assertSame('profile', $row['activity']);
        $this->assertSame(1, $row['require_audit']);
        $this->assertSame('review', $row['pending_action']);
        $this->assertSame('waiting', $row['status']);
        $this->assertSame('Summary text', $row['narrative']);
        $this->assertSame(['fname' => 'Ann'], $row['table_args']);
        $this->assertSame('', $row['action_taken']);
        $this->assertSame(0, $row['action_user']);
        $this->assertNull($row['action_taken_time']);
        $this->assertSame('', $row['checksum'], 'card data is never exposed');
    }

    public function testAPostedPaymentLooksLikeTheLegacyRow(): void
    {
        $row = LegacyActivityRow::fromRequest($this->request(
            ReviewType::Payment,
            ReviewStatus::Completed,
            ['value' => '{"1001":"10.00"}'],
            reviewedBy: 3,
            reviewNote: 'payment posted',
        ))->toColumns();

        $this->assertSame('closed', $row['status']);
        $this->assertSame('notify patient', $row['pending_action']);
        $this->assertSame('payment posted', $row['action_taken']);
        $this->assertSame('update', $row['table_action']);
        $this->assertSame('{"1001":"10.00"}', $row['table_args']);
        $this->assertSame(3, $row['action_user']);
        $this->assertSame('2026-10-02 10:00:00', $row['action_taken_time']);
    }

    public function testAnInvoiceIsNotAudited(): void
    {
        $new = LegacyActivityRow::fromRequest($this->request(ReviewType::Invoice, ReviewStatus::AwaitingPayment, ['invoices' => [5]]))->toColumns();
        $migrated = LegacyActivityRow::fromRequest($this->request(ReviewType::Invoice, ReviewStatus::AwaitingPayment, ['value' => '[5]']))->toColumns();

        $this->assertSame(0, $new['require_audit']);
        $this->assertSame('payment', $new['pending_action']);
        $this->assertSame('waiting transaction', $new['status']);
        $this->assertSame('[5]', $new['table_args']);
        $this->assertSame('[5]', $migrated['table_args']);
    }

    public function testADocumentCarriesItsDocumentIdAsTableArgs(): void
    {
        $pending = LegacyActivityRow::fromRequest($this->request(ReviewType::Document, ReviewStatus::Pending, [], '11'))->toModel();
        $deleted = LegacyActivityRow::fromRequest($this->request(ReviewType::Document, ReviewStatus::Cancelled, [], '11'))->toModel();

        $this->assertSame('11', $pending['tableArgs']);
        $this->assertSame('document', $pending['activity']);
        $this->assertSame('', $pending['tableAction']);
        $this->assertSame('deleted', $deleted['status']);
        $this->assertSame('none', $deleted['pendingAction']);
        $this->assertSame('delete', $deleted['tableAction']);
    }

    public function testTheModelFormEncodesAnArrayPayload(): void
    {
        $model = LegacyActivityRow::fromRequest($this->request(ReviewType::Profile, ReviewStatus::Pending, ['fname' => 'Ann']))->toModel();

        $this->assertSame('{"fname":"Ann"}', $model['tableArgs']);
        $this->assertSame(7, $model['patientId']);
        $this->assertSame('', $model['checksum']);
    }
}
