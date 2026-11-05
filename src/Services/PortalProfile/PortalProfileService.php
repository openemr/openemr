<?php

/**
 * The patient portal profile: the demographics the portal's profile form shows and edits,
 * mapped between `patient_data` columns and the camelCase names its JSON API and page
 * scripts use.
 *
 * Two shapes, kept as the Phreeze models returned them:
 * - a record (`api/patient/{id}`): the 53 editable fields plus the review `note`;
 * - a profile row (`api/patientdata`, the profile page): 91 keys, where only those same 53
 *   fields carry values and the rest are always null.
 * Values read from the database are strings or null, as the API has always returned them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PortalProfile;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientService;
use OpenEMR\Validators\Portal\FieldRule;
use OpenEMR\Validators\Portal\FieldRuleValidator;

/**
 * @phpstan-type ProfileRecord array<string, mixed>
 */
final readonly class PortalProfileService
{
    /** camelCase field => `patient_data` column, in the order the API returns them. */
    public const FIELDS = [
        'id' => 'id',
        'title' => 'title',
        'language' => 'language',
        'financial' => 'financial',
        'fname' => 'fname',
        'lname' => 'lname',
        'mname' => 'mname',
        'dob' => 'DOB',
        'street' => 'street',
        'postalCode' => 'postal_code',
        'city' => 'city',
        'state' => 'state',
        'countryCode' => 'country_code',
        'driversLicense' => 'drivers_license',
        'ss' => 'ss',
        'occupation' => 'occupation',
        'phoneHome' => 'phone_home',
        'phoneBiz' => 'phone_biz',
        'phoneContact' => 'phone_contact',
        'phoneCell' => 'phone_cell',
        'pharmacyId' => 'pharmacy_id',
        'status' => 'status',
        'contactRelationship' => 'contact_relationship',
        'date' => 'date',
        'sex' => 'sex',
        'referrer' => 'referrer',
        'referrerid' => 'referrerID',
        'providerid' => 'providerID',
        'refProviderid' => 'ref_providerID',
        'email' => 'email',
        'emailDirect' => 'email_direct',
        'ethnoracial' => 'ethnoracial',
        'race' => 'race',
        'ethnicity' => 'ethnicity',
        'religion' => 'religion',
        'familySize' => 'family_size',
        'pubpid' => 'pubpid',
        'pid' => 'pid',
        'hipaaMail' => 'hipaa_mail',
        'hipaaVoice' => 'hipaa_voice',
        'hipaaNotice' => 'hipaa_notice',
        'hipaaMessage' => 'hipaa_message',
        'hipaaAllowsms' => 'hipaa_allowsms',
        'hipaaAllowemail' => 'hipaa_allowemail',
        'regdate' => 'regdate',
        'mothersname' => 'mothersname',
        'guardiansname' => 'guardiansname',
        'allowImmRegUse' => 'allow_imm_reg_use',
        'allowImmInfoShare' => 'allow_imm_info_share',
        'allowHealthInfoEx' => 'allow_health_info_ex',
        'allowPatientPortal' => 'allow_patient_portal',
        'careTeam' => 'care_team_provider',
        'county' => 'county',
    ];

    /** Every key of a profile row, in order. Only the keys in FIELDS ever carry a value. */
    public const PROFILE_KEYS = [
        'id', 'title', 'language', 'financial', 'fname', 'lname', 'mname', 'dob', 'street',
        'postalCode', 'city', 'state', 'countryCode', 'driversLicense', 'ss', 'occupation',
        'phoneHome', 'phoneBiz', 'phoneContact', 'phoneCell', 'pharmacyId', 'status',
        'contactRelationship', 'date', 'sex', 'referrer', 'referrerid', 'providerid',
        'refProviderid', 'email', 'emailDirect', 'ethnoracial', 'race', 'ethnicity', 'religion',
        'interpreter', 'migrantseasonal', 'familySize', 'monthlyIncome', 'billingNote', 'homeless',
        'financialReview', 'pubpid', 'pid', 'genericname1', 'genericval1', 'genericname2',
        'genericval2', 'hipaaMail', 'hipaaVoice', 'hipaaNotice', 'hipaaMessage', 'hipaaAllowsms',
        'hipaaAllowemail', 'squad', 'fitness', 'referralSource', 'usertext1', 'usertext2',
        'usertext3', 'usertext4', 'usertext5', 'usertext6', 'usertext7', 'usertext8', 'userlist1',
        'userlist2', 'userlist3', 'userlist4', 'userlist5', 'userlist6', 'userlist7', 'pricelevel',
        'regdate', 'contrastart', 'completedAd', 'adReviewed', 'vfc', 'mothersname',
        'guardiansname', 'allowImmRegUse', 'allowImmInfoShare', 'allowHealthInfoEx',
        'allowPatientPortal', 'deceasedDate', 'deceasedReason', 'soapImportStatus',
        'cmsportalLogin', 'careTeam', 'county', 'industry',
    ];

    public function __construct(private PatientService $patients = new PatientService())
    {
    }

    /**
     * Field errors for a record about to be saved, keyed by camelCase field; empty when valid.
     * The rules are the old Phreeze field map's: sizes and numeric columns.
     *
     * @param ProfileRecord $record
     * @param bool $newChart the record is a registration, which gets its pid when the chart is
     *     created, so it has none yet
     * @return array<string, string>
     */
    public static function validate(array $record, bool $newChart = false): array
    {
        $rules = [];
        foreach (array_keys(self::FIELDS) as $field) {
            $rules[$field] = match ($field) {
                'id' => FieldRule::number(20, optional: true),
                'pid' => FieldRule::number(20, optional: $newChart),
                'pharmacyId', 'providerid', 'refProviderid', 'careTeam' => FieldRule::number(11),
                'dob', 'date', 'regdate', 'occupation' => FieldRule::text(),
                'religion', 'county' => FieldRule::text(40),
                'hipaaMail', 'hipaaVoice', 'hipaaNotice', 'hipaaAllowsms', 'hipaaAllowemail',
                'allowImmRegUse', 'allowImmInfoShare', 'allowHealthInfoEx', 'allowPatientPortal' => FieldRule::text(3),
                'hipaaMessage' => FieldRule::text(20),
                default => FieldRule::text(255),
            };
        }

        return FieldRuleValidator::validate($rules, $record);
    }

    /**
     * The profile row for a patient: the profile form's fields, as `api/patientdata` and the
     * profile page take them. Registration (pid 0) gets a row of empty fields.
     *
     * @return ProfileRecord
     */
    public function profileRow(int $pid): array
    {
        $row = array_fill_keys(self::PROFILE_KEYS, null);
        if ($pid <= 0) {
            return $row;
        }
        $record = $this->findBy('pid', $pid);

        return $record === null ? [] : array_merge($row, $record);
    }

    /**
     * A patient's record by `patient_data.id`, as `api/patient/{id}` returns it.
     *
     * @return ProfileRecord|null
     */
    public function record(int $id): ?array
    {
        $record = $this->findBy('id', $id);

        return $record === null ? null : ['note' => null] + $record;
    }

    /**
     * Save a record's fields to the chart through PatientService, which stamps the update and
     * tells the patient-updated listeners. Returns the saved record.
     *
     * @param ProfileRecord $record
     * @return ProfileRecord
     */
    public function save(array $record): array
    {
        $columns = self::columns($record);
        unset($columns['id']);
        if ($this->patients->databaseUpdate($columns) === false) {
            throw new \RuntimeException('The patient profile was not saved');
        }
        $id = filter_var($record['id'] ?? null, FILTER_VALIDATE_INT);
        if (!is_int($id)) {
            throw new \InvalidArgumentException('A patient record id is required');
        }

        return $this->record($id) ?? throw new \RuntimeException('The saved patient profile was not found');
    }

    /**
     * Create the chart for a self-registered patient through PatientService, which picks the
     * pid and the public id and tells the patient-created listeners.
     *
     * @param ProfileRecord $record the profile form's fields; `id`, `pid` and `pubpid` are ignored
     * @return ProfileRecord the new record
     */
    public function register(array $record): array
    {
        $columns = self::columns($record);
        unset($columns['id'], $columns['pid'], $columns['pubpid']);
        $id = self::insertedRecordId($this->patients->databaseInsert($columns));

        return $this->record($id) ?? throw new \RuntimeException('The new patient chart was not found');
    }

    /**
     * The new `patient_data.id` from PatientService::databaseInsert(), which returns the
     * inserted fields (its docblock says int|false), or false when the insert failed.
     */
    private static function insertedRecordId(mixed $created): int
    {
        $id = is_array($created) ? filter_var($created['id'] ?? null, FILTER_VALIDATE_INT) : false;
        if (!is_int($id) || $id <= 0) {
            throw new \RuntimeException('The patient chart was not created');
        }

        return $id;
    }

    /**
     * Copy the named fields from a request body onto a record, as the Phreeze controllers did:
     * a field the body leaves out keeps its value, and the date fields are normalised (`dob`
     * and `regdate` to Y-m-d, `date` to Y-m-d H:i:s; an empty date becomes the epoch).
     *
     * @param ProfileRecord $record
     * @param list<string> $fields camelCase fields the request may set
     * @return ProfileRecord
     */
    public static function applyRequest(array $record, \stdClass $body, array $fields): array
    {
        foreach ($fields as $field) {
            $value = property_exists($body, $field) ? $body->$field : ($record[$field] ?? null);
            $record[$field] = match ($field) {
                'dob', 'regdate' => date('Y-m-d', self::timestamp($value)),
                'date' => date('Y-m-d H:i:s', self::timestamp($value)),
                default => $value,
            };
        }

        return $record;
    }

    /**
     * A record's fields keyed by their `patient_data` column, as the profile review stores them.
     *
     * @param ProfileRecord $record
     * @return array<string, mixed>
     */
    public static function columns(array $record): array
    {
        $columns = [];
        foreach (self::FIELDS as $field => $column) {
            $columns[$column] = $record[$field] ?? null;
        }

        return $columns;
    }

    /**
     * The profile form's containers for the DEM layout fields a portal patient may not edit:
     * fields that are not shown (`uor` 0) or that the layout excludes from the portal (`EP`).
     *
     * @return list<string> element ids such as `postalCodeInputContainer`
     */
    public function excludedFieldContainers(): array
    {
        $containers = [];
        $rows = QueryUtils::fetchRecords(
            "SELECT `field_id`, `uor`, `edit_options` FROM `layout_options` "
            . "WHERE `form_id` = 'DEM' AND (`uor` = 0 || `edit_options` > '') ORDER BY `group_id`, `seq`",
        );
        foreach ($rows as $row) {
            $fieldId = $row['field_id'] ?? '';
            $options = $row['edit_options'] ?? '';
            if (!is_string($fieldId) || !is_string($options)) {
                continue;
            }
            $uor = $row['uor'] ?? null;
            if ($uor === 0 || $uor === '0' || str_contains($options, 'EP')) {
                $camel = preg_replace_callback('/_([^_])/', static fn(array $m): string => ucfirst($m[1]), strtolower($fieldId));
                $containers[] = lcfirst((string) $camel) . 'InputContainer';
            }
        }

        return $containers;
    }

    /**
     * The providers a portal patient may choose: active, authorized portal users.
     *
     * @return list<array<mixed>> rows of `id`, `username`, `fname` and `lname`
     */
    public function portalProviders(): array
    {
        return QueryUtils::fetchRecords(
            "SELECT `id`, `username`, `fname`, `lname` FROM `users` "
            . "WHERE `authorized` = 1 AND `active` = 1 AND `portal_user` = 1 ORDER BY `lname`, `fname`",
        );
    }

    /**
     * strtotime() as the old controllers used it: anything unparsable is the epoch.
     */
    private static function timestamp(mixed $value): int
    {
        $time = is_string($value) ? strtotime($value) : false;

        return $time === false ? 0 : $time;
    }

    /**
     * @return ProfileRecord|null
     */
    private function findBy(string $column, int $value): ?array
    {
        $select = implode(', ', array_map(static fn(string $c): string => '`' . $c . '`', self::FIELDS));
        $rows = QueryUtils::fetchRecords(
            'SELECT ' . $select . ' FROM `patient_data` WHERE `' . ($column === 'id' ? 'id' : 'pid') . '` = ?',
            [$value],
        );
        if (!isset($rows[0])) {
            return null;
        }
        $record = [];
        foreach (self::FIELDS as $field => $dbColumn) {
            $v = $rows[0][$dbColumn] ?? null;
            $record[$field] = is_int($v) || is_float($v) ? (string) $v : $v;
        }

        return $record;
    }
}
