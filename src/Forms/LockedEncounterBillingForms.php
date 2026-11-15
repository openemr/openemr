<?php

/**
 * Billing forms that stay available on an e-signed, locked encounter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms;

use OpenEMR\Common\Database\QueryUtils;

/**
 * With "Lock e-signed encounters and their forms" on, a signed encounter takes
 * no new forms, except these billing forms: coding and claim details often
 * follow the provider's signature, and neither is clinical documentation. The
 * main menu's Fees > Fee Sheet makes the same exception (menuActionClick in
 * tabs_view_model.js).
 */
final readonly class LockedEncounterBillingForms
{
    private const FORMS = ['fee_sheet', 'misc_billing_options'];

    /**
     * @param bool $lockIndividualForms the lock_esign_individual setting
     */
    public function __construct(private bool $lockIndividualForms)
    {
    }

    public function isBillingForm(string $formdir): bool
    {
        return in_array($formdir, self::FORMS, true);
    }

    /**
     * Whether a form is signed and locked on its own, which keeps it locked
     * whatever its encounter. This is the form's own part of
     * ESign\Form_Signable::isLocked().
     *
     * @param int $formsId the form's forms.id
     */
    public function isLockedOnItsOwn(int $formsId): bool
    {
        if (!$this->lockIndividualForms) {
            return false;
        }
        $locks = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS locks FROM esign_signatures WHERE tid = ? AND `table` = 'forms' AND is_lock = 1",
            'locks',
            [$formsId]
        );
        return is_numeric($locks) && (int) $locks > 0;
    }

    /**
     * Whether the encounter form menu leaves out Misc Billing Options. Its menu
     * item opens the session encounter's existing form (one per encounter)
     * without its id, since misc_billing_options/new.php reads the encounter
     * from the session. So it's left out when review mode shows another
     * encounter, which would open the session encounter's form, and when that
     * form is locked on its own, which would open a locked form for editing.
     * A deleted form still counts: deleting it keeps its data row, which
     * new.php opens again by encounter.
     *
     * @param ?int $displayedEncounter the encounter on screen, null if none
     * @param int $sessionEncounter the session's encounter, 0 if none
     * @param bool $reviewMode whether the page is reviewing an encounter (review_id)
     */
    public function hidesMiscBillingFromMenu(?int $displayedEncounter, int $sessionEncounter, bool $reviewMode): bool
    {
        if ($reviewMode && $displayedEncounter !== $sessionEncounter) {
            return true;
        }
        if ($sessionEncounter < 1) {
            return false;
        }
        $formsIds = QueryUtils::fetchTableColumn(
            "SELECT id FROM forms WHERE encounter = ? AND formdir = 'misc_billing_options'",
            'id',
            [$sessionEncounter]
        );
        foreach ($formsIds as $formsId) {
            if (is_numeric($formsId) && $this->isLockedOnItsOwn((int) $formsId)) {
                return true;
            }
        }
        return false;
    }
}
