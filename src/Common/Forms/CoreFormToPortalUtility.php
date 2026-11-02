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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

class CoreFormToPortalUtility
{
    /**
     * @param array|null $get
     * @return bool
     */
    public static function isPatientPortalSession(?array $get): bool
    {
        if (isset($get['isPortal']) && (int)$get['isPortal'] !== 0) {
            $session = SessionWrapperFactory::getInstance()->getPortalSession();
            if ($session->has('pid') && $session->has('patient_portal_onsite_two')) {
                // patient portal session is authenticated
                return true;
            } else {
                // user has claimed that is using patient portal, however patient portal session is not
                // authenticated, so destroy the session/cookie and kill the script
                SessionWrapperFactory::getInstance()->destroyPortalSession();
                exit;
            }
        } else {
            // not using patient portal (ie. using core OpenEMR)
            return false;
        }
    }

    /**
     * @param array|null $get
     * @return bool
     */
    public static function isPatientPortalOther(?array $get): bool
    {
        // $_GET['formOrigin']; not set is from core, 0 is from portal, 1 is from portal module, 2 is from portal dashboard
        if (($get['formOrigin'] ?? 0) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * The patient of the portal questionnaire document that holds this questionnaire form, if any.
     *
     * Staff open a portal document's questionnaire from the Portal Dashboard to audit it, often
     * with no chart (or another patient's chart) open, so the session-patient check would refuse
     * it. A document keeps its embedded form's id in onsite_documents.encounter, but not which
     * form table that id is from; each form table numbers its own rows. The document's template
     * (onsite_documents.file_path holds its id) says so: only a {Questionnaire:...} directive
     * embeds a questionnaire_assessments form. The form must also be live and of the same patient.
     */
    public static function questionnaireDocumentPatient(int $formId): ?int
    {
        if ($formId <= 0) {
            return null;
        }
        $row = QueryUtils::querySingleRow(
            'SELECT od.pid FROM `onsite_documents` AS od'
            . ' JOIN `document_templates` AS dt ON dt.id = od.file_path'
            . " AND dt.template_content LIKE '%{Questionnaire:%'"
            . " JOIN `forms` AS f ON f.form_id = od.encounter AND f.formdir = 'questionnaire_assessments'"
            . ' AND f.pid = od.pid AND f.deleted = 0'
            . ' WHERE od.encounter = ? LIMIT 1',
            [$formId]
        );
        $pid = is_array($row) ? ($row['pid'] ?? null) : null;
        return is_numeric($pid) && (int) $pid > 0 ? (int) $pid : null;
    }

    /**
     * @param bool   $patientPortalSession
     * @param int    $formid
     * @param string $formdir
     * @param int    $patientId
     * @return void
     */
    public static function confirmFormBootstrapPatient(bool $patientPortalSession, int $formid, string $formdir, int $patientId): void
    {
        if ($patientPortalSession) {
            $pidForm = sqlQuery("SELECT `pid` FROM `forms` WHERE `form_id` = ? AND `formdir` = ?", [$formid, $formdir])['pid'];
            if (empty($pidForm) || ($pidForm != $patientId)) {
                echo xlt("illegal Action");
                SessionWrapperFactory::getInstance()->destroyPortalSession();
                exit;
            }
        }
    }

    /**
     * Note that when submit is fired from javascript the onsubmit event doesn't fire.
     * So if validation needs to be called, call from message event or otherwise.
     *
     * @param bool     $patientPortalSession
     * @param bool     $patientPortalOther
     * @param string   $mode
     * @param int|null $formid
     * @return string
     */
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

    /**
     * @param int $formid
     * @return string
     */
    public static function formPatientPortalPostSave(int $formid): string
    {
        $ret = "<html><head><script>";
        $ret .= "parent.postMessage({formid:" . js_escape($formid) . "}, window.location.origin);";
        $ret .= "</script></head><body></body></html>";
        return $ret;
    }

    /**
     * @param int $formid
     * @return string
     */
    public static function formQuestionnairePortalPostSave(int $formid, $qId = '', $qrId = '', $encounter = 0): string
    {
        $ret = "<html><head><script>";
        $ret .= "parent.postMessage({formid:" . js_escape($formid) .
            ",questionnaireId:" . js_escape($qId) .
            ",responseId:" . js_escape($qrId) .
            ",formEncounter:" . js_escape($encounter) .
            "}, window.location.origin);";
        $ret .= "</script></head><body></body></html>";
        return $ret;
    }

    /**
     * @param int $id
     * @return void
     */
    public static function insertPatientPortalTemplate(int $id): void
    {
        $infoNewRegisteredForm = sqlQuery("SELECT `name`, `directory` FROM `registry` WHERE `id` = ?", [$id]);
        $patientPortalCompliant = file_exists(OEGlobalsBag::getInstance()->getKernel()->getSrcDir() . "/../interface/forms/" . $infoNewRegisteredForm['directory'] . "/patient_portal.php");
        if ($patientPortalCompliant) {
            $checkDocumentTemplate = sqlQuery("SELECT `id` FROM `document_templates` WHERE `template_name` = ?", [$infoNewRegisteredForm['name']]);
            if (empty($checkDocumentTemplate)) {
                $contentTemplate = '{EncounterForm:' . $infoNewRegisteredForm['directory'] . '}';
                (new DocumentTemplateService())->insertTemplate(-1, '', $infoNewRegisteredForm['name'], $contentTemplate, 'text/plain');
            }
        }
    }

    /**
     * @return array
     */
    public static function getListPortalCompliantEncounterForms(): array
    {
        // first get list of form directory names that are patient portal compliant
        $dirFormNames = [];
        $formDirs = scandir(OEGlobalsBag::getInstance()->getKernel()->getSrcDir() . "/../interface/forms/");
        foreach ($formDirs as $formDir) {
            if (
                !in_array($formDir, [".", "..", "LBF"]) &&
                file_exists(OEGlobalsBag::getInstance()->getKernel()->getSrcDir() . "/../interface/forms/" . $formDir . "/patient_portal.php")
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
