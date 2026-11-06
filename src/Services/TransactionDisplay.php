<?php

/**
 * Pieces of the patient Transactions screens (interface/patient_file/transaction): the
 * Transaction Type select on the add/edit page, and the date each transaction is listed under.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Utils\DateFormatterUtils;
use Twig\Environment;

final class TransactionDisplay
{
    public function __construct(private ?Environment $twig = null)
    {
    }

    /**
     * The transaction types: each LBT layout's form id and title, in layout order.
     *
     * @return list<array<mixed>>
     */
    public static function types(): array
    {
        return QueryUtils::fetchRecords(
            "SELECT grp_form_id, grp_title FROM layout_group_properties " .
            "WHERE grp_form_id LIKE 'LBT%' AND grp_group_id = '' ORDER BY grp_seq, grp_title"
        );
    }

    /**
     * The Transaction Type select, with the type being added or edited selected.
     *
     * @param list<array<mixed>> $types as types() gives them
     */
    public function typeSelect(array $types, mixed $selectedFormId): string
    {
        return $this->twig()->render('patient_file/transaction/type_select.html.twig', [
            'types' => $types,
            'selected' => $selectedFormId,
        ]);
    }

    /**
     * The date a transaction is listed under: a referral's Referral Date, which getTransByPid()
     * reads from lbt_data and which has no time, else the date and time it was saved.
     *
     * @param mixed $transaction a row as getTransByPid() gives it
     */
    public static function listDate(mixed $transaction): string
    {
        $transaction = is_array($transaction) ? $transaction : [];
        $referDate = $transaction['refer_date'] ?? null;
        if (is_string($referDate) && $referDate !== '' && $referDate !== '0') {
            $date = DateFormatterUtils::oeFormatShortDate($referDate);
            return is_string($date) ? $date : $referDate;
        }
        return DateFormatterUtils::oeFormatDateTime($transaction['date'] ?? null);
    }

    private function twig(): Environment
    {
        return $this->twig ??= ServiceContainer::getTwig();
    }
}
