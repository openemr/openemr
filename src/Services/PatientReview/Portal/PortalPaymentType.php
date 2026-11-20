<?php

/**
 * The payment types the portal payment page posts (radio_type_of_payment) and staff posting
 * (portal_payment.php) records. Backed, because the values travel in a payment's saved invoice
 * selection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Portal;

enum PortalPaymentType: string
{
    /** An encounter payment by a self-pay patient. */
    case Cash = 'cash';

    case Copay = 'copay';

    case InvoiceBalance = 'invoice_balance';

    /** A payment toward no invoice, recorded as the patient's credit. */
    case PrePayment = 'pre_payment';

    /**
     * Whether the amount is the sum of the selection's invoice lines (form_upay), rather than
     * the prepayment field.
     */
    public function paysInvoiceLines(): bool
    {
        return match ($this) {
            self::Cash, self::Copay, self::InvoiceBalance => true,
            self::PrePayment => false,
        };
    }
}
