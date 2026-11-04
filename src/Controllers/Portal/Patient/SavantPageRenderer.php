<?php

/**
 * Renders the portal/patient pages from their Savant templates
 * (portal/patient/templates/*.tpl.php), giving each the `ROOT_URL` it expects.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

final readonly class SavantPageRenderer implements PortalPageRenderer
{
    /**
     * @param string $templatePath the templates directory
     * @param string $rootUrl the portal/patient base URL, with trailing slash
     */
    public function __construct(private string $templatePath, private string $rootUrl)
    {
    }

    public function render(string $page, array $vars = []): string
    {
        $engine = new \SavantRenderEngine($this->templatePath);
        $engine->assign('ROOT_URL', $this->rootUrl);
        foreach ($vars as $name => $value) {
            $engine->assign($name, $value);
        }
        return $engine->fetch($page);
    }
}
