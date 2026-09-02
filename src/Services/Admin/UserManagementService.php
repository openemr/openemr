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

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Database\QueryPagination;
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
use Psr\Log\LoggerInterface;

class UserManagementService extends UserService
{
    private readonly UserValidator $userValidator;

    private readonly LoggerInterface $logger;

    /**
     * Exposes the username column, which the base user service hides, and wires the admin validator.
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        parent::__construct();
        $this->logger = $logger ?? ServiceContainer::getLogger();
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
     * @param QueryPagination|null $pagination Optional limit/offset for the list endpoint
     * @return ProcessingResult
     */
    public function searchUsers(
        array $search = [],
        bool $isAndCondition = true,
        ?QueryPagination $pagination = null
    ): ProcessingResult {
        /** @var ProcessingResult $processingResult */
        $processingResult = $this->search($search, $isAndCondition, $pagination);
        /** @var list<array<string, mixed>> $currentData */
        $currentData = $processingResult->getData();

        $usernames = [];
        foreach ($currentData as $record) {
            $username = $record['username'] ?? null;
            if (is_string($username) && $username !== '') {
                $usernames[] = $username;
            }
        }
        $aclGroupsByUsername = $this->fetchAclGroupTitles($usernames);

        /** @var list<array<string, mixed>> $enrichedData */
        $enrichedData = [];
        foreach ($currentData as $record) {
            $username = $record['username'] ?? null;
            $key = is_string($username) ? self::foldUsername($username) : '';
            $record['acl_groups'] = $aclGroupsByUsername[$key] ?? [];
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

        // Deliberately NOT a BINARY match, unlike the statements below that target the row this
        // request inserts. The ARO namespace this create will write into is case-insensitive:
        // GaclApi::get_object_id() and Gacl::acl_get_groups() both compare gacl_aro.value with a
        // plain equality compare, and gacl_aro carries UNIQUE KEY (section_value, value). A
        // case-sensitive check here therefore admits `SMITH` alongside an existing `smith`, and
        // AclExtended::setUserAro() then resolves `smith`'s ARO, renames it, drops every group
        // association it holds and installs the ones this request asked for -- rewriting a
        // different account's ACL memberships from a create-only endpoint. The legacy UI
        // (interface/usergroup/usergroup_admin.php) matches case-insensitively for the same
        // reason; this keeps the API's contract identical to it.
        $existing = QueryUtils::querySingleRow("SELECT username FROM users WHERE username = ?", [$username]);
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

        // Reject unknown ACL group titles before any write. AclExtended::setUserAro() silently
        // ignores titles that match no group, so without this a create would report 201 while
        // leaving the user with none of the memberships that were asked for.
        $invalidGroups = $this->validateAccessGroups($accessGroup);
        if ($invalidGroups !== []) {
            $processingResult->setValidationMessages([
                'access_group' => ['Invalid access group(s): ' . implode(', ', $invalidGroups)],
            ]);
            return $processingResult;
        }

        // AuthUtils::updatePassword() verifies the admin password and executes the INSERT
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $adminPass = self::strVal($data['admin_password'] ?? '');
        $authUtils = new AuthUtils();

        // Wrap the DB writes on this connection in a transaction so a failure in any step
        // (user row, UUID, facility, groups) rolls back the entire user creation.
        //
        // The ACL write cannot participate (GaclApi holds its own connection), so it is done
        // after the commit instead — see the note there.
        //
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
                // AuthUtils builds these messages with xl(), so they arrive translated. Comparing
                // against English literals would misclassify every failure on a non-English
                // install; running the same source strings through xl() here matches in any locale.
                $field = match (true) {
                    $errorMsg === xl("Incorrect password!") => 'admin_password',
                    $errorMsg === xl("Password not long enough"),
                    $errorMsg === xl("Password too long"),
                    $errorMsg === xl("Password not strong enough"),
                    $errorMsg === xl("Empty Password Not Allowed") => 'password',
                    $errorMsg === xl("Trying to create user with existing username!") => 'username',
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
                    "UPDATE users, facility SET users.facility = facility.name WHERE facility.id = ? AND BINARY users.username = ?",
                    [$facilityId, $username]
                );
            }
            if ($billingFacilityId !== '') {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE users, facility SET users.billing_facility = facility.name WHERE facility.id = ? AND BINARY users.username = ?",
                    [$billingFacilityId, $username]
                );
            }

            // Insert into groups
            QueryUtils::sqlStatementThrowException("INSERT INTO `groups` SET name = ?, user = ?", [$groupname, $username]);

            // Audit log
            EventAuditLogger::getInstance()->newEvent(
                'user-create',
                self::strVal($session->get('authUser')),
                self::strVal($session->get('authProvider')),
                1,
                "New user created via API: " . $username
            );

            QueryUtils::commitTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
        } catch (\Throwable $e) {
            QueryUtils::rollbackTransaction(); // @phpstan-ignore openemr.deprecatedSqlFunction
            throw $e;
        }

        // Moved out of the transaction deliberately. AclExtended::setUserAro() writes through
        // GaclApi, which holds its own ADODB connection, so its rows were never covered by the
        // rollback above: a failure in the audit step or the commit left gacl_aro and
        // gacl_groups_aro_map rows behind, keyed to a username whose users row had just been
        // rolled away. setUserAro() then *edits* a matching ARO rather than creating one, so the
        // next create of that username would silently inherit the stale grants.
        //
        // Running it after the commit cannot produce that state. The failure mode instead becomes
        // a user that exists with missing or partial ACL groups, which is visible in the user
        // admin UI and fails closed rather than granting anything.
        AclExtended::setUserAro($accessGroup, $username, $fname, $mname, $lname);

        // Dispatched after the commit, matching the event's documented "after a user has been
        // created" contract. Listeners do outside work (welcome mail, downstream provisioning)
        // that must not run for a creation that then fails to commit, and a throwing listener
        // must not roll back a user that is already persisted.
        //
        // The user exists at this point, so a listener failure leaves the caller with a 5xx for a
        // record that was in fact created, and a retry then fails with "Username already exists".
        // Absorbing the failure is not an option here: openemr.forbiddenCatchType (phpstan) and
        // CatchExceptionToThrowableRector (rector) between them rule out swallowing \Error and
        // \ErrorException, which is the project's deliberate position. So the failure is logged
        // with enough context to reconcile the created user, then propagates, matching the
        // handling in interface/forms/questionnaire_assessments/native_save.php.
        $eventData = $data;
        $eventData['uuid'] = UuidRegistry::uuidToString($uuid);
        $eventData['username'] = $username;
        unset($eventData['password'], $eventData['admin_password']);
        $userCreatedEvent = new UserCreatedEvent($eventData);
        try {
            OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()->dispatch(
                $userCreatedEvent,
                UserCreatedEvent::EVENT_HANDLE
            );
        } catch (\Throwable $e) {
            $this->logger->error('UserCreatedEvent listener failed after user creation', [
                'username' => $username,
                'uuid' => UuidRegistry::uuidToString($uuid),
                'exception' => $e,
            ]);
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
     * Fetch ACL group titles for a set of usernames in a single query.
     *
     * Replaces a per-record AclExtended::aclGetGroupTitles() call, which issued
     * several gacl API queries for every user in the result set. The join mirrors
     * that helper: direct (non-recursive) ARO group memberships in the `users`
     * section, with the group *name* column as the title, sorted per user.
     *
     * Matching is case-insensitive, deliberately unlike the BINARY username lookups used
     * against the `users` table. gacl resolves an ARO with a plain equality compare
     * (Gacl::acl_get_groups(), GaclApi::get_object_id()), so under the install default
     * collation `Smith` is *enforced* with `smith`'s groups; matching case-sensitively here
     * would report none and leave the privilege view disagreeing with what is enforced.
     * `gacl_aro` also carries UNIQUE KEY (section_value, value), so the two cannot hold
     * separate AROs and there is no union to guard against. A plain IN() keeps that index
     * usable, where `BINARY aro.value` is non-sargable and scans the table per list page.
     *
     * Keys are case-folded for the same reason: the ARO stores whichever spelling was
     * registered, so callers must fold the `users.username` they look up with.
     *
     * Note the two sides use different folding rules -- the IN() binds raw usernames and is
     * folded by the column collation, while the keying uses mb_strtolower(). They agree over
     * the ASCII set UserValidator admits, and the collation folds at least as much as
     * mb_strtolower() does, so the query returns a superset: a row the collation matched but
     * mb_strtolower() would not is keyed under a fold no user matches and is simply dropped.
     * That direction can only under-report, never attribute one user's groups to another.
     *
     * @param list<string> $usernames
     * @return array<string, list<string>> case-folded username => sorted group titles
     */
    private function fetchAclGroupTitles(array $usernames): array
    {
        if ($usernames === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($usernames), '?'));
        $sql = "SELECT aro.value AS username, grp.name AS group_title
                FROM gacl_aro aro
                INNER JOIN gacl_groups_aro_map map ON map.aro_id = aro.id
                INNER JOIN gacl_aro_groups grp ON grp.id = map.group_id
                WHERE aro.section_value = 'users' AND aro.value IN (" . $placeholders . ")";

        /** @var list<array<string, mixed>> $rows */
        $rows = QueryUtils::fetchRecords($sql, $usernames);

        /** @var array<string, list<string>> $titlesByUsername */
        $titlesByUsername = [];
        foreach ($rows as $row) {
            $username = $row['username'] ?? null;
            $title = $row['group_title'] ?? null;
            if (!is_string($username) || !is_string($title)) {
                continue;
            }
            $titlesByUsername[self::foldUsername($username)][] = $title;
        }

        foreach ($titlesByUsername as &$titles) {
            sort($titles);
        }
        unset($titles);

        return $titlesByUsername;
    }

    /**
     * Case-fold a username for keying ACL lookups.
     *
     * gacl matches ARO values with a plain equality compare, so the case the ARO happens to
     * be stored under need not match the `users.username` row it belongs to. Folding both
     * sides keeps the enrichment lookup in step with what gacl actually resolves.
     */
    private static function foldUsername(string $username): string
    {
        return mb_strtolower($username, 'UTF-8');
    }

    /**
     * Return the requested ACL group titles that do not match an existing group.
     *
     * @param list<string> $groupTitles
     * @return list<string> Invalid titles, empty when every title is known
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
