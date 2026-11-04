<?php

/**
 * PatientController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\PortalPatientAccessGuard;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\PatientReview\Portal\SessionReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PortalProfile\PortalProfileService;

/**
 * The portal profile page and the chart-side profile API: the profile form, its data, staff
 * "Commit to Chart", and self-registration.
 */
class PatientController extends AppBasePortalController
{
    /** The fields a self-registering patient may fill in; the name, birth date and email come from the verified registration. */
    private const REGISTRATION_FIELDS = [
        'title', 'language', 'financial', 'street', 'postalCode', 'city', 'state', 'countryCode',
        'driversLicense', 'ss', 'occupation', 'phoneHome', 'phoneBiz', 'phoneContact', 'phoneCell',
        'pharmacyId', 'status', 'contactRelationship', 'date', 'sex', 'referrer', 'referrerid',
        'providerid', 'refProviderid', 'ethnoracial', 'race', 'ethnicity', 'religion', 'familySize',
        'hipaaMail', 'hipaaVoice', 'hipaaNotice', 'hipaaMessage', 'hipaaAllowsms', 'hipaaAllowemail',
        'regdate', 'mothersname', 'guardiansname', 'allowImmRegUse', 'allowImmInfoShare',
        'allowHealthInfoEx', 'allowPatientPortal', 'careTeam', 'county',
    ];

    /** The fields staff may change when committing a profile to the chart. */
    private const UPDATE_FIELDS = [
        'title', 'language', 'fname', 'lname', 'mname', 'dob', 'street', 'postalCode', 'city',
        'state', 'countryCode', 'driversLicense', 'ss', 'occupation', 'phoneHome', 'phoneBiz',
        'phoneContact', 'phoneCell', 'pharmacyId', 'status', 'contactRelationship', 'date', 'sex',
        'referrer', 'referrerid', 'providerid', 'refProviderid', 'email', 'emailDirect',
        'ethnoracial', 'race', 'ethnicity', 'religion', 'familySize', 'hipaaMail', 'hipaaVoice',
        'hipaaNotice', 'hipaaMessage', 'hipaaAllowsms', 'hipaaAllowemail', 'regdate', 'mothersname',
        'guardiansname', 'allowImmRegUse', 'allowImmInfoShare', 'allowHealthInfoEx',
        'allowPatientPortal', 'careTeam', 'county',
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
     * Displays the profile form.
     */
    public function ListView()
    {

        $rid = $pid = $user = $encounter = $register = 0;
        if (isset($_GET['id'])) {
            $rid = (int) $_GET['id'];
        }

        if (isset($_GET['pid'])) {
            $pid = (int) $_GET['pid'];
        }

        $globalsBag = OEGlobalsBag::getInstance();
        // only allow patient to see themself
        $bootstrapPid = $globalsBag->get('bootstrap_pid');
        if (!empty($bootstrapPid)) {
            $pid = $bootstrapPid;
        }

        if (isset($_GET['user'])) {
            $user = $_GET['user'];
        }

        if (isset($_GET['enc'])) {
            $encounter = $_GET['enc'];
        }

        if (isset($_GET['register'])) {
            $register = $_GET['register'];
        }

        // force register to pid of 0 and register of true
        if (!empty($globalsBag->get('bootstrap_register'))) {
            $pid = 0;
            $register = true;
        } else {
            PortalPatientAccessGuard::assertCanRead($pid);
        }
        $this->Assign('recid', $rid);
        $this->Assign('cpid', $pid);
        $this->Assign('cuser', $user);
        $this->Assign('encounter', $encounter);
        $this->Assign('register', $register);

        $profiles = self::profiles();
        $this->Assign('trow', $profiles->profileRow((int) $pid));
        $this->Assign('exclude', $profiles->excludedFieldContainers());
        $this->Assign('users_list', $profiles->portalProviders());

        // finally render the template.
        $this->Render();
    }

    /**
     * API Method returns the profile row for a patient as JSON.
     */
    public function Query()
    {
        try {
            $pid = RequestUtil::Get('patientId');
            $globalsBag = OEGlobalsBag::getInstance();
            // only allow patient to see themself
            $bootstrapPid = $globalsBag->get('bootstrap_pid');
            if (!empty($bootstrapPid)) {
                $pid = $bootstrapPid;
            }
            // Registration (no chart yet) gets one row of empty fields.
            if (!empty($globalsBag->get('bootstrap_register'))) {
                $rows = [self::profiles()->profileRow(0)];
            } else {
                PortalPatientAccessGuard::assertCanRead($pid);
                $pid = filter_var($pid, FILTER_VALIDATE_INT);
                $row = is_int($pid) && $pid > 0 ? self::profiles()->profileRow($pid) : [];
                $rows = $row === [] ? [] : [$row];
            }
            $this->RenderJSON([
                'orderBy' => RequestUtil::Get('orderBy'),
                'orderDesc' => RequestUtil::Get('orderDesc') != '',
                'rows' => $rows,
                'totalResults' => count($rows),
                'totalPages' => 1,
                'pageSize' => count($rows),
                'currentPage' => 1,
            ], $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method retrieves a single Patient record and render as JSON
     */
    public function Read()
    {
        try {
            $record = self::profiles()->record(self::recordId($this->GetRouter()->GetUrlParam('id')))
                ?? throw new Exception('Not found');
            PortalPatientAccessGuard::assertCanRead($record['pid']);
            $this->RenderJSON($record, $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method creates the chart for a self-registering patient and renders it as JSON.
     */
    public function Create()
    {
        try {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            $json = json_decode(RequestUtil::GetBody());
            if (empty($json) || !$json instanceof stdClass) {
                throw new Exception('The request body does not contain valid JSON');
            }
            if ($session->get('pid') !== true && $session->get('register') !== true) {
                throw new Exception('Unauthorized');
            }

            if (empty($session->get('fnameRegistration')) || empty($session->get('lnameRegistration')) || empty($session->get('dobRegistration')) || empty($session->get('emailRegistration')) || empty($session->get('token_id_holder'))) {
                throw new Exception('Something went wrong');
            }

            $record = PortalProfileService::applyRequest(
                array_fill_keys(array_keys(PortalProfileService::FIELDS), null)
                    + ['hipaaAllowsms' => 'NO', 'hipaaAllowemail' => 'NO'],
                $json,
                self::REGISTRATION_FIELDS,
            );
            // The verified registration supplies these, not the form.
            $record['fname'] = $session->get('fnameRegistration');
            $record['lname'] = $session->get('lnameRegistration');
            $record['mname'] = $session->get('mnameRegistration');
            $record['dob'] = $session->get('dobRegistration');
            $record['email'] = $session->get('emailRegistration');

            $errors = PortalProfileService::validate(array_diff_key($record, ['pid' => true]));
            if ($errors !== []) {
                $this->RenderErrorJSON('Please check the form for errors', $errors);
                return;
            }

            $patient = self::profiles()->register($record);
            // Later registration steps (such as insurance) find the new chart through the holder.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `verify_email` SET `pid_holder` = ? WHERE `id` = ?",
                [$patient['pid'], $session->get('token_id_holder')],
                true,
            );
            $this->RenderJSON($patient, $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method saves a profile to the chart ("Commit to Chart") and renders it as JSON.
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

            $record = PortalProfileService::applyRequest($record, $json, self::UPDATE_FIELDS);
            $errors = PortalProfileService::validate($record);
            if ($errors !== []) {
                $this->RenderErrorJSON('Please check the form for errors', $errors);
                return;
            }
            $saved = self::profiles()->save($record);
            $this->CloseAudit($saved['pid']);
            $this->RenderJSON($saved, $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * Closes the patient's pending profile edit once staff have committed it to the chart.
     */
    public function CloseAudit(mixed $pid): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        // Only staff accept a profile change; a patient saving their own record leaves it in review.
        if ($session->has('patient_portal_onsite_two')) {
            return;
        }
        // The demographics are already saved. If the request cannot be closed (a concurrent close,
        // say), it stays open for staff to clear; failing here would report the save as failed
        // and invite a second save.
        try {
            ReviewQueueFactory::portalWorkflows()->completeProfileChange(self::pid($pid), SessionReviewActor::staff($session));
        } catch (\RuntimeException | \LogicException | DbalException $e) {
            ServiceContainer::getLogger()->error('Could not close the profile review request after saving', [
                'pid' => $pid,
                'exception' => $e,
            ]);
        }
    }

    private static function pid(mixed $pid): int
    {
        $pid = filter_var($pid, FILTER_VALIDATE_INT);
        if (!is_int($pid) || $pid <= 0) {
            throw new Exception('Not found');
        }

        return $pid;
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
