<?php

/**
 * The staff Portal Dashboard (portal/patient/provider), and the reply for an unknown API path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\RouteMatch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class ProviderController
{
    public function __construct(private PortalPageRenderer $pages)
    {
    }

    /**
     * The Portal Dashboard: GET provider (and the portal/patient root).
     */
    public function Home(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if (!$session->has('authUserID')) {
            // A portal patient's session reached a staff page.
            SessionUtil::portalSessionCookieDestroy();

            return new Response(
                'Shared session not allowed with Portal!!!  <br />Onsite portal is using this session<br />Destroying Onsite Portal session and logging it out........',
                Response::HTTP_OK,
                ['refresh' => '4;url= ./provider'],
            );
        }

        if (!AclMain::aclCheckCore('patientportal', 'portal')) {
            AccessDeniedHelper::deny('Unauthorized access to provider home');
        }
        $globalsBag = OEGlobalsBag::getInstance();

        return new Response($this->pages->render('ProviderHome', [
            'cpid' => $globalsBag->get('pid'),
            'cuser' => $session->get('authUserID'),
            'pdf_language' => $globalsBag->getString('pdf_language'),
        ]));
    }

    /**
     * The reply for an `api/` path no route serves.
     */
    public function ErrorApi404(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        return PortalJson::error('An unknown API endpoint was requested.');
    }
}
