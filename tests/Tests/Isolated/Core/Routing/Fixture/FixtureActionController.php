<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core\Routing\Fixture;

use Laminas\Mvc\Controller\AbstractActionController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stub controller standing in for a real zend_module AbstractActionController.
 *
 * It returns plain values (a Symfony Response, a string) so the resolver's
 * dispatch + response-mapping mechanics can be exercised without this test
 * scaffolding naming the deprecated Laminas view models. The JsonModel and
 * ViewModel branches are proven separately: against the real Application
 * IndexController (canary) and the inherited default indexAction respectively.
 *
 * Action names deliberately avoid "index" so the parent's typed indexAction()
 * (returning a ViewModel) is not overridden — overriding it with a different
 * return type would be an LSP violation.
 */
final class FixtureActionController extends AbstractActionController
{
    public function responseAction(): Response
    {
        return new Response('from-controller', 200);
    }

    public function htmlAction(): string
    {
        return '<p>html-action</p>';
    }
}
