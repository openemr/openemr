<?php

/**
 * Isolated VisitListPostedBlock Test
 *
 * Reading where the appointment editor was told to save a Visit List block from its posted
 * form: one provider or several, an all-day event, a 12-hour PM time, and forms that don't
 * say.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Appointment\VisitList;

use DateTimeImmutable;
use DomainException;
use OpenEMR\Appointment\VisitList\VisitListPostedBlock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class VisitListPostedBlockTest extends TestCase
{
    /**
     * @param array<string, mixed>          $form
     * @param array{list<int>, string, string} $expected providers, date and start
     */
    #[DataProvider('forms')]
    public function testReadsTheBlocksPlaceFromTheForm(array $form, array $expected): void
    {
        $posted = VisitListPostedBlock::fromPost(new ParameterBag($form));

        $this->assertNotNull($posted);
        $this->assertSame($expected, [$posted->providerIds, $posted->date->format('Y-m-d H:i:s'), $posted->startTime]);
    }

    /**
     * @return array<string, array{array<string, mixed>, array{list<int>, string, string}}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function forms(): array
    {
        return [
            'one provider, 24-hour time' => [
                ['form_provider' => '7', 'form_date' => '2031-03-10', 'form_allday' => '0', 'form_hour' => '9', 'form_minute' => '05'],
                [[7], '2031-03-10 00:00:00', '09:05:00'],
            ],
            'several providers, sorted' => [
                ['form_provider' => ['9', '7', '9'], 'form_date' => '2031-03-10', 'form_hour' => '13', 'form_minute' => '00'],
                [[7, 9], '2031-03-10 00:00:00', '13:00:00'],
            ],
            '12-hour PM' => [
                ['form_provider' => '7', 'form_date' => '2031-03-10', 'form_hour' => '1', 'form_minute' => '30', 'form_ampm' => '2'],
                [[7], '2031-03-10 00:00:00', '13:30:00'],
            ],
            '12 noon stays 12' => [
                ['form_provider' => '7', 'form_date' => '2031-03-10', 'form_hour' => '12', 'form_minute' => '00', 'form_ampm' => '2'],
                [[7], '2031-03-10 00:00:00', '12:00:00'],
            ],
            'all day starts at midnight' => [
                ['form_provider' => '7', 'form_date' => '2031-03-10', 'form_allday' => '1', 'form_hour' => '9', 'form_minute' => '00'],
                [[7], '2031-03-10 00:00:00', '00:00:00'],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $form
     */
    #[DataProvider('formsThatDontSay')]
    public function testIsNullWhenTheFormDoesntNameTheBlocksPlace(array $form): void
    {
        $this->assertNull(VisitListPostedBlock::fromPost(new ParameterBag($form)));
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function formsThatDontSay(): array
    {
        return [
            'no provider' => [['form_date' => '2031-03-10', 'form_hour' => '9', 'form_minute' => '00']],
            'provider not a number' => [['form_provider' => 'x', 'form_date' => '2031-03-10', 'form_hour' => '9', 'form_minute' => '00']],
            'no date' => [['form_provider' => '7', 'form_hour' => '9', 'form_minute' => '00']],
            'hour past 23' => [['form_provider' => '7', 'form_date' => '2031-03-10', 'form_hour' => '24', 'form_minute' => '00']],
        ];
    }

    public function testRejectsAStartTimeWithSeconds(): void
    {
        $this->expectException(DomainException::class);
        new VisitListPostedBlock([7], new DateTimeImmutable('2031-03-10'), '09:00:30');
    }
}
