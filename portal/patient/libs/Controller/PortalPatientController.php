<?php

/**
 * PortalPatientController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\PortalPatientAccessGuard;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\PatientReview\Portal\SessionReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PortalProfile\PortalProfileService;

/**
 * A portal patient's profile changes: they are sent for staff review, not saved to the chart.
 */
class PortalPatientController extends AppBasePortalController
{
    /** The fields a patient may change, sent for review with their note. */
    private const UPDATE_FIELDS = [
        'title', 'language', 'fname', 'lname', 'mname', 'dob', 'street', 'postalCode', 'city',
        'state', 'countryCode', 'driversLicense', 'ss', 'occupation', 'phoneHome', 'phoneBiz',
        'phoneContact', 'phoneCell', 'pharmacyId', 'status', 'contactRelationship', 'date', 'sex',
        'referrer', 'referrerid', 'providerid', 'refProviderid', 'email', 'emailDirect',
        'ethnoracial', 'race', 'ethnicity', 'religion', 'familySize', 'pubpid', 'hipaaMail',
        'hipaaVoice', 'hipaaNotice', 'hipaaMessage', 'hipaaAllowsms', 'hipaaAllowemail',
        'mothersname', 'guardiansname', 'allowImmRegUse', 'allowImmInfoShare', 'allowHealthInfoEx',
        'allowPatientPortal', 'careTeam', 'county', 'note',
    ];

    /**
     * Override here for any controller-specific functionality
     *
     * @inheritdocs
     */
    protected function Init()
    {
        parent::Init();
    }

    /**
     * API Method returns the patient's pending profile change, with camelCase keys, as JSON.
     */
    public function Read()
    {
        try {
            $record = self::profiles()->record(self::recordId($this->GetRouter()->GetUrlParam('id')))
                ?? throw new Exception('Not found');
            PortalPatientAccessGuard::assertCanRead($record['pid']);
            $pending = ReviewQueueFactory::portalWorkflows()->pendingProfileChange(self::recordId($record['pid']));
            $changed = $pending->payload ?? [];
            $newv = [];
            foreach ($changed as $key => $val) {
                $newv[lcfirst(ucwords((string) preg_replace_callback("/(\_(.))/", fn($match): string => strtoupper($match[2]), strtolower((string) $key))))] = $val;
            }
            $this->RenderJSON($newv, $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method sends the patient's profile changes for staff review and renders the
     * changed record as JSON. The chart is not changed.
     */
    public function Update()
    {
        try {
            $json = json_decode(RequestUtil::GetBody());
            if (!$json instanceof stdClass) {
                throw new Exception('The request body does not contain valid JSON');
            }

            $record = self::profiles()->record(self::recordId($this->GetRouter()->GetUrlParam('id')))
                ?? throw new Exception('Not found');
            PortalPatientAccessGuard::assertCanWrite($record['pid']);

            $fields = self::UPDATE_FIELDS;
            // A registration date is only kept up to date once the chart has one.
            if (!empty($record['regdate'])) {
                $fields[] = 'regdate';
            }
            $record = PortalProfileService::applyRequest($record, $json, $fields);

            $errors = PortalProfileService::validate($record);
            if ($errors !== []) {
                $this->RenderErrorJSON('Please check the form for errors', $errors);
                return;
            }
            self::SaveAudit($record);
            $this->RenderJSON($record, $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * Submit the changed profile, keyed by chart column, for staff review.
     *
     * @param array<string, mixed> $record
     */
    public static function SaveAudit(array $record): void
    {
        $changes = PortalProfileService::columns($record);
        $changes['note'] = $record['note'] ?? null;
        ReviewQueueFactory::portalWorkflows()->submitProfileChange(
            self::recordId($record['pid']),
            $changes,
            SessionReviewActor::from(SessionWrapperFactory::getInstance()->getActiveSession()),
        );
    }

    private static function recordId(mixed $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!is_int($id) || $id <= 0) {
            throw new Exception('Not found');
        }

        return $id;
    }

    private static function profiles(): PortalProfileService
    {
        return new PortalProfileService();
    }
}
