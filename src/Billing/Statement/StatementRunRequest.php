<?php

/**
 * A statement run as the statements form posts it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use Symfony\Component\HttpFoundation\InputBag;

final readonly class StatementRunRequest
{
    /**
     * @param list<int> $checkedInvoices form_encounter.id of each checked invoice (form_cb[id])
     * @param bool $withoutUpdate Without Update: don't count the invoices as statemented
     */
    private function __construct(
        public StatementDelivery $delivery,
        public array $checkedInvoices,
        public bool $withoutUpdate,
    ) {
    }

    /**
     * The run the posted button asks for, or null when none was pressed.
     *
     * @param InputBag<bool|float|int|string> $post
     */
    public static function fromPost(InputBag $post): ?self
    {
        $delivery = match (true) {
            $post->getString('form_portalnotify') !== '' => StatementDelivery::Portal,
            $post->getString('form_email') !== '' => StatementDelivery::Email,
            $post->getString('form_download') !== '' => StatementDelivery::Download,
            $post->getString('form_pdf') !== '' => StatementDelivery::Pdf,
            $post->getString('form_print') !== '' => StatementDelivery::Print,
            default => null,
        };
        if ($delivery === null) {
            return null;
        }
        $checked = [];
        foreach (array_keys($post->all('form_cb')) as $id) {
            if (is_int($id) || ctype_digit($id)) {
                $checked[] = (int) $id;
            }
        }
        return new self($delivery, $checked, $post->getString('form_without') !== '');
    }
}
