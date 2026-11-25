<?php

/**
 * Read-only EDI X12 277 / 277CA claim-status reader.
 *
 * Reuses an already-built OpenEMR\Billing\EdiHistory\X12File instead of
 * parsing envelopes again: it iterates the ST entries from edih_envelopes()
 * whose type is 277, slices the segment list from edih_segments() using each
 * ST's start and count, and takes the element, composite and segment
 * delimiters from edih_delimiters().
 *
 * Within each ST the HL segments are walked while maintaining a parent stack
 * keyed by HL01 with parent HL02 and level HL03. Level 20 is an information
 * source, 21 an information receiver, 22 and 23 are the subscriber and
 * dependent levels that carry claim status in a 277 response, PT is the claim
 * level in a 277CA, and SS is a service line. A new claim starts whenever a
 * TRN segment with TRN01 equal to '2' appears at claim level; TRN02 is the
 * claim's reference key, so several claims under one patient remain separate
 * claim objects.
 *
 * Every status observed is appended to the current claim and never
 * overwritten. STC segments at SS level attach as service-line statuses
 * carrying the preceding SVC segment. Claim level also captures REF*1K,
 * REF*D9, REF*EJ, DTP*472 and the patient name from NM1 with entity code IL
 * or QC. Each claim is attached to its nearest ancestor information source
 * (level 20).
 *
 * The reader touches no database, globals, session, or code-label tables. It
 * returns raw facts exactly as the file states them, with no interpretation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR Community
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

final class Claim277Reader
{
    /** @var list<string> HL levels that carry a claim in a 277 / 277CA transaction */
    private const CLAIM_LEVELS = ['22', '23', 'PT'];

    public function __construct(
        private readonly X12File $file,
    ) {
    }

    /**
     * All claims found in the 277 / 277CA ST-SE envelopes of the file, in
     * file order.
     *
     * @return list<Claim277Claim>
     */
    public function claims(): array
    {
        $segments = $this->file->edih_segments();
        $envelopes = $this->file->edih_envelopes();
        $delimiters = $this->file->edih_delimiters();

        $elementDelimiter = $delimiters['e'] ?? '*';
        $compositeDelimiter = $delimiters['s'] ?? ':';

        $claims = [];
        foreach ($envelopes['ST'] ?? [] as $st) {
            if ($st['type'] !== '277') {
                continue;
            }

            $stSegments = array_slice(
                $segments,
                (int) ($st['start'] ?? 0),
                (int) ($st['count'] ?? 0),
                true,
            );

            $claims = array_merge($claims, $this->parseSt($stSegments, $elementDelimiter, $compositeDelimiter));
        }

        return $claims;
    }

    /**
     * Finds a claim by its reference key (TRN02) with exact string equality
     * (===). Never matches substrings.
     */
    public function findClaim(string $reference): ?Claim277Claim
    {
        foreach ($this->claims() as $claim) {
            if ($claim->reference === $reference) {
                return $claim;
            }
        }

        return null;
    }

    /**
     * @param array<int,string> $segments
     * @return list<Claim277Claim>
     */
    private function parseSt(array $segments, string $elementDelimiter, string $compositeDelimiter): array
    {
        $working = [];
        $hlStack = [];
        $currentHl = '';
        $sourceData = [];
        $lastSvc = null;
        $subscriberName = null;
        $dependentName = null;

        foreach ($segments as $segment) {
            $parts = explode($elementDelimiter, $segment);
            $segId = $parts[0] ?? '';

            switch ($segId) {
                case 'HL':
                    $hl01 = $parts[1] ?? '';
                    $hlStack[$hl01] = [
                        'parent' => $parts[2] ?? '',
                        'level' => $parts[3] ?? '',
                    ];
                    $currentHl = $hl01;
                    if (($parts[3] ?? '') === '22') {
                        $subscriberName = null;
                        $dependentName = null;
                    }
                    break;

                case 'TRN':
                    if (
                        ($parts[1] ?? '') === '2'
                        && $this->isClaimLevel(($hlStack[$currentHl] ?? [])['level'] ?? '')
                    ) {
                        $working[] = [
                            'reference' => $parts[2] ?? '',
                            'source' => $this->resolveSource($hlStack, $currentHl, $sourceData),
                            'statuses' => [],
                            'serviceLineStatuses' => [],
                            'payerClaimNumber' => null,
                            'clearinghouseTrace' => null,
                            'patientAccountNumber' => null,
                            'serviceDate' => null,
                            'patientName' => $dependentName ?? $subscriberName,
                            'patientNameFromQc' => $dependentName !== null,
                        ];
                        $lastSvc = null;
                    }
                    break;

                case 'STC':
                    if ($working === []) {
                        break;
                    }
                    $level = ($hlStack[$currentHl] ?? [])['level'] ?? '';
                    if (!in_array($level, ['22', '23', 'PT', 'SS'], true)) {
                        break;
                    }
                    $current = &$working[array_key_last($working)];
                    $status = $this->parseStc($segment, $parts, $compositeDelimiter);
                    if ($level === 'SS') {
                        $status['svcSegment'] = $lastSvc;
                        $current['serviceLineStatuses'][] = $status;
                    } else {
                        $current['statuses'][] = $status;
                    }
                    unset($current);
                    break;

                case 'NM1':
                    $level = ($hlStack[$currentHl] ?? [])['level'] ?? '';
                    $entity = $parts[1] ?? '';
                    if ($level === '20') {
                        $sourceData[$currentHl] = $this->updateSource(
                            $sourceData[$currentHl] ?? [],
                            $parts[1] ?? '',
                            $parts[3] ?? '',
                            $parts[4] ?? '',
                            $parts[8] ?? '',
                            $parts[9] ?? '',
                        );
                    } elseif ($this->isClaimLevel($level)) {
                        // Loop-context names: subscriber at level 22 (IL), dependent at level 23 (QC).
                        if ($level === '22' && $entity === 'IL') {
                            $subscriberName = $this->combineName($parts[4] ?? '', $parts[3] ?? '');
                        } elseif ($level === '23' && $entity === 'QC') {
                            $dependentName = $this->combineName($parts[4] ?? '', $parts[3] ?? '');
                        }
                        // Claim-level patient name: QC preferred, first wins, IL only fills null.
                        if (($entity === 'IL' || $entity === 'QC') && $working !== []) {
                            $current = &$working[array_key_last($working)];
                            if ($entity === 'QC') {
                                if (!$current['patientNameFromQc']) {
                                    $current['patientName'] = $this->combineName($parts[4] ?? '', $parts[3] ?? '');
                                    $current['patientNameFromQc'] = true;
                                }
                            } elseif ($current['patientName'] === null) {
                                $current['patientName'] = $this->combineName($parts[4] ?? '', $parts[3] ?? '');
                            }
                            unset($current);
                        }
                    }
                    break;

                case 'DTP':
                    $level = ($hlStack[$currentHl] ?? [])['level'] ?? '';
                    if (($parts[1] ?? '') === '050' && $level === '20') {
                        $sourceData[$currentHl]['receivedDate'] = $this->opt($parts[3] ?? null);
                    } elseif (
                        ($parts[1] ?? '') === '472'
                        && $this->isClaimLevel($level)
                        && $working !== []
                    ) {
                        $date = $parts[3] ?? '';
                        $current = &$working[array_key_last($working)];
                        if ($current['serviceDate'] === null && $date !== '') {
                            $current['serviceDate'] = substr($date, 0, 8);
                        }
                        unset($current);
                    }
                    break;

                case 'REF':
                    if ($working === [] || !$this->isClaimLevel(($hlStack[$currentHl] ?? [])['level'] ?? '')) {
                        break;
                    }
                    $current = &$working[array_key_last($working)];
                    switch (($parts[1] ?? '')) {
                        case '1K':
                            if ($current['payerClaimNumber'] === null) {
                                $current['payerClaimNumber'] = $this->opt($parts[2] ?? null);
                            }
                            break;
                        case 'D9':
                            if ($current['clearinghouseTrace'] === null) {
                                $current['clearinghouseTrace'] = $this->opt($parts[2] ?? null);
                            }
                            break;
                        case 'EJ':
                            if ($current['patientAccountNumber'] === null) {
                                $current['patientAccountNumber'] = $this->opt($parts[2] ?? null);
                            }
                            break;
                    }
                    unset($current);
                    break;

                case 'SVC':
                    $lastSvc = $segment;
                    break;
            }
        }

        $claims = [];
        foreach ($working as $claim) {
            $claims[] = new Claim277Claim(
                reference: $claim['reference'],
                informationSource: $claim['source'],
                payerClaimNumber: $claim['payerClaimNumber'],
                clearinghouseTrace: $claim['clearinghouseTrace'],
                patientAccountNumber: $claim['patientAccountNumber'],
                serviceDate: $claim['serviceDate'],
                patientName: $claim['patientName'],
                statuses: $this->buildStatuses($claim['statuses']),
                serviceLineStatuses: $this->buildStatuses($claim['serviceLineStatuses']),
            );
        }

        return $claims;
    }

    /**
     * Walks the HL parent stack from the current HL up to the nearest
     * ancestor whose level is 20 (information source).
     */
    private function resolveSource(array $hlStack, string $currentHl, array $sourceData): ?Claim277Source
    {
        $hl = $currentHl;
        while ($hl !== '') {
            $entry = $hlStack[$hl] ?? null;
            if ($entry === null) {
                break;
            }
            if ($entry['level'] === '20') {
                return $this->buildSource($sourceData[$hl] ?? []);
            }
            $hl = $entry['parent'] ?? '';
        }

        return null;
    }

    private function buildSource(array $parts): ?Claim277Source
    {
        if (!isset($parts['entityCode'])) {
            return null;
        }

        return new Claim277Source(
            entityCode: $parts['entityCode'],
            name: $parts['name'] ?? null,
            idCode: $parts['idCode'] ?? null,
            id: $parts['id'] ?? null,
            receivedDate: $parts['receivedDate'] ?? null,
        );
    }

    /**
     * @param list<array> $working
     * @return list<Claim277Status>
     */
    private function buildStatuses(array $working): array
    {
        $statuses = [];
        foreach ($working as $status) {
            $statuses[] = new Claim277Status(
                category: $status['category'],
                code: $status['code'],
                entity: $status['entity'],
                statusDate: $status['statusDate'],
                actionCode: $status['actionCode'],
                amount: $status['amount'],
                payerText: $status['payerText'],
                rawSegment: $status['rawSegment'],
                svcSegment: $status['svcSegment'],
            );
        }

        return $statuses;
    }

    private function parseStc(string $segment, array $parts, string $compositeDelimiter): array
    {
        $composites = explode($compositeDelimiter, $parts[1] ?? '');

        return [
            'category' => $composites[0] ?? '',
            'code' => $composites[1] ?? '',
            'entity' => $composites[2] ?? '',
            'statusDate' => $this->opt($parts[2] ?? null),
            'actionCode' => $this->opt($parts[3] ?? null),
            'amount' => $this->opt($parts[4] ?? null),
            'payerText' => $this->opt($parts[7] ?? null),
            'rawSegment' => $segment,
            'svcSegment' => null,
        ];
    }

    private function updateSource(
        array $parts,
        string $entityCode,
        string $lastName,
        string $firstName,
        string $idCode,
        string $id,
    ): array {
        $parts['entityCode'] = $entityCode;

        $name = $this->combineName($firstName, $lastName);
        if ($name !== null) {
            $parts['name'] = $name;
        }
        if ($idCode !== '') {
            $parts['idCode'] = $idCode;
        }
        if ($id !== '') {
            $parts['id'] = $id;
        }

        return $parts;
    }

    private static function combineName(string $firstName, string $lastName): ?string
    {
        $name = trim($firstName . ' ' . $lastName);

        return $name === '' ? null : $name;
    }

    private static function opt(?string $value): ?string
    {
        return ($value === null || $value === '') ? null : $value;
    }

    private function isClaimLevel(string $level): bool
    {
        return in_array($level, self::CLAIM_LEVELS, true);
    }
}
