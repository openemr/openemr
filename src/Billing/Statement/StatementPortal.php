<?php

/**
 * Sending a patient statement to the patient portal (onsite portal two).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Services\PatientReview\Portal\SessionReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class StatementPortal
{
    /**
     * @param bool $enabled the portal_onsite_two_enable global
     * @param string $templateDir the site's onsite_portal_documents/templates folder
     * @param ?\Closure(mixed, mixed, mixed, mixed, mixed, mixed, mixed, mixed, mixed, mixed=, mixed=): (int|string) $sendMail sendMail() from portal/lib/portal_mail.inc.php,
     *     which the page loads when the portal is on; 1 when the message was saved
     */
    public function __construct(
        private bool $enabled,
        private string $templateDir,
        private SessionInterface $session,
        private ?\Closure $sendMail,
    ) {
    }

    /**
     * The patient may use the portal. Also remembers them as the session's portal user,
     * whom the notification goes to.
     */
    public function isAuthorized(int $pid): bool
    {
        return $this->enabled && self::authorize($pid);
    }

    /**
     * The statement list marks the patient as a portal user. Unlike isAuthorized(),
     * this leaves the session alone: reopening it for each listed patient fails once
     * the page has started its output.
     */
    public function listsAsPortalUser(int $pid): bool
    {
        return $this->enabled && self::portalUser($pid) !== null;
    }

    /**
     * is_auth_portal() for the site's statement.inc.php: the patient may use the
     * portal, and is now the session's portal user.
     */
    public static function authorize(int $pid): bool
    {
        $portalUser = self::portalUser($pid);
        if ($portalUser === null) {
            return false;
        }
        // The page's session may be closed for writing by now; this reopens it.
        SessionUtil::setSession('portalUser', $portalUser);
        return true;
    }

    /**
     * The user name of the patient's portal account, which the portal reads their
     * mail by, or null when they may not use the portal or have no account.
     */
    private static function portalUser(int $pid): ?string
    {
        $patient = QueryUtils::querySingleRow(
            'SELECT p.allow_patient_portal, a.portal_username FROM `patient_data` AS p '
            . 'LEFT JOIN `patient_access_onsite` AS a ON a.pid = p.pid WHERE p.pid = ?',
            [$pid]
        );
        if (!is_array($patient) || ($patient['allow_patient_portal'] ?? '') !== 'YES') {
            return null;
        }
        $username = $patient['portal_username'] ?? null;
        return is_string($username) && $username !== '' ? $username : null;
    }

    /**
     * Put the statement in the patient's portal documents, ask them to pay the
     * invoices online, and message the patient and the biller.
     *
     * @param list<array<string, mixed>> $invoices the statement as it stood after each of the patient's invoices
     */
    public function notify(int $pid, array $invoices, string $statement, string $invoiceId): bool
    {
        $sendMail = $this->sendMail;
        if (!$this->enabled || $sendMail === null || $statement === '' || $invoices === []) {
            return false;
        }
        $patientDir = $this->templateDir . '/' . $pid;
        if (!is_dir($patientDir) && !mkdir($patientDir, 0755, true) && !is_dir($patientDir)) {
            return false;
        }
        if (file_put_contents($patientDir . '/invoice' . $invoiceId . '.tpl', $statement) === false) {
            return false;
        }

        // A newer request replaces the one still waiting on the patient.
        ReviewQueueFactory::portalWorkflows()->requestInvoicePayment($pid, $invoices, SessionReviewActor::staff($this->session));

        $note = xl('You have an invoice due for payment in your Patient Documents. There you may pay, download or print the invoice. Thank you.');
        $authUser = $this->session->get('authUser');
        $portalUser = $this->session->get('portalUser');
        $patientName = $invoices[0]['patient'] ?? '';
        // Remind the biller this was sent, then notify the patient.
        if ($sendMail($authUser, $note, xlt('Bill/Collect'), '', '0', $authUser, $authUser, $portalUser, $patientName, 'New', '0') != 1) {
            return false;
        }
        $sendMail($portalUser, $note, xlt('Bill/Collect'), '', '0', $authUser, $authUser, $portalUser, $patientName, 'New', '0');
        return true;
    }
}
