<?php

/**
 * Renders the portal/patient pages from their Twig templates in templates/portal/patient/.
 *
 * Every page gets the shared context as well as its own variables:
 * - ROOT_URL: the portal/patient base URL, with trailing slash;
 * - web_root, assets_static_relative and v_js_includes, for script and style URLs;
 * - is_portal_session: a portal patient (not staff) is using the page;
 * - software_version and current_year, for the footer.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

use Twig\Environment;

final readonly class TwigPageRenderer implements PortalPageRenderer
{
    /** Page name => template. */
    public const TEMPLATES = [
        'ProviderHome' => 'portal/patient/provider_home.html.twig',
        'PatientListView' => 'portal/patient/patient_list_view.html.twig',
        'OnsiteDocumentListView' => 'portal/patient/onsite_document_list_view.html.twig',
        'OnsiteActivityViewListView' => 'portal/patient/onsite_activity_view_list_view.html.twig',
        'DefaultError404' => 'portal/patient/error_404.html.twig',
        'DefaultErrorFatal' => 'portal/patient/error_fatal.html.twig',
    ];

    /**
     * @param array<string, mixed> $context the shared context every page gets
     */
    public function __construct(private Environment $twig, private array $context)
    {
    }

    /**
     * Renders a named portal page with the shared context; an unknown page name is a programming error.
     */
    public function render(string $page, array $vars = []): string
    {
        $template = self::TEMPLATES[$page] ?? throw new \InvalidArgumentException('No portal page ' . $page);

        return $this->twig->render($template, $vars + $this->context);
    }
}
