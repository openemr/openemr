<?php

/**
 * Admin User Management Service — read, create, update, and deactivate operations for admin user endpoints.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

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
    /** @var list<string> Database column names that can be updated via PUT. */
    public const UPDATABLE_FIELDS = [
        'fname', 'lname', 'mname', 'suffix', 'email', 'authorized',
        'facility_id', 'billing_facility_id', 'npi', 'taxonomy', 'specialty',
        'federaltaxid', 'state_license_number', 'federaldrugid', 'upin',
        'calendar', 'portal_user', 'active',
    ];

    private readonly UserValidator $userValidator;

    public function __construct()
    {
        parent::__construct();
        $this->toggleSensitiveFields(['username']);
        $this->userValidator = new UserValidator();
    }

    /**
     * @inheritDoc
     * Adds authorized to the base column list.
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
     * Routes through searchUsers() so the detail endpoint returns the same
     * column set and enrichment as the list endpoint.
     *
     * @param string $uuid UUID string
     * @return ProcessingResult
     */
    public function getOneByUuid(string $uuid): ProcessingResult
    {
        if (!UuidRegistry::isValidStringUUID($uuid)) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages(['uuid' => ['Invalid UUID format']]);
            return $processingResult;
        }
        return $this->searchUsers(['uuid' => UuidRegistry::uuidToBytes($uuid)]);
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

        // Validate facility IDs exist before creating the user
        if ($facilityId !== '') {
            $facility = QueryUtils::querySingleRow("SELECT id FROM facility WHERE id = ?", [$facilityId]);
            if ($facility === false) {
                $processingResult->setValidationMessages(['facility_id' => ['Facility does not exist']]);
                return $processingResult;
            }
        }
        if ($billingFacilityId !== '') {
            $billingFacility = QueryUtils::querySingleRow("SELECT id FROM facility WHERE id = ?", [$billingFacilityId]);
            if ($billingFacility === false) {
                $processingResult->setValidationMessages(['billing_facility_id' => ['Billing facility does not exist']]);
                return $processingResult;
            }
        }

        // Structured user data for AuthUtils::updatePassword() parameterized INSERT
        $userData = [
            'username' => $username,
            'password' => 'NoLongerUsed',
            'fname' => $fname,
            'mname' => $mname,
            'lname' => $lname,
            'suffix' => $suffix,
            'email' => $email,
            'federaltaxid' => $federaltaxid,
            'state_license_number' => $stateLicenseNumber,
            'authorized' => $authorized,
            'federaldrugid' => $federaldrugid,
            'upin' => $upin,
            'npi' => $npi,
            'taxonomy' => $taxonomy,
            'facility_id' => $facilityId,
            'billing_facility_id' => $billingFacilityId,
            'specialty' => $specialty,
            'calendar' => $calendar,
            'portal_user' => $portalUser,
        ];

        // AuthUtils::updatePassword() verifies the admin password and executes the INSERT
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $adminPass = self::strVal($data['admin_password'] ?? '');
        $authUtils = new AuthUtils();

        // Wrap all DB writes in a transaction so a failure in any step
        // (UUID, facility, groups, ACL) rolls back the entire user creation.
        // Note: using manual transaction methods because inTransaction() closure
        // is incompatible with AuthUtils::updatePassword() by-reference parameters
        // in the CI environment.
        QueryUtils::startTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
        try {
            $success = $authUtils->updatePassword(
                $session->get('authUserID'),
                0,
                $adminPass,
                $password,
                true,
                $userData,
                $username
            );

            if (!$success) {
                QueryUtils::rollbackTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
                $rawError = $authUtils->getErrorMessage();
                $errorMsg = is_string($rawError) && $rawError !== '' ? $rawError : 'User creation failed';
                $field = match (true) {
                    str_contains($errorMsg, 'Incorrect password') => 'admin_password',
                    str_contains($errorMsg, 'not long enough'),
                    str_contains($errorMsg, 'Empty Password') => 'password',
                    str_contains($errorMsg, 'existing username') => 'username',
                    default => 'admin_password',
                };
                $processingResult->setValidationMessages([$field => [$errorMsg]]);
                return $processingResult;
            }

            // Always assign UUID to the newly created user
            $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();
            QueryUtils::sqlStatementThrowException("UPDATE users SET uuid = ? WHERE BINARY username = ?", [$uuid, $username]);

            // Update facility name fields (IDs were validated above)
            if ($facilityId !== '') {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE users, facility SET users.facility = facility.name WHERE facility.id = ? AND users.username = ?",
                    [$facilityId, $username]
                );
            }
            if ($billingFacilityId !== '') {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE users, facility SET users.billing_facility = facility.name WHERE facility.id = ? AND users.username = ?",
                    [$billingFacilityId, $username]
                );
            }

            // Insert into groups
            QueryUtils::sqlStatementThrowException("INSERT INTO `groups` SET name = ?, user = ?", [$groupname, $username]);

            // Validate and set ACL groups
            $invalidGroups = $this->validateAccessGroups($accessGroup);
            if ($invalidGroups !== []) {
                QueryUtils::rollbackTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
                $processingResult->setValidationMessages([
                    'access_group' => ['Invalid access group(s): ' . implode(', ', $invalidGroups)],
                ]);
                return $processingResult;
            }
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

            QueryUtils::commitTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
        } catch (\Throwable $e) {
            QueryUtils::rollbackTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
            throw $e;
        }

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
     * Update an existing user.
     *
     * @param string $uuid UUID of the user to update
     * @param array<string, mixed> $data Fields to update
     * @param string $authenticatedUserUuid UUID of the currently authenticated user (self-deactivation guard)
     * @return ProcessingResult
     */
    public function updateUser(string $uuid, array $data, string $authenticatedUserUuid): ProcessingResult
    {
        if (!UuidRegistry::isValidStringUUID($uuid)) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages(['uuid' => ['Invalid UUID format']]);
            return $processingResult;
        }

        if (
            $uuid === $authenticatedUserUuid
            && array_key_exists('active', $data)
            && in_array($data['active'], [0, '0'], true)
        ) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages(['active' => ['Cannot deactivate your own account']]);
            return $processingResult;
        }

        /** @var ProcessingResult $processingResult */
        $processingResult = $this->userValidator->validate($data, BaseValidator::DATABASE_UPDATE_CONTEXT);
        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        // Resolve UUID to user record
        $uuidBytes = UuidRegistry::uuidToBytes($uuid);
        /** @var array{id: int, username: string, active: string|int}|false $user */
        $user = QueryUtils::querySingleRow(
            "SELECT `id`, `username`, `active` FROM `users` WHERE `uuid` = ?",
            [$uuidBytes]
        );
        if ($user === false) {
            return $processingResult;
        }

        $userId = (int) $user['id'];
        $username = (string) $user['username'];

        // Validate facility IDs if provided
        $facilityId = $data['facility_id'] ?? null;
        if ($facilityId !== null) {
            $facilityIdStr = trim(self::strVal($facilityId));
            if ($facilityIdStr !== '') {
                $facility = QueryUtils::querySingleRow("SELECT id FROM facility WHERE id = ?", [$facilityIdStr]);
                if ($facility === false) {
                    $processingResult->setValidationMessages(['facility_id' => ['Facility does not exist']]);
                    return $processingResult;
                }
            }
        }
        $billingFacilityId = $data['billing_facility_id'] ?? null;
        if ($billingFacilityId !== null) {
            $billingFacilityIdStr = trim(self::strVal($billingFacilityId));
            if ($billingFacilityIdStr !== '') {
                $billingFacility = QueryUtils::querySingleRow("SELECT id FROM facility WHERE id = ?", [$billingFacilityIdStr]);
                if ($billingFacility === false) {
                    $processingResult->setValidationMessages(['billing_facility_id' => ['Billing facility does not exist']]);
                    return $processingResult;
                }
            }
        }

        // Extract access_group before building SET clause (it's not a column)
        /** @var list<string>|null $accessGroup */
        $accessGroup = isset($data['access_group']) && is_array($data['access_group']) ? $data['access_group'] : null;
        $columnData = $data;
        unset($columnData['access_group']);

        QueryUtils::startTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
        try {
            // Build and execute UPDATE if there are column changes
            if ($columnData !== []) {
                $setClause = $this->buildSetClause($columnData);
                if ($setClause['set'] !== '') {
                    $sql = "UPDATE `users` SET " . $setClause['set'] . " WHERE `id` = ?";
                    $setClause['bind'][] = $userId;
                    QueryUtils::sqlStatementThrowException($sql, $setClause['bind']);
                }
            }

            // Update facility name fields if facility IDs changed
            if ($facilityId !== null) {
                $facilityIdStr = trim(self::strVal($facilityId));
                if ($facilityIdStr !== '') {
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE users, facility SET users.facility = facility.name WHERE facility.id = ? AND users.id = ?",
                        [$facilityIdStr, $userId]
                    );
                }
            }
            if ($billingFacilityId !== null) {
                $billingFacilityIdStr = trim(self::strVal($billingFacilityId));
                if ($billingFacilityIdStr !== '') {
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE users, facility SET users.billing_facility = facility.name WHERE facility.id = ? AND users.id = ?",
                        [$billingFacilityIdStr, $userId]
                    );
                }
            }

            // Validate and update ACL groups if provided
            if ($accessGroup !== null) {
                $invalidGroups = $this->validateAccessGroups($accessGroup);
                if ($invalidGroups !== []) {
                    QueryUtils::rollbackTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
                    $processingResult = new ProcessingResult();
                    $processingResult->setValidationMessages([
                        'access_group' => ['Invalid access group(s): ' . implode(', ', $invalidGroups)],
                    ]);
                    return $processingResult;
                }

                $fname = isset($data['fname']) ? trim(self::strVal($data['fname'])) : null;
                $mname = isset($data['mname']) ? trim(self::strVal($data['mname'])) : null;
                $lname = isset($data['lname']) ? trim(self::strVal($data['lname'])) : null;
                if ($fname === null || $mname === null || $lname === null) {
                    $currentUser = QueryUtils::querySingleRow("SELECT fname, mname, lname FROM users WHERE id = ?", [$userId]);
                    if (is_array($currentUser)) {
                        $rawFname = $currentUser['fname'] ?? '';
                        $rawMname = $currentUser['mname'] ?? '';
                        $rawLname = $currentUser['lname'] ?? '';
                        $fname ??= is_string($rawFname) ? $rawFname : '';
                        $mname ??= is_string($rawMname) ? $rawMname : '';
                        $lname ??= is_string($rawLname) ? $rawLname : '';
                    }
                }
                AclExtended::setUserAro($accessGroup, $username, $fname ?? '', $mname ?? '', $lname ?? '');
            }

            // Audit log
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            EventAuditLogger::getInstance()->newEvent(
                'user-update',
                self::strVal($session->get('authUser')),
                self::strVal($session->get('authProvider')),
                1,
                "User updated via API: " . $username
            );

            QueryUtils::commitTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
        } catch (\Throwable $e) {
            QueryUtils::rollbackTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
            throw $e;
        }

        // Return updated user data
        return $this->getOneByUuid($uuid);
    }

    /**
     * Deactivate a user (soft delete: set active=0).
     *
     * @param string $uuid UUID of the user to deactivate
     * @param string $authenticatedUserUuid UUID of the currently authenticated user (self-deactivation guard)
     * @return ProcessingResult
     */
    public function deactivateUser(string $uuid, string $authenticatedUserUuid): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        if (!UuidRegistry::isValidStringUUID($uuid)) {
            $processingResult->setValidationMessages(['uuid' => ['Invalid UUID format']]);
            return $processingResult;
        }

        if ($uuid === $authenticatedUserUuid) {
            $processingResult->setValidationMessages(['uuid' => ['Cannot deactivate your own account']]);
            return $processingResult;
        }

        // Resolve UUID to user record
        $uuidBytes = UuidRegistry::uuidToBytes($uuid);
        /** @var array{id: int, username: string, active: string|int}|false $user */
        $user = QueryUtils::querySingleRow(
            "SELECT `id`, `username`, `active` FROM `users` WHERE `uuid` = ?",
            [$uuidBytes]
        );
        if ($user === false) {
            return $processingResult;
        }

        $userId = (int) $user['id'];
        $username = (string) $user['username'];

        QueryUtils::sqlStatementThrowException("UPDATE `users` SET `active` = 0 WHERE `id` = ?", [$userId]);

        // Audit log
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        EventAuditLogger::getInstance()->newEvent(
            'user-deactivate',
            self::strVal($session->get('authUser')),
            self::strVal($session->get('authProvider')),
            1,
            "User deactivated via API: " . $username
        );

        $processingResult->addData([
            'uuid' => $uuid,
            'username' => $username,
            'active' => 0,
        ]);

        return $processingResult;
    }

    /**
     * Build a SQL SET clause from validated data using an explicit allowlist.
     *
     * @param array<string, mixed> $data Validated input data
     * @return array{set: string, bind: list<string|int>}
     */
    private function buildSetClause(array $data): array
    {
        $allowedFields = self::UPDATABLE_FIELDS;

        $setParts = [];
        /** @var list<string|int> $bind */
        $bind = [];
        foreach ($allowedFields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $setParts[] = "`{$field}` = ?";
            $value = $data[$field];
            if (is_int($value)) {
                $bind[] = $value;
            } else {
                $bind[] = is_string($value) ? trim($value) : self::strVal($value);
            }
        }

        return [
            'set' => implode(', ', $setParts),
            'bind' => $bind,
        ];
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

    /**
     * @param list<string> $groupTitles
     * @return list<string> Invalid titles (empty if all valid)
     */
    private function validateAccessGroups(array $groupTitles): array
    {
        /** @var array<int|string, string> $validTitleMap */
        $validTitleMap = AclExtended::aclGetGroupTitleList();
        $validTitles = array_values($validTitleMap);
        $invalid = [];
        foreach ($groupTitles as $title) {
            if (!in_array($title, $validTitles, true)) {
                $invalid[] = $title;
            }
        }
        return $invalid;
    }
}
