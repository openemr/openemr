<?php

/**
 * Isolated tests for mapping patient_review_request and patient_review_event rows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use OpenEMR\Services\PatientReview\PayloadFormat;
use OpenEMR\Services\PatientReview\ReviewActorType;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ReviewRequestMapperIsolatedTest extends TestCase
{
    private const UUID = '0193a7c4-5b1e-4f6a-9c2d-7e8f9a0b1c2d';

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function row(array $overrides = []): array
    {
        return array_merge([
            'id' => '42',
            'uuid' => Uuid::fromString(self::UUID)->getBytes(),
            'pid' => '7',
            'type' => 'profile',
            'source' => 'portal',
            'client_id' => null,
            'status' => 'pending',
            'summary' => 'Patient request changes to demographics.',
            'payload' => '{"fname":"Ann","pid":7}',
            'payload_format' => 'json',
            'target_table' => 'patient_data',
            'target_id' => '7',
            'created_at' => '2026-10-01 09:30:00',
            'updated_at' => '2026-10-01 09:30:00',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ], $overrides);
    }

    public function testMapsARequestRow(): void
    {
        $request = (new ReviewRequestMapper())->toRequest($this->row());

        $this->assertSame(42, $request->id);
        $this->assertSame(self::UUID, $request->uuid);
        $this->assertSame(7, $request->pid);
        $this->assertSame(ReviewType::Profile, $request->type);
        $this->assertSame(ReviewSource::Portal, $request->source);
        $this->assertNull($request->clientId);
        $this->assertSame(ReviewStatus::Pending, $request->status);
        $this->assertSame(['fname' => 'Ann', 'pid' => 7], $request->payload);
        $this->assertSame(PayloadFormat::Json, $request->payloadFormat);
        $this->assertSame('patient_data', $request->targetTable);
        $this->assertSame('2026-10-01 09:30:00', $request->createdAt->format('Y-m-d H:i:s'));
        $this->assertNull($request->reviewedBy);
        $this->assertNull($request->reviewedAt);
    }

    public function testMapsAReviewedApiRequest(): void
    {
        $request = (new ReviewRequestMapper())->toRequest($this->row([
            'id' => 43,
            'source' => 'api',
            'client_id' => 'client-abc',
            'status' => 'completed',
            'reviewed_by' => '3',
            'reviewed_at' => '2026-10-02 10:00:00',
            'review_note' => 'accept',
        ]));

        $this->assertSame(43, $request->id);
        $this->assertSame(ReviewSource::Api, $request->source);
        $this->assertSame('client-abc', $request->clientId);
        $this->assertSame(3, $request->reviewedBy);
        $this->assertSame('2026-10-02 10:00:00', $request->reviewedAt?->format('Y-m-d H:i:s'));
        $this->assertSame('accept', $request->reviewNote);
    }

    /**
     * @return array<string, array{?string, string, array<array-key, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function payloadProvider(): array
    {
        return [
            'json map' => ['{"a":1}', 'json', ['a' => 1]],
            'json scalar is wrapped' => ['"text"', 'json', ['value' => 'text']],
            'legacy serialized profile' => [serialize(['fname' => 'Ann', 'pid' => '7']), 'php-serialized', ['fname' => 'Ann', 'pid' => '7']],
            'legacy raw text is wrapped' => ['12.50', 'text', ['value' => '12.50']],
            'empty payload' => ['', 'json', []],
            'null payload' => [null, 'json', []],
        ];
    }

    /**
     * @param array<array-key, mixed> $expected
     */
    #[DataProvider('payloadProvider')]
    public function testDecodesEachPayloadFormat(?string $payload, string $format, array $expected): void
    {
        $request = (new ReviewRequestMapper())->toRequest($this->row(['payload' => $payload, 'payload_format' => $format]));
        $this->assertSame($expected, $request->payload);
    }

    public function testLegacySerializedPayloadNeverRevivesObjects(): void
    {
        $request = (new ReviewRequestMapper())->toRequest($this->row([
            'payload' => serialize(['when' => new \DateTimeImmutable('2026-01-01')]),
            'payload_format' => 'php-serialized',
        ]));

        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $request->payload['when']);
    }

    public function testEncodePayloadRoundTrips(): void
    {
        $mapper = new ReviewRequestMapper();
        $payload = ['fname' => 'Ann', 'nested' => ['a' => [1, 2]]];
        $request = $mapper->toRequest($this->row(['payload' => $mapper->encodePayload($payload)]));

        $this->assertSame($payload, $request->payload);
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function badRowProvider(): array
    {
        return [
            'non-numeric id' => [['id' => 'abc']],
            'missing pid' => [['pid' => null]],
            'unparseable created_at' => [['created_at' => 'yesterday']],
            'missing created_at' => [['created_at' => null]],
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     */
    #[DataProvider('badRowProvider')]
    public function testRejectsAMalformedRow(array $overrides): void
    {
        $this->expectException(\UnexpectedValueException::class);
        (new ReviewRequestMapper())->toRequest($this->row($overrides));
    }

    public function testRejectsAnUnknownStatus(): void
    {
        $this->expectException(\ValueError::class);
        (new ReviewRequestMapper())->toRequest($this->row(['status' => 'waiting']));
    }

    public function testMapsEventRows(): void
    {
        $mapper = new ReviewRequestMapper();
        $created = $mapper->toEvent([
            'id' => '1', 'request_id' => '42', 'from_status' => null, 'to_status' => 'pending',
            'actor_type' => 'patient', 'actor_id' => '7', 'created_at' => '2026-10-01 09:30:00', 'note' => null,
        ]);
        $closed = $mapper->toEvent([
            'id' => 2, 'request_id' => 42, 'from_status' => 'pending', 'to_status' => 'completed',
            'actor_type' => 'system', 'actor_id' => null, 'created_at' => '2026-10-02 10:00:00', 'note' => 'accept',
        ]);

        $this->assertNull($created->from);
        $this->assertSame(ReviewStatus::Pending, $created->to);
        $this->assertSame(ReviewActorType::Patient, $created->actorType);
        $this->assertSame(7, $created->actorId);
        $this->assertSame(ReviewStatus::Pending, $closed->from);
        $this->assertSame(ReviewStatus::Completed, $closed->to);
        $this->assertNull($closed->actorId);
        $this->assertSame('accept', $closed->note);
    }
}
