<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PostCalendar\ViewModel;

/**
 * Turns the calendar search's date-keyed `$A_EVENTS` into the rows the search results template
 * iterates, with each row's provider contact tooltip.
 *
 * The providers are looked up once for the whole result set: the distinct `aid` values are
 * collected first and handed to the injected lookup in a single call, instead of one `users`
 * query per row.
 *
 * @phpstan-type SearchResultRow array{
 *     event_id_token: string,
 *     datetime_display: string,
 *     provider_name: string,
 *     provider_info_title: string,
 *     catname: string,
 *     patient_name: string
 * }
 */
final readonly class SearchResultsBuilder
{
    /**
     * @param \Closure(list<int|string>): list<array<mixed>> $fetchProviders Returns the
     *        `users` rows (with `id`, `fname`, `phonew1`, `street`, `city`, `state`) for the ids.
     * @param string $contactInfoLabel Localized "contact info" label of the tooltip.
     */
    public function __construct(
        private \Closure $fetchProviders,
        private string $contactInfoLabel,
    ) {
    }

    /**
     * Build the search result rows, in event order.
     *
     * @param array<mixed> $rawEvents `$A_EVENTS`: date (Y-m-d) => list of event arrays
     * @return list<SearchResultRow>
     */
    public function build(array $rawEvents): array
    {
        $providers = $this->providersById($rawEvents);

        $rows = [];
        foreach ($rawEvents as $eDate => $dateEvents) {
            if (!is_string($eDate) || !is_array($dateEvents)) {
                continue;
            }
            $eventDateYmd = substr($eDate, 0, 4) . substr($eDate, 5, 2) . substr($eDate, 8, 2);
            foreach ($dateEvents as $event) {
                if (!is_array($event)) {
                    continue;
                }
                $aid = self::providerId($event);
                $provInfo = $aid === null ? null : ($providers[(string) $aid] ?? null);

                $startTimeRaw = $event['startTime'] ?? '00:00:00';
                $startTimeStr = is_string($startTimeRaw) ? $startTimeRaw : '00:00:00';
                $eventTs = strtotime($eDate . ' ' . $startTimeStr);
                $datetimeDisplay = $eventTs !== false ? date('Y-m-d h:i a', $eventTs) : '';

                $eid = $event['eid'] ?? '';
                $eidStr = is_int($eid) || is_string($eid) ? (string) $eid : '';

                $rows[] = [
                    'event_id_token' => $eidStr . '~' . $eventDateYmd,
                    'datetime_display' => $datetimeDisplay,
                    'provider_name' => self::stringValue($event, 'provider_name'),
                    'provider_info_title' => $this->providerInfoTitle($provInfo),
                    'catname' => self::stringValue($event, 'catname'),
                    'patient_name' => self::stringValue($event, 'patient_name'),
                ];
            }
        }
        return $rows;
    }

    /**
     * Look up every provider the events reference, in one call, keyed by `users.id`.
     *
     * @param array<mixed> $rawEvents
     * @return array<string, array<mixed>>
     */
    private function providersById(array $rawEvents): array
    {
        $ids = [];
        foreach ($rawEvents as $eDate => $dateEvents) {
            if (!is_string($eDate) || !is_array($dateEvents)) {
                continue;
            }
            foreach ($dateEvents as $event) {
                if (!is_array($event)) {
                    continue;
                }
                $aid = self::providerId($event);
                if ($aid !== null) {
                    $ids[(string) $aid] ??= $aid;
                }
            }
        }
        if ($ids === []) {
            return [];
        }

        $byId = [];
        foreach (($this->fetchProviders)(array_values($ids)) as $row) {
            $id = $row['id'] ?? null;
            if (is_int($id) || is_string($id)) {
                $byId[(string) $id] = $row;
            }
        }
        return $byId;
    }

    /**
     * The event's provider id, or null when it has none.
     *
     * @param array<mixed> $event
     */
    private static function providerId(array $event): int|string|null
    {
        $aid = $event['aid'] ?? null;
        return is_int($aid) || (is_string($aid) && $aid !== '') ? $aid : null;
    }

    /**
     * Tooltip text: "<first name> contact info:" and, when the provider was found, phone,
     * street and city/state on the following lines.
     *
     * @param array<mixed>|null $provInfo
     */
    private function providerInfoTitle(?array $provInfo): string
    {
        $title = self::stringValue($provInfo ?? [], 'fname') . ' ' . $this->contactInfoLabel . ":\n";
        if ($provInfo === null) {
            return $title;
        }
        return $title
            . self::stringValue($provInfo, 'phonew1') . "\n"
            . self::stringValue($provInfo, 'street') . "\n"
            . self::stringValue($provInfo, 'city') . ' ' . self::stringValue($provInfo, 'state');
    }

    /**
     * A string element of an array, or '' when it is missing or not a string.
     *
     * @param array<mixed> $values
     */
    private static function stringValue(array $values, string $key): string
    {
        $value = $values[$key] ?? null;
        return is_string($value) ? $value : '';
    }
}
