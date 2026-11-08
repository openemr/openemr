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
     * The portal questionnaire document's form, with its patient and encounter, if a document holds it.
     *
     * Staff open a portal document's questionnaire from the Portal Dashboard to audit it, often
     * with no chart (or another patient's chart) open, so the session-patient check would refuse
     * it. A document keeps its embedded form's id in onsite_documents.encounter, but not which
     * form table that id is from; each form table numbers its own rows. What the document itself
     * saved says so: its template_data records the embedded frame's src (the encounterForm
     * entry), written with the document, so a later edit of its template does not change it.
     * The form must also be live and of the same patient. Every matching document then has the
     * form's patient; if any of them is locked (charted history), the form is reported locked.
     */
    public static function questionnaireDocumentForm(int $formId): ?QuestionnaireDocumentForm
    {
        if ($formId <= 0) {
            return null;
        }
        $rows = QueryUtils::fetchRecords(
            'SELECT od.pid, od.template_data, od.denial_reason, f.encounter FROM `onsite_documents` AS od'
            . " JOIN `forms` AS f ON f.form_id = od.encounter AND f.formdir = 'questionnaire_assessments'"
            . ' AND f.pid = od.pid AND f.deleted = 0'
            . ' WHERE od.encounter = ?',
            [$formId]
        );
        $found = null;
        $locked = false;
        foreach ($rows as $row) {
            $pid = self::positiveInt($row['pid'] ?? null);
            if ($pid === null || !self::embedsQuestionnaireForm($row['template_data'] ?? null)) {
                continue;
            }
            $found ??= ['pid' => $pid, 'encounter' => self::positiveInt($row['encounter'] ?? null) ?? 0];
            $locked = $locked || ($row['denial_reason'] ?? null) === DocumentTemplateService::DENIAL_STATUS_LOCKED;
        }
        return $found === null ? null : new QuestionnaireDocumentForm($found['pid'], $found['encounter'], $locked);
    }

    /** A positive integer column value (an int, or a digit string from the driver), else null. */
    private static function positiveInt(mixed $value): ?int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return is_int($int) ? $int : null;
    }

    /**
     * Did the document save a questionnaire_assessments frame? template_data is the JSON list of
     * {name, value} elements the document page saves; encounterForm is its embedded frame's src.
     */
    private static function embedsQuestionnaireForm(mixed $templateData): bool
    {
        if (!is_string($templateData) || $templateData === '') {
            return false;
        }
        $elements = json_decode($templateData, true);
        if (!is_array($elements)) {
            return false;
        }
        foreach ($elements as $element) {
            if (!is_array($element) || ($element['name'] ?? null) !== 'encounterForm' || !is_string($element['value'] ?? null)) {
                continue;
            }
            $path = parse_url($element['value'], PHP_URL_PATH);
            return is_string($path)
                && str_ends_with($path, '/interface/forms/questionnaire_assessments/questionnaire_assessments.php');
        }
        return false;
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
