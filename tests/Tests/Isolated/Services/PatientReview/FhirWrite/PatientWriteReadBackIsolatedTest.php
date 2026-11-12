<?php

/**
 * Isolated tests for a patient app reading back the writes it sent that are waiting for review,
 * and for what every other read of those ids finds.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview\FhirWrite;

use DateTimeImmutable;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReadBack;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReadOutcome;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewRequest;
use PHPUnit\Framework\TestCase;

class PatientWriteReadBackIsolatedTest extends TestCase
{
    use PatientWriteQueueFixture;

    private const OTHER_CLIENT = 'client-xyz';

    private PatientWriteReadBack $readBack;

    protected function setUp(): void
    {
        $this->setUpQueue();
        $this->readBack = new PatientWriteReadBack($this->queue);
    }

    private function held(PatientWritableType $type = PatientWritableType::VitalSignObservation, string $clientId = self::CLIENT): ReviewRequest
    {
        return $this->hold()->hold($this->context($clientId), $type, $this->resourceOf($type));
    }

    /**
     * The app's own pending write reads back under the id it was given, tagged held-for-review.
     */
    public function testTheAppReadsItsWaitingWriteUnderItsIdTaggedAsHeld(): void
    {
        $request = $this->held();

        $read = $this->readBack->read($this->context(), PatientWritableType::VitalSignObservation, $request->uuid);

        $this->assertSame(PatientWriteReadOutcome::Held, $read->outcome);
        $resource = $read->resource ?? [];
        $this->assertSame(['resourceType', 'id', 'meta'], array_slice(array_keys($resource), 0, 3));
        $this->assertSame('Observation', $resource['resourceType']);
        $this->assertSame($request->uuid, $resource['id']);
        $this->assertSame([
            'lastUpdated' => $request->updatedAt->format(DATE_ATOM),
            'tag' => [[
                'system' => PatientWriteReadBack::HELD_TAG_SYSTEM,
                'code' => PatientWriteReadBack::HELD_TAG_CODE,
                'display' => 'Held for staff review; not yet in the chart',
            ]],
        ], $resource['meta']);
        $this->assertSame('preliminary', $resource['status']);
        $this->assertSame(['reference' => 'Patient/' . self::PATIENT], $resource['subject']);
        $this->assertSame([['ids' => [$request->id], 'actor' => 'ann7']], $this->trail->reads, 'the read is audited');
    }

    /**
     * To another app, another patient's token or another type's endpoint the write does not
     * exist, and nothing is shown, so nothing is audited.
     */
    public function testNoOneButTheWritingAppSeesIt(): void
    {
        $request = $this->held();
        $vital = PatientWritableType::VitalSignObservation;

        foreach (
            [
                'another app' => $this->readBack->read($this->context(self::OTHER_CLIENT), $vital, $request->uuid),
                'another patient' => $this->readBack->read($this->context(pid: 8), $vital, $request->uuid),
                'another type' => $this->readBack->read($this->context(), PatientWritableType::Condition, $request->uuid),
                'no such id' => $this->readBack->read($this->context(), $vital, '00000000-0000-4000-8000-000000000000'),
                'not a uuid' => $this->readBack->read($this->context(), $vital, 'Observation/1'),
            ] as $label => $read
        ) {
            $this->assertSame(PatientWriteReadOutcome::NotHeld, $read->outcome, $label);
            $this->assertNull($read->resource, $label);
        }
        $this->assertSame([], $this->trail->reads);
    }

    /**
     * The app's own denied or withdrawn write reads as Gone (410), not as a chart record.
     */
    public function testADeniedOrWithdrawnWriteIsGone(): void
    {
        $denied = $this->held();
        $this->queue->deny($denied->id, ReviewActor::user(3, 'drsmith'), 'not a home reading');
        $withdrawn = $this->held();
        $this->queue->cancel($withdrawn->id, $this->context()->actor);

        foreach ([$denied, $withdrawn] as $request) {
            $read = $this->readBack->read($this->context(), PatientWritableType::VitalSignObservation, $request->uuid);
            $this->assertSame(PatientWriteReadOutcome::Gone, $read->outcome);
            $this->assertNull($read->resource);
        }
        $this->assertSame([], $this->trail->reads);
    }

    /**
     * Once approved, the id names a chart record; the queue leaves the read to the chart.
     */
    public function testAnApprovedWriteIsReadFromTheChart(): void
    {
        $request = $this->held();
        $this->queue->complete($request->id, ReviewActor::user(3, 'drsmith'));

        $read = $this->readBack->read($this->context(), PatientWritableType::VitalSignObservation, $request->uuid);

        $this->assertSame(PatientWriteReadOutcome::NotHeld, $read->outcome);
    }

    /**
     * A replaced write reads back as replaced, with meta.lastUpdated the time of the replacement.
     */
    public function testAReplacedWriteShowsWhenItWasReplaced(): void
    {
        $request = $this->held();
        $this->clock->setTo(new DateTimeImmutable('2026-11-07 11:15:00'));
        $this->hold()->replace(
            $this->context(),
            PatientWritableType::VitalSignObservation,
            $request->uuid,
            ['id' => $request->uuid] + $this->vitalSign(72)
        );

        $resource = $this->readBack->read($this->context(), PatientWritableType::VitalSignObservation, $request->uuid)->resource ?? [];

        $meta = $resource['meta'] ?? null;
        $this->assertIsArray($meta);
        $this->assertSame((new DateTimeImmutable('2026-11-07 11:15:00'))->format(DATE_ATOM), $meta['lastUpdated'] ?? null);
        $this->assertSame(['value' => 72, 'unit' => 'kg'], $resource['valueQuantity'] ?? null);
    }

    /**
     * Search shows only this app's waiting writes of the type, oldest first, in one audit entry.
     */
    public function testSearchFindsOnlyTheAppsWaitingWritesOfTheType(): void
    {
        $first = $this->held();
        $this->held(PatientWritableType::Condition);
        $this->held(clientId: self::OTHER_CLIENT);
        $denied = $this->held();
        $this->queue->deny($denied->id, ReviewActor::user(3, 'drsmith'), 'duplicate');
        $second = $this->held();

        $found = $this->readBack->search($this->context(), PatientWritableType::VitalSignObservation);

        $this->assertSame([$first->uuid, $second->uuid], array_column($found, 'id'));
        $this->assertSame([['ids' => [$first->id, $second->id], 'actor' => 'ann7']], $this->trail->reads);
    }

    public function testASearchThatFindsNothingIsNotAudited(): void
    {
        $this->held(clientId: self::OTHER_CLIENT);

        $this->assertSame([], $this->readBack->search($this->context(), PatientWritableType::VitalSignObservation));
        $this->assertSame([], $this->trail->reads);
    }
}
