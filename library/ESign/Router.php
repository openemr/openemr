<?php

/**
 * Allows routing to the correct controller and method using the
 * request string
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace ESign;

use OpenEMR\Core\OEGlobalsBag;

require_once OEGlobalsBag::getInstance()->getSrcDir() . '/ESign/Abstract/Controller.php';

class Router
{
    /**
     * The only modules the ESign router may dispatch to, mapping the
     * request value (compared case-insensitively) to its canonical class
     * prefix and on-disk directory. The `module` request parameter is
     * developer-supplied in normal use (see Form_Configuration::getModule()
     * and Encounter_Configuration::getModule()); treating it as an open
     * string let a crafted value traverse out of the ESign directory into
     * an arbitrary require_once. Resolving through this closed allowlist
     * ensures no caller-controlled bytes ever reach the include path or the
     * instantiated class name.
     */
    private const MODULES = [
        'form' => 'Form',
        'encounter' => 'Encounter',
    ];

    public function route()
    {
        $request = new Request();
        $Module = self::resolveModule($request->getParam('module'));
        require_once OEGlobalsBag::getInstance()->getSrcDir() . '/ESign/' . $Module . '/Controller.php';
        $controllerClass = "\\ESign\\" . $Module . "_Controller";
        $controller = new $controllerClass($request);
        if ($controller instanceof Abstract_Controller) {
            $controller->run();
        }
    }

    /**
     * Resolve a raw `module` request value to its canonical class prefix,
     * rejecting anything outside the allowlist. Pure and dependency-free so
     * it can be unit tested in isolation.
     *
     * @param  mixed $moduleParam Raw request value (untrusted).
     * @return string             Canonical prefix, e.g. "Form" or "Encounter".
     * @throws \InvalidArgumentException When the module is not allowlisted.
     */
    public static function resolveModule($moduleParam): string
    {
        $key = strtolower(trim((string) $moduleParam));
        if (!isset(self::MODULES[$key])) {
            throw new \InvalidArgumentException('Unknown ESign module requested');
        }
        return self::MODULES[$key];
    }
}
