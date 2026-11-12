<?php

/**
 * Wires the questionnaire administration screens and picks the one a request asked for.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super;

use GuzzleHttp\Client;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;
use OpenEMR\Services\FHIR\Questionnaire\LhcFormToFhirConverter;
use OpenEMR\Services\Globals\CollapsedSections;
use OpenEMR\Services\Questionnaire\Import\LoincFormClient;
use OpenEMR\Services\Questionnaire\Import\QuestionnaireImportService;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityLookups;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;
use OpenEMR\Services\Questionnaire\QuestionnaireRuntimeAssets;
use OpenEMR\Services\QuestionnaireService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class QuestionnaireAdminEntryPoint
{
    private const IMPORT_VIEWS = ['import', 'preview'];
    private const LOINC_NOTE_HIDDEN = '3';

    /**
     * Build the screen the request asks for and answer it.
     *
     * view=import and view=preview go to the importer; anything else is the availability manager.
     */
    public function handle(Request $request): Response
    {
        $availability = new QuestionnaireAvailabilityService();
        $lookups = new QuestionnaireAvailabilityLookups(new ClientRepository());
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $twig = ServiceContainer::getTwig();
        $logger = ServiceContainer::getLogger();

        if (!in_array($request->query->getString('view'), self::IMPORT_VIEWS, true)) {
            return (new QuestionnaireAvailabilityController(
                $availability,
                $lookups,
                $twig,
                $session,
                $logger,
                CollapsedSections::forCurrentUser(QuestionnaireAvailabilityController::SECTIONS),
            ))->dispatchAction($request);
        }

        $globals = OEGlobalsBag::getInstance();

        return (new QuestionnaireImportController(
            new QuestionnaireImportService(new QuestionnaireService(), $availability, $lookups, new DocumentTemplateService()),
            new LoincFormClient(new Client()),
            new LhcFormToFhirConverter(),
            $twig,
            $session,
            $logger,
            (new QuestionnaireRuntimeAssets($globals))->describe(),
            $globals->getBoolean('portal_onsite_two_enable'),
            $globals->getString('questionnaire_display_LOINCnote') !== self::LOINC_NOTE_HIDDEN,
            CollapsedSections::forCurrentUser(QuestionnaireImportController::SECTIONS),
        ))->dispatchAction($request);
    }
}
