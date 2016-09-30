<?php
/**
 * JavaScriptTranslationProviderController
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;
$ignoreAuth=true;
require_once("../../globals.php");
require_once($GLOBALS['fileroot'] . "/library/translation.inc.php");
require_once($GLOBALS['fileroot'] . "/interface/main/utils/http_response_helper.php");

class JavaScriptTranslationProviderController {
    public function __construct() {
        // (note this is here until we use Zend Framework)
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->get();
                break;
        }
    }

    public function get() {
        HttpResponseHelper::send(200, generate_javascript_translation_mapping(), 'TEXT');
    }
}

// Initialize self (note this is here until we use Zend Framework)
$javaScriptTranslationProviderController = new JavaScriptTranslationProviderController();
