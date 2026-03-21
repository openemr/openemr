<?php

/**
 * Admin User Management Service — read and create operations for admin user endpoints.
 *
 * @package   OpenEMR
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Admin;

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\User\UserCreatedEvent;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\Admin\UserValidator;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class UserManagementService extends UserService
{
    private UserValidator $userValidator;

    public function __construct()
    {
        parent::__construct();
        $this->toggleSensitiveFields(['username']);
        $this->userValidator = new UserValidator();
    }

    /**
     * @inheritDoc
     * Adds username and authorized to the base column list.
     */
    protected function getSelectColumns(): string
    {
        return parent::getSelectColumns() . ", authorized";
    }

    /**
     * Search admin users with ACL group enrichment.
     *
     * @param array<string, mixed> $search Search fields (key => value)
     * @param bool $isAndCondition AND or OR for multiple criteria
     * @return ProcessingResult
     */
    public function searchUsers(array $search = [], bool $isAndCondition = true): ProcessingResult
    {
        /** @var ProcessingResult $processingResult */
        $processingResult = $this->search($search, $isAndCondition);
        /** @var list<array<string, mixed>> $enrichedData */
        $enrichedData = [];
        /** @var list<array<string, mixed>> $currentData */
        $currentData = $processingResult->getData();
        foreach ($currentData as $record) {
            $this->enrichWithAclGroups($record);
            $enrichedData[] = $record;
        }
        $processingResult->setData($enrichedData);
        return $processingResult;
    }

    /**
     * Get a single user by UUID with ACL group enrichment.
     *
     * @param string $uuid UUID string
     * @return ProcessingResult
     */
    public function getOneByUuid(string $uuid): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $user = $this->getUserByUUID($uuid);
        if ($user !== false) {
            $this->enrichWithAclGroups($user);
            $processingResult->addData($user);
        }
        return $processingResult;
    }

    /**
     * Create a new user. Extracts logic from interface/usergroup/usergroup_admin.php.
     *
     * @param array<string, mixed> $data User data from the API request
     * @return ProcessingResult
     */
    public function createUser(array $data): ProcessingResult
    {
        /** @var ProcessingResult $processingResult */
        $processingResult = $this->userValidator->validate($data, BaseValidator::DATABASE_INSERT_CONTEXT);
        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $username = trim(self::strVal($data['username'] ?? ''));
        $password = self::strVal($data['password'] ?? '');
        $fname = trim(self::strVal($data['fname'] ?? ''));
        $mname = trim(self::strVal($data['mname'] ?? ''));
        $lname = trim(self::strVal($data['lname'] ?? ''));
        $suffix = trim(self::strVal($data['suffix'] ?? ''));
        $email = trim(self::strVal($data['email'] ?? ''));
        $authorized = self::intVal($data['authorized'] ?? 0);
        $facilityId = trim(self::strVal($data['facility_id'] ?? ''));
        $billingFacilityId = trim(self::strVal($data['billing_facility_id'] ?? ''));
        $npi = trim(self::strVal($data['npi'] ?? ''));
        $taxonomy = trim(self::strVal($data['taxonomy'] ?? ''));
        $specialty = trim(self::strVal($data['specialty'] ?? ''));
        $calendar = ($data['calendar'] ?? false) ? 1 : 0;
        $portalUser = ($data['portal_user'] ?? false) ? 1 : 0;
        $federaltaxid = trim(self::strVal($data['federaltaxid'] ?? ''));
        $stateLicenseNumber = trim(self::strVal($data['state_license_number'] ?? ''));
        $federaldrugid = trim(self::strVal($data['federaldrugid'] ?? ''));
        $upin = trim(self::strVal($data['upin'] ?? ''));
        /** @var list<string> $accessGroup */
        $accessGroup = $data['access_group'] ?? [];
        $groupname = trim(self::strVal($data['groupname'] ?? 'Default'));

        // Check username uniqueness
        $existing = QueryUtils::querySingleRow("SELECT username FROM users WHERE BINARY username = ?", [$username]);
        if (is_array($existing) && isset($existing['username']) && $existing['username'] !== '') {
            $processingResult->setValidationMessages(['username' => ['Username already exists']]);
            return $processingResult;
        }

        // Build INSERT SQL — AuthUtils::updatePassword() requires raw SQL string
        // (it executes via privStatement($insert_sql, []) with no bind params)
        $insertUserSQL =
            "INSERT INTO users SET " .
            "username = '" . self::escape($username) .
            "', password = 'NoLongerUsed'" .
            ", fname = '" . self::escape($fname) .
            "', mname = '" . self::escape($mname) .
            "', lname = '" . self::escape($lname) .
            "', suffix = '" . self::escape($suffix) .
            "', email = '" . self::escape($email) .
            "', federaltaxid = '" . self::escape($federaltaxid) .
            "', state_license_number = '" . self::escape($stateLicenseNumber) .
            "', authorized = '" . self::escape((string) $authorized) .
            "', federaldrugid = '" . self::escape($federaldrugid) .
            "', upin = '" . self::escape($upin) .
            "', npi = '" . self::escape($npi) .
            "', taxonomy = '" . self::escape($taxonomy) .
            "', facility_id = '" . self::escape($facilityId) .
            "', billing_facility_id = '" . self::escape($billingFacilityId) .
            "', specialty = '" . self::escape($specialty) .
            "', calendar = '" . self::escape((string) $calendar) .
            "', portal_user = '" . self::escape((string) $portalUser) .
            "'";

        // AuthUtils::updatePassword() verifies the admin password and executes the INSERT
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $adminPass = self::strVal($data['admin_password'] ?? '');
        $authUtils = new AuthUtils();
        $success = $authUtils->updatePassword(
            $session->get('authUserID'),
            0,
            $adminPass,
            $password,
            true,
            $insertUserSQL,
            $username
        );

        if (!$success) {
            $errorMsg = $authUtils->getErrorMessage();
            if ($errorMsg === null || $errorMsg === '') {
                $errorMsg = 'User creation failed';
            }
            $processingResult->addInternalError($errorMsg);
            return $processingResult;
        }

        // Generate UUID and update facility names
        $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();

        QueryUtils::sqlStatementThrowException(
            "UPDATE users, facility SET users.facility = facility.name, users.uuid = ? WHERE facility.id = ? AND users.username = ?",
            [$uuid, $facilityId, $username]
        );

        if ($billingFacilityId !== '') {
            QueryUtils::sqlStatementThrowException(
                "UPDATE users, facility SET users.billing_facility = facility.name, users.uuid = ? WHERE facility.id = ? AND users.username = ?",
                [$uuid, $billingFacilityId, $username]
            );
        }

        // If no facility_id was provided, still set the UUID
        if ($facilityId === '') {
            QueryUtils::sqlStatementThrowException("UPDATE users SET uuid = ? WHERE BINARY username = ?", [$uuid, $username]);
        }

        // Insert into groups
        QueryUtils::sqlStatementThrowException("INSERT INTO `groups` SET name = ?, user = ?", [$groupname, $username]);

        // Set ACL groups
        AclExtended::setUserAro($accessGroup, $username, $fname, $mname, $lname);

        // Audit log
        EventAuditLogger::getInstance()->newEvent(
            'user-create',
            self::strVal($session->get('authUser')),
            self::strVal($session->get('authProvider')),
            1,
            "New user created via API: " . $username
        );

        // Dispatch event
        $eventData = $data;
        $eventData['uuid'] = UuidRegistry::uuidToString($uuid);
        $eventData['username'] = $username;
        unset($eventData['password'], $eventData['admin_password']);
        $userCreatedEvent = new UserCreatedEvent($eventData);
        OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()->dispatch(
            $userCreatedEvent,
            UserCreatedEvent::EVENT_HANDLE
        );

        // Return created user
        $processingResult->addData([
            'uuid' => UuidRegistry::uuidToString($uuid),
            'username' => $username,
            'fname' => $fname,
            'lname' => $lname,
        ]);

        return $processingResult;
    }

    /**
     * Type-safe wrapper for add_escape_custom() which returns mixed.
     */
    private static function escape(string $value): string
    {
        /** @var string $escaped */
        $escaped = add_escape_custom($value);
        return $escaped;
    }

    /**
     * Safely extract a string value from mixed data.
     */
    private static function strVal(mixed $value, string $default = ''): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        return $default;
    }

    /**
     * Safely extract an int value from mixed data.
     */
    private static function intVal(mixed $value, int $default = 0): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }
        return $default;
    }

    /**
     * Enrich a user record with ACL group titles.
     *
     * @param array<string, mixed> $record
     */
    private function enrichWithAclGroups(array &$record): void
    {
        $username = $record['username'] ?? '';
        if (is_string($username) && $username !== '') {
            $record['acl_groups'] = AclExtended::aclGetGroupTitles($username) ?? [];
        } else {
            $record['acl_groups'] = [];
        }
    }
}
