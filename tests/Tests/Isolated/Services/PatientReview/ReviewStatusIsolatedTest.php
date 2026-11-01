<?php

/**
 * Isolated tests for the review request state machine.
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
use OpenEMR\Services\PatientReview\ReviewSource;
use OpenEMR\Services\PatientReview\ReviewStatus;
use OpenEMR\Services\PatientReview\ReviewType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReviewStatusIsolatedTest extends TestCase
{
    /**
     * Every pair of states, so adding a status forces a decision about each transition.
     *
     * @return array<string, array{ReviewStatus, ReviewStatus, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function transitionProvider(): array
    {
        $allowed = [
            'pending' => ['approved', 'denied', 'cancelled', 'completed'],
            'awaiting_payment' => ['completed', 'cancelled'],
            'approved' => ['completed', 'cancelled'],
            'denied' => [],
            'cancelled' => [],
            'completed' => [],
        ];
        $cases = [];
        foreach (ReviewStatus::cases() as $from) {
            foreach (ReviewStatus::cases() as $to) {
                $cases[$from->value . ' to ' . $to->value] = [$from, $to, in_array($to->value, $allowed[$from->value], true)];
            }
        }
        return $cases;
    }

    #[DataProvider('transitionProvider')]
    public function testTransitions(ReviewStatus $from, ReviewStatus $to, bool $expected): void
    {
        $this->assertSame($expected, $from->canTransitionTo($to));
    }

    public function testOpenAndTerminalStates(): void
    {
        $open = array_values(array_filter(ReviewStatus::cases(), static fn(ReviewStatus $s): bool => $s->isOpen()));
        $terminal = array_values(array_filter(ReviewStatus::cases(), static fn(ReviewStatus $s): bool => $s->isTerminal()));

        $this->assertSame([ReviewStatus::Pending, ReviewStatus::AwaitingPayment], $open);
        $this->assertSame([ReviewStatus::Denied, ReviewStatus::Cancelled, ReviewStatus::Completed], $terminal);
    }

    /**
     * These strings are written to patient_review_request and patient_review_event; renaming one
     * orphans stored rows.
     *
     * @return array<string, array{class-string<\BackedEnum>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function storedValueProvider(): array
    {
        $cases = [];
        foreach (['pending', 'awaiting_payment', 'approved', 'denied', 'cancelled', 'completed'] as $value) {
            $cases['status ' . $value] = [ReviewStatus::class, $value];
        }
        foreach (['profile', 'payment', 'document', 'invoice'] as $value) {
            $cases['type ' . $value] = [ReviewType::class, $value];
        }
        foreach (['portal', 'api'] as $value) {
            $cases['source ' . $value] = [ReviewSource::class, $value];
        }
        foreach (['patient', 'user', 'system'] as $value) {
            $cases['actor ' . $value] = [ReviewActorType::class, $value];
        }
        foreach (['json', 'php-serialized', 'text'] as $value) {
            $cases['payload ' . $value] = [PayloadFormat::class, $value];
        }
        return $cases;
    }

    /**
     * @param class-string<\BackedEnum> $enum
     */
    #[DataProvider('storedValueProvider')]
    public function testStoredValuesAreStable(string $enum, string $value): void
    {
        $this->assertNotNull($enum::tryFrom($value), $enum . ' must keep the stored value ' . $value);
    }

    public function testOnlyAnInvoiceStartsOutsideTheStaffQueue(): void
    {
        foreach (ReviewType::cases() as $type) {
            $this->assertSame(
                $type === ReviewType::Invoice ? ReviewStatus::AwaitingPayment : ReviewStatus::Pending,
                $type->initialStatus()
            );
        }
    }
}
