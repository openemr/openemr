<?php

/**
 * Admin User Management Service — read and create operations for admin user endpoints.
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
        $calendar = self::intVal($data['calendar'] ?? 0);
        $portalUser = self::intVal($data['portal_user'] ?? 0);
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
