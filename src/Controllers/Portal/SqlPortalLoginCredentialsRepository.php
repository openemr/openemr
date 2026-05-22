<?php

/**
 * SqlPortalLoginCredentialsRepository — production implementation against the
 * patient_access_onsite and patient_data tables via QueryUtils.
 *
 * The SQL strings and parameter shapes are preserved verbatim from
 * portal/get_patient_info.php prior to the extraction so the refactor is behavior-
 * preserving — with one explicit deviation: the legacy script used the deprecated
 * privQuery/privStatement helpers (which bypass the SQL log to avoid recording
 * password-related queries). This repository routes those same statements through
 * QueryUtils with the `noLog` flag set, preserving the audit-omission behavior while
 * using the non-deprecated API surface.
 *
 * Raw DB rows (where the legacy ADODB layer returns every value as a string) are
 * normalized to the typed shapes declared on PortalLoginCredentialsRepository via
 * PortalLoginRowNormalizer.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\Portal;

use OpenEMR\Common\Database\QueryUtils;

class SqlPortalLoginCredentialsRepository implements PortalLoginCredentialsRepository
{
    private const FIELDS = 'id, pid, portal_pwd, portal_username, portal_login_username, portal_pwd_status';
    private const FIELDS_WITH_ONETIME = self::FIELDS . ', portal_onetime';

    /**
     * @param (callable(int): (array<string, mixed>|false|null))|null $providerLookup
     *   Resolves a provider user id to display info. The default null means provider info is not
     *   available (returns null from fetchProviderInfo). The script wires this to the legacy
     *   `getUserIDInfo` function so this class does not have to reference it directly.
     */
    public function __construct(
        private $providerLookup = null,
    ) {
    }

    public function fetchByOneTimeToken(string $token): ?array
    {
        $row = QueryUtils::querySingleRow(
            'SELECT ' . self::FIELDS_WITH_ONETIME . ' FROM patient_access_onsite WHERE BINARY portal_onetime = ?',
            [$token],
            false
        );
        return is_array($row) ? PortalLoginRowNormalizer::authRow($row, true) : null;
    }

    public function fetchByLoginUsername(string $loginUsername): ?array
    {
        $row = QueryUtils::querySingleRow(
            'SELECT ' . self::FIELDS . ' FROM patient_access_onsite WHERE portal_login_username = ?',
            [$loginUsername],
            false
        );
        return is_array($row) ? PortalLoginRowNormalizer::authRow($row, false) : null;
    }

    public function fetchByUsername(string $username): ?array
    {
        $row = QueryUtils::querySingleRow(
            'SELECT ' . self::FIELDS . ' FROM patient_access_onsite WHERE portal_username = ?',
            [$username],
            false
        );
        return is_array($row) ? PortalLoginRowNormalizer::authRow($row, false) : null;
    }

    public function clearOneTimeToken(string $token): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE patient_access_onsite SET portal_onetime=NULL WHERE BINARY portal_onetime = ?',
            [$token],
            true
        );
    }

    public function updatePasswordHash(int $id, string $newHash): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE patient_access_onsite SET portal_pwd = ? WHERE id = ?',
            [$newHash, $id],
            true
        );
    }

    public function updateLoginAndPassword(int $id, string $loginUsername, string $newHash): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE patient_access_onsite  SET portal_login_username=?, portal_pwd=?, portal_pwd_status=1 WHERE id=?',
            [$loginUsername, $newHash, $id],
            true
        );
    }

    public function fetchPatientData(int $pid): ?array
    {
        $row = QueryUtils::querySingleRow('SELECT * FROM `patient_data` WHERE `pid` = ?', [$pid]);
        if (!is_array($row) || $row === []) {
            return null;
        }
        return PortalLoginRowNormalizer::patientDataRow($row);
    }

    public function fetchProviderInfo(int $providerId): ?array
    {
        if ($providerId === 0 || $this->providerLookup === null) {
            return null;
        }
        $row = ($this->providerLookup)($providerId);
        if (!is_array($row) || $row === []) {
            return null;
        }
        return PortalLoginRowNormalizer::providerInfoRow($row);
    }
}
