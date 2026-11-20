<?php

/**
 * A portal patient's profile changes: they are sent for staff review, not saved to the chart.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

use OpenEMR\Common\Session\PortalPatientAccessGuard;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\RouteMatch;
use OpenEMR\Services\PatientReview\Portal\SessionReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PortalProfile\PortalProfileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class PortalPatientController
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

    public function __construct(private PortalProfileService $profiles = new PortalProfileService())
    {
    }

    /**
     * The patient's pending profile change, with camelCase keys: GET api/portalpatient/{id}.
     */
    public function Read(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $record = $this->profiles->record(PortalValues::positiveId($route->param('id')))
                ?? throw new \RuntimeException('Not found');
            PortalPatientAccessGuard::assertCanRead($record['pid']);
            $pending = ReviewQueueFactory::portalWorkflows()->pendingProfileChange(PortalValues::positiveId($record['pid']));
            $changed = $pending->payload ?? [];
            $newv = [];
            foreach ($changed as $key => $val) {
                $newv[lcfirst(ucwords((string) preg_replace_callback("/(\_(.))/", fn($match): string => strtoupper($match[2]), strtolower((string) $key))))] = $val;
            }

            return PortalJson::ok($newv);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * Send the patient's profile changes for staff review: PUT api/portalpatient/{id}. Returns
     * the changed record; the chart is not changed.
     */
    public function Update(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $json = PortalJson::body($request->getContent());
            $record = $this->profiles->record(PortalValues::positiveId($route->param('id')))
                ?? throw new \RuntimeException('Not found');
            PortalPatientAccessGuard::assertCanWrite($record['pid']);

            $fields = self::UPDATE_FIELDS;
            // A registration date is only kept up to date once the chart has one.
            if (PortalValues::isFilled($record['regdate'] ?? null)) {
                $fields[] = 'regdate';
            }
            $record = PortalProfileService::applyRequest($record, $json, $fields);

            $errors = PortalProfileService::validate($record);
            if ($errors !== []) {
                return PortalJson::error('Please check the form for errors', $errors);
            }

            $changes = PortalProfileService::columns($record);
            $changes['note'] = $record['note'] ?? null;
            ReviewQueueFactory::portalWorkflows()->submitProfileChange(
                PortalValues::positiveId($record['pid']),
                $changes,
                SessionReviewActor::from(SessionWrapperFactory::getInstance()->getActiveSession()),
            );

            return PortalJson::ok($record);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }
}
