<?php

/**
 * VisitListPostedBlock
 *
 * Where the appointment editor was told to save a Visit List block: its provider(s), date and
 * start time as posted. After the save, only events there are taken as the ones the save
 * added, so a block someone else saves at the same moment is never mistaken for this one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

use DateTimeImmutable;
use DomainException;
use OpenEMR\Services\Utils\DateFormatterUtils;
use Symfony\Component\HttpFoundation\ParameterBag;

final readonly class VisitListPostedBlock
{
    /**
     * @param non-empty-list<int> $providerIds the posted providers, sorted
     * @param DateTimeImmutable   $date        the posted date, at midnight
     * @param string              $startTime   the posted start (H:i:s)
     */
    public function __construct(
        public array $providerIds,
        public DateTimeImmutable $date,
        public string $startTime,
    ) {
        foreach ($providerIds as $providerId) {
            if ($providerId <= 0) {
                throw new DomainException('Provider ID must be positive');
            }
        }
        if (preg_match('/^\d{2}:\d{2}:00$/', $startTime) !== 1) {
            throw new DomainException('Start time must be H:i:00');
        }
    }

    /**
     * The block's place from the editor's posted form, read the way add_edit_event.php reads
     * it: form_provider (one, or several for a multi-provider event), form_date in the
     * display format, and form_hour, form_minute and form_ampm, or 00:00 for an all-day event.
     * Null when the form doesn't name a provider and a date.
     *
     * @param ParameterBag $post the editor's posted form
     */
    public static function fromPost(ParameterBag $post): ?self
    {
        $posted = $post->all()['form_provider'] ?? null;
        $providerIds = [];
        foreach (is_array($posted) ? $posted : [$posted] as $provider) {
            $providerId = is_string($provider) || is_int($provider)
                ? filter_var($provider, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;
            if (is_int($providerId)) {
                $providerIds[] = $providerId;
            }
        }
        $providerIds = array_values(array_unique($providerIds));
        sort($providerIds);
        if ($providerIds === []) {
            return null;
        }

        $ymd = DateFormatterUtils::DateToYYYYMMDD($post->getString('form_date'));
        $date = is_string($ymd) ? DateTimeImmutable::createFromFormat('!Y-m-d', $ymd) : false;
        if ($date === false) {
            return null;
        }

        $hour = 0;
        $minute = 0;
        $allDay = !in_array($post->getString('form_allday'), ['', '0'], true);
        if (!$allDay) {
            // Digits, not getInt(): the minutes come as "00" or "05", which an int filter rejects.
            $hour = (int) $post->getDigits('form_hour');
            $minute = (int) $post->getDigits('form_minute');
            if ($post->getString('form_ampm') === '2' && $hour < 12) {
                $hour += 12;
            }
        }
        if ($hour > 23 || $minute > 59) {
            return null;
        }

        return new self($providerIds, $date, sprintf('%02d:%02d:00', $hour, $minute));
    }
}
