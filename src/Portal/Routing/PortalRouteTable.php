<?php

/**
 * The portal/patient routes. URLs, access and registration flags are the old
 * portal/patient/_app_config.php route map's, unchanged; callers outside the portal hard-code
 * these URLs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Portal\Routing;

use OpenEMR\Controllers\Portal\Patient\OnsiteDocumentController;
use OpenEMR\Controllers\Portal\Patient\PatientController;
use OpenEMR\Controllers\Portal\Patient\PortalPatientController;
use OpenEMR\Controllers\Portal\Patient\PortalReviewController;
use OpenEMR\Controllers\Portal\Patient\ProviderController;

final class PortalRouteTable
{
    /**
     * @return list<PortalRoute>
     */
    public static function routes(): array
    {
        $id = ['id' => 2];

        return [
            // The staff Portal Dashboard.
            new PortalRoute('GET', '', ProviderController::class, 'Home', RouteAccess::All),
            new PortalRoute('GET', 'provider', ProviderController::class, 'Home', RouteAccess::All),

            // The profile form and its data. Registration uses these to create the chart.
            new PortalRoute('GET', 'patientdata', PatientController::class, 'ListView', RouteAccess::All, registration: true),
            new PortalRoute('GET', 'api/patientdata', PatientController::class, 'Query', RouteAccess::All, registration: true),
            new PortalRoute('POST', 'api/patient', PatientController::class, 'Create', RouteAccess::None, registration: true),
            new PortalRoute('GET', 'api/patient/(:num)', PatientController::class, 'Read', RouteAccess::All, params: $id),
            // Commit to Chart: staff only. A portal patient's changes go to api/portalpatient for review.
            new PortalRoute('PUT', 'api/patient/(:num)', PatientController::class, 'Update', RouteAccess::Staff, params: $id),
            new PortalRoute('PUT', 'api/portalpatient/(:num)', PortalPatientController::class, 'Update', RouteAccess::All, params: $id),
            new PortalRoute('GET', 'api/portalpatient/(:num)', PortalPatientController::class, 'Read', RouteAccess::All, params: $id),

            // Portal documents.
            new PortalRoute('GET', 'onsitedocuments', OnsiteDocumentController::class, 'ListView', RouteAccess::All),
            new PortalRoute('GET', 'api/onsitedocuments', OnsiteDocumentController::class, 'Query', RouteAccess::All),
            new PortalRoute('POST', 'api/onsitedocument', OnsiteDocumentController::class, 'Create', RouteAccess::All),
            new PortalRoute('GET', 'api/onsitedocument/(:num)', OnsiteDocumentController::class, 'Read', RouteAccess::All, params: $id),
            new PortalRoute('PUT', 'api/onsitedocument/(:num)', OnsiteDocumentController::class, 'Update', RouteAccess::All, params: $id),
            new PortalRoute('DELETE', 'api/onsitedocument/(:num)', OnsiteDocumentController::class, 'Delete', RouteAccess::All, params: $id),

            // A document's review activity, from the document screen.
            new PortalRoute('GET', 'api/onsiteportalactivities', PortalReviewController::class, 'DocumentQuery', RouteAccess::All),
            new PortalRoute('POST', 'api/onsiteportalactivity', PortalReviewController::class, 'DocumentChange', RouteAccess::All),
            new PortalRoute('GET', 'api/onsiteportalactivity/(:num)', PortalReviewController::class, 'Read', RouteAccess::All, params: $id),
            new PortalRoute('PUT', 'api/onsiteportalactivity/(:num)', PortalReviewController::class, 'DocumentChange', RouteAccess::All, params: $id),

            // The staff review list.
            new PortalRoute('GET', 'onsiteactivityviews', PortalReviewController::class, 'ListView', RouteAccess::Staff),
            new PortalRoute('GET', 'api/onsiteactivityviews', PortalReviewController::class, 'Query', RouteAccess::Staff),
            new PortalRoute('GET', 'api/onsiteactivityview/(:any)', PortalReviewController::class, 'Read', RouteAccess::Staff, params: $id),

            // Any other api/ path, for every caller: the unknown-endpoint JSON error.
            new PortalRoute('GET', 'api/(:any)', ProviderController::class, 'ErrorApi404', RouteAccess::All, registration: true),
            new PortalRoute('PUT', 'api/(:any)', ProviderController::class, 'ErrorApi404', RouteAccess::All, registration: true),
            new PortalRoute('POST', 'api/(:any)', ProviderController::class, 'ErrorApi404', RouteAccess::All, registration: true),
            new PortalRoute('DELETE', 'api/(:any)', ProviderController::class, 'ErrorApi404', RouteAccess::All, registration: true),
        ];
    }
}
