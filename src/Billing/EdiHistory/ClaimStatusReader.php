<?php

/**
 * Reads what a 277 file says about each claim in it.
 *
 * Works for the claim acknowledgment (277CA) a clearinghouse or payer returns for an 837 and
 * for the claim status response to a 276. In both, HL level 20 is the information source, the
 * entity whose statuses the transaction carries, and below the provider level each TRN segment
 * starts one claim's block: its statuses (STC), its numbers (REF), its service date (DTP*472)
 * and, after any SVC, the statuses of its service lines.
 *
 * Every status is kept, with the source that reported it. The statuses given at the receiver
 * and provider levels are about a batch, not a claim, and are not reported.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

use DateTimeImmutable;

final class ClaimStatusReader
{
    /**
     * The ISA segment is fixed width: 106 characters, its 4th the element separator, its
     * 105th the component separator and its 106th the segment terminator.
     */
    private const ISA_LENGTH = 106;

    private const TRANSACTION_SET = '277';

    private const LEVEL_SOURCE = '20';

    /**
     * HL levels above the claim that are not the source: information receiver and provider.
     */
    private const LEVELS_ABOVE_CLAIM = ['21', '19'];

    /**
     * Element positions of an STC segment that hold a category:code:entity composite.
     */
    private const STATUS_POSITIONS = [1, 10, 11];

    private const DTP_RECEIVED = '050';

    private const DTP_SERVICE = '472';

    private function __construct()
    {
    }

    /**
     * @param string $text the whole content of an X12 file
     * @return list<ClaimStatusReport> one per claim block, in file order; empty when the text
     *                                 is not X12 or holds no 277 transaction
     */
    public static function read(string $text): array
    {
        $file = self::split($text);
        if ($file === null) {
            return [];
        }
        $component = $file['component'];

        $reports = [];
        $interchangeControlNumber = '';
        $interchangeDate = null;
        $transactionControlNumber = '';
        $in277 = false;
        $level = '';
        $source = new ClaimStatusSource('', '', '');
        $sourceReceivedDate = null;
        $patientLastName = '';
        $patientFirstName = '';
        /** @var list<list<string>> $block */
        $block = [];

        foreach ($file['segments'] as $segment) {
            $id = $segment[0];
            $endsBlock = in_array($id, ['ISA', 'ST', 'SE', 'GE', 'IEA', 'HL', 'TRN'], true);
            if ($endsBlock && $block !== []) {
                $reports[] = self::report(
                    $block,
                    $component,
                    $source,
                    $sourceReceivedDate,
                    $interchangeControlNumber,
                    $interchangeDate,
                    $transactionControlNumber,
                    $patientLastName,
                    $patientFirstName,
                );
                $block = [];
            }

            if ($id === 'ISA') {
                $interchangeDate = self::date(self::element($segment, 9), '!ymd');
                $interchangeControlNumber = self::element($segment, 13);
                $in277 = false;
                $level = '';
                continue;
            }
            if ($id === 'ST') {
                $in277 = self::element($segment, 1) === self::TRANSACTION_SET;
                $transactionControlNumber = self::element($segment, 2);
                $level = '';
                $source = new ClaimStatusSource('', '', '');
                $sourceReceivedDate = null;
                continue;
            }
            if ($id === 'SE' || $id === 'GE' || $id === 'IEA') {
                $in277 = false;
                $level = '';
                continue;
            }
            if (!$in277) {
                continue;
            }
            if ($id === 'HL') {
                $level = self::element($segment, 3);
                $patientLastName = '';
                $patientFirstName = '';
                if ($level === self::LEVEL_SOURCE) {
                    $source = new ClaimStatusSource('', '', '');
                    $sourceReceivedDate = null;
                }
                continue;
            }
            if ($level === self::LEVEL_SOURCE) {
                if ($id === 'NM1') {
                    $source = new ClaimStatusSource(
                        self::element($segment, 1),
                        self::element($segment, 3),
                        self::element($segment, 9),
                    );
                } elseif ($id === 'DTP' && self::element($segment, 1) === self::DTP_RECEIVED) {
                    $sourceReceivedDate = self::date(self::element($segment, 3), '!Ymd');
                }
                continue;
            }
            if ($level === '' || in_array($level, self::LEVELS_ABOVE_CLAIM, true)) {
                continue;
            }
            // At the claim level: subscriber, dependent or (277CA) patient.
            if ($id === 'TRN') {
                $block = [$segment];
            } elseif ($block !== []) {
                $block[] = $segment;
            } elseif ($id === 'NM1') {
                $patientLastName = self::element($segment, 3);
                $patientFirstName = self::element($segment, 4);
            }
        }

        if ($block !== []) {
            $reports[] = self::report(
                $block,
                $component,
                $source,
                $sourceReceivedDate,
                $interchangeControlNumber,
                $interchangeDate,
                $transactionControlNumber,
                $patientLastName,
                $patientFirstName,
            );
        }

        return $reports;
    }

    /**
     * @param non-empty-list<list<string>> $block     the claim's segments, its TRN first
     * @param non-empty-string             $component the component separator
     */
    private static function report(
        array $block,
        string $component,
        ClaimStatusSource $source,
        ?DateTimeImmutable $sourceReceivedDate,
        string $interchangeControlNumber,
        ?DateTimeImmutable $interchangeDate,
        string $transactionControlNumber,
        string $patientLastName,
        string $patientFirstName,
    ): ClaimStatusReport {
        $statuses = [];
        $references = [];
        $serviceDateFrom = null;
        $serviceDateTo = null;
        $serviceLine = '';

        foreach ($block as $segment) {
            $id = $segment[0];
            if ($id === 'SVC') {
                $serviceLine = self::element($segment, 1);
            } elseif ($id === 'STC') {
                foreach (self::STATUS_POSITIONS as $position) {
                    $parts = explode($component, self::element($segment, $position));
                    if ($parts[0] === '') {
                        continue;
                    }
                    $statuses[] = new ClaimStatus(
                        $parts[0],
                        $parts[1] ?? '',
                        $parts[2] ?? '',
                        self::date(self::element($segment, 2), '!Ymd'),
                        self::element($segment, 3),
                        self::element($segment, 4),
                        $position === 1 ? self::element($segment, 12) : '',
                        $serviceLine,
                    );
                }
            } elseif ($id === 'REF') {
                $value = self::element($segment, 2);
                if ($value !== '') {
                    $references[self::element($segment, 1)][] = $value;
                }
            } elseif ($id === 'DTP' && $serviceLine === '' && self::element($segment, 1) === self::DTP_SERVICE) {
                // D8 is one date, RD8 is first-last.
                $days = explode('-', self::element($segment, 3), 2);
                $serviceDateFrom = self::date($days[0], '!Ymd');
                $serviceDateTo = isset($days[1]) ? self::date($days[1], '!Ymd') : $serviceDateFrom;
            }
        }

        return new ClaimStatusReport(
            self::element($block[0], 2),
            $source,
            $sourceReceivedDate,
            $interchangeControlNumber,
            $interchangeDate,
            $transactionControlNumber,
            $patientLastName,
            $patientFirstName,
            $statuses,
            $references,
            $serviceDateFrom,
            $serviceDateTo,
        );
    }

    /**
     * Split a file into segments of elements, using the separators its ISA declares.
     *
     * @return ?array{segments: list<non-empty-list<string>>, component: non-empty-string} null when the text is not X12
     */
    private static function split(string $text): ?array
    {
        if (strlen($text) < self::ISA_LENGTH || !str_starts_with($text, 'ISA')) {
            return null;
        }
        $elementSeparator = $text[3];
        $terminator = $text[self::ISA_LENGTH - 1];
        // A file that ends its segments with a line break only.
        if ($terminator === "\r") {
            $terminator = "\n";
        }
        if (ctype_alnum($elementSeparator) || ctype_alnum($terminator) || $elementSeparator === $terminator) {
            return null;
        }

        $segments = [];
        foreach (explode($terminator, $text) as $raw) {
            $raw = trim($raw, " \t\r\n");
            if ($raw !== '') {
                $segments[] = array_map(trim(...), explode($elementSeparator, $raw));
            }
        }

        return ['segments' => $segments, 'component' => $text[self::ISA_LENGTH - 2]];
    }

    /**
     * @param list<string> $segment
     */
    private static function element(array $segment, int $position): string
    {
        return $segment[$position] ?? '';
    }

    /**
     * @param string $format a DateTimeImmutable::createFromFormat() format of digits only
     * @return ?DateTimeImmutable null when $value is not a real date in that format
     */
    private static function date(string $value, string $format): ?DateTimeImmutable
    {
        if ($value === '' || !ctype_digit($value)) {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat($format, $value);
        if ($date === false || $date->format(ltrim($format, '!')) !== $value) {
            return null;
        }
        return $date;
    }
}
