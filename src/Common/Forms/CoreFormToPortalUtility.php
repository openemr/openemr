<?php

/**
 * CoreFormToPortalUtility class for supporting core form use in the patient portal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms;

if (!class_exists('OpenEMR\Common\Session\SessionUtil')) {
    // This CoreFormToPortalUtility class is sometimes used prior to autoloader,
    //   so need to manually bring in the SessionUtil class if not autoloaded yet.
    require_once(__DIR__ . "/../Session/SessionUtil.php");
}

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

class CoreFormToPortalUtility
{
    public static function isPatientPortalSession(?array $get): bool
    {
        if (isset($get['isPortal']) && (int)$get['isPortal'] !== 0) {
            SessionUtil::portalSessionStart();
            if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
                // patient portal session is authenticated
                return true;
            } else {
                // user has claimed that is using patient portal, however patient portal session is not
                // authenticated, so destroy the session/cookie and kill the script
                SessionUtil::portalSessionCookieDestroy();
                exit;
            }
        } else {
            // not using patient portal (ie. using core OpenEMR)
            return false;
        }
    }

    public static function isPatientPortalOther(?array $get): bool
    {
        // $_GET['formOrigin']; not set is from core, 0 is from portal, 1 is from portal module, 2 is from portal dashboard
        if (($get['formOrigin'] ?? 0) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function confirmFormBootstrapPatient(bool $patientPortalSession, int $formid, string $formdir, int $patientId): void
    {
        if ($patientPortalSession) {
            $pidForm = sqlQuery("SELECT `pid` FROM `forms` WHERE `form_id` = ? AND `formdir` = ?", [$formid, $formdir])['pid'];
            if (empty($pidForm) || ($pidForm != $patientId)) {
                echo xlt("illegal Action");
                SessionUtil::portalSessionCookieDestroy();
                exit;
            }
        }
    }

    public static function javascriptSupportPortal(bool $patientPortalSession, bool $patientPortalOther, string $mode, ?int $formid): string
    {
        $ret = '';
        if ($patientPortalSession || $patientPortalOther) {
            // code to support form in the patient portal
            $ret = '
            $(function () {
                window.addEventListener("message", (e) => {
                    if (event.origin !== window.location.origin) {
                        return false;
                    }
                    if (e.data.submitForm === true) {
                        e.preventDefault();
                        document.forms[0].submit();
                    }
            ';
            if (($mode == "update") && $patientPortalOther && !empty($formid)) {
                $ret .= 'parent.postMessage({formid:' . js_escape($formid) . '}, window.location.origin);';
            }
            $ret .= '
                });
            });
            ';
        }
        return $ret;
    }

    public static function formPatientPortalPostSave(int $formid): string
    {
        $ret = "<html><head><script>";
        $ret .= "parent.postMessage({formid:" . js_escape($formid) . "}, window.location.origin);";
        $ret .= "</script></head><body></body></html>";
        return $ret;
    }

    public static function insertPatientPortalTemplate(int $id): void
    {
        $infoNewRegisteredForm = sqlQuery("SELECT `name`, `directory` FROM `registry` WHERE `id` = ?", [$id]);
        $patientPortalCompliant = file_exists($GLOBALS['srcdir'] . "/../interface/forms/" . $infoNewRegisteredForm['directory'] . "/patient_portal.php");
        if ($patientPortalCompliant) {
            $checkDocumentTemplate = sqlQuery("SELECT `id` FROM `document_templates` WHERE `template_name` = ?", [$infoNewRegisteredForm['name']]);
            if (empty($checkDocumentTemplate)) {
                $contentTemplate = '{EncounterForm:' . $infoNewRegisteredForm['directory'] . '}';
                (new DocumentTemplateService())->insertTemplate(-1, '', $infoNewRegisteredForm['name'], $contentTemplate, 'text/plain');
            }
        }
    }

    public static function getListPortalCompliantEncounterForms(): array
    {
        // first get list of form directory names that are patient portal compliant
        $dirFormNames = [];
        $formDirs = scandir($GLOBALS['srcdir'] . "/../interface/forms/");
        foreach ($formDirs as $formDir) {
            if (
                $formDir != "." &&
                $formDir != ".." &&
                $formDir != "LBF" &&
                file_exists($GLOBALS['srcdir'] . "/../interface/forms/" . $formDir . "/patient_portal.php")
            ) {
                $dirFormNames[] = $formDir;
            }
        }

        // then remove forms that are not yet registered
        $list = [];
        $res = sqlStatement("SELECT `directory` FROM `registry`");
        while ($ret = sqlFetchArray($res)) {
            if (!empty($ret['directory']) && in_array($ret['directory'], $dirFormNames)) {
                $list[] = $ret['directory'];
            }
        }

        return $list;
    }
}
