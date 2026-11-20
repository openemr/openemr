<?php

/**
 * The portal profile page and the chart-side profile API: the profile form, its data, staff
 * "Commit to Chart", and self-registration.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\PortalPatientAccessGuard;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\PrincipalKind;
use OpenEMR\Portal\Routing\RouteMatch;
use OpenEMR\Services\PatientReview\Portal\SessionReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PortalProfile\PortalProfileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class PatientController
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

    public function __construct(
        private PortalPageRenderer $pages,
        private PortalProfileService $profiles = new PortalProfileService(),
    ) {
    }

    /**
     * The profile form: GET patientdata.
     */
    public function ListView(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        $query = $request->query;
        $globalsBag = OEGlobalsBag::getInstance();
        // A portal patient sees only themselves.
        $pid = $caller->patientPid() ?? $query->getInt('pid');
        // '1' only for a registering visitor, whatever the query string says: patientdata.js
        // compares it with '0', and nothing else from the request reaches the page's script.
        $register = '0';

        // Registration has no chart yet.
        if ($caller->kind === PrincipalKind::Registering) {
            $pid = 0;
            $register = '1';
        } else {
            PortalPatientAccessGuard::assertCanRead($pid);
        }
        $row = $this->profiles->profileRow($pid);
        $srcDir = $globalsBag->getString('srcdir');

        return new Response($this->pages->render('PatientListView', [
            'recid' => $query->getInt('id'),
            'cpid' => $pid,
            'cuser' => $query->get('user', 0),
            'encounter' => $query->get('enc', 0),
            'register' => $register,
            'trow' => $row,
            'exclude' => $this->profiles->excludedFieldContainers(),
            'users_list' => $this->profiles->portalProviders(),
            'select_lists' => self::selectLists($srcDir, $row),
            'datetimepicker_js' => PortalValues::scriptOutput($srcDir . '/js/xl/jquery-datetimepicker-2-5-4-alternate.js.php'),
        ]));
    }

    /**
     * The profile form's list-backed selects, by field, set to the row's values.
     *
     * @param array<string, mixed> $row the profile row
     * @return array<string, string> field => select HTML
     */
    private static function selectLists(string $srcDir, array $row): array
    {
        require_once $srcDir . '/options.inc.php';

        // field => [list id, title, extra class]
        $lists = [
            'title' => ['titles', xl('Title'), 'form-control'],
            'sex' => ['sex', xl('Sex'), ''],
            'status' => ['marital', xl('Marital Status'), 'form-control'],
            'state' => ['state', xl('State'), 'form-control'],
            'race' => ['race', xl('Race'), 'form-control'],
            'ethnicity' => ['ethnicity', xl('Ethnicity'), 'form-control'],
            'religion' => ['religious_affiliation', xl('Religion'), 'form-control'],
        ];
        $selects = [];
        foreach ($lists as $field => [$listId, $title, $class]) {
            $selects[$field] = generate_select_list($field, $listId, $row[$field] ?? null, $title, 'Unassigned', $class);
        }

        return $selects;
    }

    /**
     * The profile row for a patient: GET api/patientdata.
     */
    public function Query(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            // A portal patient sees only themselves.
            $pid = $caller->patientPid() ?? $request->query->getString('patientId');
            // Registration (no chart yet) gets one row of empty fields.
            if ($caller->kind === PrincipalKind::Registering) {
                $rows = [$this->profiles->profileRow(0)];
            } else {
                PortalPatientAccessGuard::assertCanRead($pid);
                $pid = filter_var($pid, FILTER_VALIDATE_INT);
                $row = is_int($pid) && $pid > 0 ? $this->profiles->profileRow($pid) : [];
                $rows = $row === [] ? [] : [$row];
            }

            return PortalJson::ok([
                'orderBy' => $request->query->getString('orderBy'),
                'orderDesc' => $request->query->getString('orderDesc') !== '',
                'rows' => $rows,
                'totalResults' => count($rows),
                'totalPages' => 1,
                'pageSize' => count($rows),
                'currentPage' => 1,
            ]);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * A patient's record: GET api/patient/{id}.
     */
    public function Read(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $record = $this->profiles->record(PortalValues::positiveId($route->param('id')))
                ?? throw new \RuntimeException('Not found');
            PortalPatientAccessGuard::assertCanRead($record['pid']);

            return PortalJson::ok($record);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * Create the chart for a self-registering patient: POST api/patient.
     */
    public function Create(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            $json = PortalJson::body($request->getContent());
            if ($session->get('pid') !== true && $session->get('register') !== true) {
                throw new \RuntimeException('Unauthorized');
            }
            foreach (['fnameRegistration', 'lnameRegistration', 'dobRegistration', 'emailRegistration', 'token_id_holder'] as $required) {
                if (!PortalValues::isFilled($session->get($required))) {
                    throw new \RuntimeException('Something went wrong');
                }
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

            // The chart gets its pid when it is created.
            $errors = PortalProfileService::validate($record, newChart: true);
            if ($errors !== []) {
                return PortalJson::error('Please check the form for errors', $errors);
            }

            $patient = $this->profiles->register($record);
            // Later registration steps (such as insurance) find the new chart through the holder.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `verify_email` SET `pid_holder` = ? WHERE `id` = ?",
                [$patient['pid'], $session->get('token_id_holder')],
                true,
            );

            return PortalJson::ok($patient);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * Save a profile to the chart ("Commit to Chart"): PUT api/patient/{id}. Staff only; a portal
     * patient's changes go to api/portalpatient for review.
     */
    public function Update(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $json = PortalJson::body($request->getContent());
            $record = $this->profiles->record(PortalValues::positiveId($route->param('id')))
                ?? throw new \RuntimeException('Not found');
            PortalPatientAccessGuard::assertCanWrite($record['pid']);

            $record = PortalProfileService::applyRequest($record, $json, self::UPDATE_FIELDS);
            $errors = PortalProfileService::validate($record);
            if ($errors !== []) {
                return PortalJson::error('Please check the form for errors', $errors);
            }
            $saved = $this->profiles->save($record);
            self::closeProfileReview($saved['pid'], $caller);

            return PortalJson::ok($saved);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * Close the patient's pending profile change once staff have committed it to the chart.
     */
    private static function closeProfileReview(mixed $pid, PortalPrincipal $caller): void
    {
        // The route is staff-only; this keeps a review open if anyone else ever reaches it.
        if ($caller->kind !== PrincipalKind::Staff) {
            return;
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        // The demographics are already saved. If the request cannot be closed (a concurrent close,
        // say), it stays open for staff to clear; failing here would report the save as failed
        // and invite a second save.
        try {
            ReviewQueueFactory::portalWorkflows()->completeProfileChange(PortalValues::positiveId($pid), SessionReviewActor::staff($session));
        } catch (\RuntimeException | \LogicException | DbalException $e) {
            ServiceContainer::getLogger()->error('Could not close the profile review request after saving', [
                'pid' => $pid,
                'exception' => $e,
            ]);
        }
    }
}
