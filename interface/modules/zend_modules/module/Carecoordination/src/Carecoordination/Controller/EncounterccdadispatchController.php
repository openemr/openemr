<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Riju K P <rijukp@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Controller;

use Application\Listener\Listener;
use Carecoordination\Model\EncounterccdadispatchTable;
use DOMDocument;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\System\System;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Carecoordination\Controller\EncountermanagerController;
use Exception;
use OpenEMR\Cqm\QrdaControllers\QrdaReportController;
use XSLTProcessor;

class EncounterccdadispatchController extends AbstractActionController
{
    protected $data;

    protected $patient_id;

    protected $encounter_id;

    protected $sections;

    protected $encounterccdadispatchTable;

    protected $createdtime;

    protected $listenerObject;

    protected $recipients;

    protected $params;

    protected $referral_reason;

    protected $latest_ccda;

    public function __construct(EncounterccdadispatchTable $encounterccdadispatchTable)
    {
        $this->listenerObject = new Listener();
        $this->encounterccdadispatchTable = $encounterccdadispatchTable;
    }

    public function indexAction()
    {

        global $assignedEntity;
        global $representedOrganization;

        //$assignedEntity['streetAddressLine']    = '17 Daws Rd.';
        //$assignedEntity['city']                 = 'Blue Bell';
        //$assignedEntity['state']                = 'MA';
        //$assignedEntity['postalCode']           = '02368';
        //$assignedEntity['country']              = 'US';
        //$assignedEntity['telecom']              = '5555551234';

        $representedOrganization = $this->getEncounterccdadispatchTable()->getRepresentedOrganization();

        $request = $this->getRequest();
        $this->patient_id = $this->getRequest()->getQuery('pid');
        $this->encounter_id = $this->getRequest()->getQuery('encounter');
        $combination = $this->getRequest()->getQuery('combination');
        $this->sections = $this->getRequest()->getQuery('sections');
        $sent_by = $this->getRequest()->getQuery('sent_by');
        $send = $this->getRequest()->getQuery('send') ?: 0;
        $view = $this->getRequest()->getQuery('view') ?: 0;
        $emr_transfer = $this->getRequest()->getQuery('emr_transfer') ?: 0;
        $this->recipients = $this->getRequest()->getQuery('recipient');
        $this->params = $this->getRequest()->getQuery('param');
        $this->referral_reason = $this->getRequest()->getQuery('referral_reason');
        $this->components = $this->getRequest()->getQuery('components') ?: $this->params('components');
        $downloadccda = $this->params('downloadccda');
        $downloadqrda = $this->params('downloadqrda');
        $downloadqrda3 = $this->params('downloadqrda3');
        $this->latest_ccda = $this->getRequest()->getQuery('latest_ccda') ?: $this->params('latest_ccda');
        $hie_hook = $this->getRequest()->getQuery('hiehook') || 0;

        // @TODO uncomment below for production.
        /*$qrda_options = [
            'performance_period_start' => $this->getRequest()->getQuery('form_date_from') ?? null,
            'performance_period_end' => $this->getRequest()->getQuery('form_date_to') ?? null
        ];*/
        $qrda_options = [
            'performance_period_start' => "2020-01-01 00:00:00",
            'performance_period_end' => "2020-12-31 23:59:59"
        ];

        // QRDA I user view html version
        if ($this->getRequest()->getQuery('doctype') === 'qrda') {
            $xmlController = new QrdaReportController();
            $document = $xmlController->getCategoryIReport($combination, '', 'html', $qrda_options);
            echo $document;
            exit;
        }

        // QRDA III user view html version @todo create reports html in service
        if ($this->getRequest()->getQuery('doctype') === 'qrda3') {
            $xmlController = new QrdaReportController();
            $document = $xmlController->getCategoryIIIReport($combination, '', $qrda_options);
            echo $document;
            exit;
        }
        // QRDA I batch selected pids download as zip.
        if ($downloadqrda === 'download_qrda') {
            $xmlController = new QrdaReportController();
            $combination = $this->params('pids');
            $view = $this->params('view');
            $pids = explode('|', $combination);
            $measures = $_REQUEST['report_measures'] ?? "";
            if (is_array($measures)) {
                if (empty($measures[0])) {
                    $measures = ''; // defaults to all current one per patient.
                } elseif (($measures[0] ?? null) == 'all') {
                    $measures = 'all'; // defaults to all current measures per patient.
                }
            }
            $xmlController->downloadQrdaIAsZip($pids, $measures, 'xml', $qrda_options);
            exit;
        }

        // QRDA III batch selected pids download as zip.
        if ($downloadqrda3 === 'download_qrda3') {
            $xmlController = new QrdaReportController();
            $combination = $this->params('pids');
            $view = $this->params('view');
            $pids = explode('|', $combination);
            $measures = $_REQUEST['report_measures_cat3'] ?? "";
            if (is_array($measures)) {
                if (empty($measures[0])) {
                    $measures = ''; // defaults to all current one per patient.
                } elseif (($measures[0] ?? null) == 'all') {
                    $measures = 'all'; // defaults to all current measures per patient.
                }
            }
            $xmlController->downloadQrdaIII($pids, $measures, $qrda_options);
            exit;
        }

        if ($downloadccda === 'download_ccda') {
            $combination = $this->params('pids');
            $view = $this->params('view');
        }
        // Since called outside a route(api from cdaDocumentService) we haven't any route parameters
        // so we need to get necessary parameters from post request.
        if (!empty($_POST['sent_by_app'] ?? '')) {
            $downloadccda = $this->getRequest()->getPost('downloadccda');
            if ($downloadccda === 'download_ccda') {
                $combination = $this->getRequest()->getPost('combination');
                $view = $this->getRequest()->getPost('view');
                $this->latest_ccda = $this->getRequest()->getPost('latest_ccda');
                $this->components = $this->getRequest()->getPost('components');
            }
        }

        if ($sent_by != '') {
            $_SESSION['authUserID'] = $sent_by;
        }

        if (!$this->sections) {
            $components0 = $this->getEncounterccdadispatchTable()->getCCDAComponents(0);
            foreach ($components0 as $key => $value) {
                if ($str ?? '') {
                    $str .= '|';
                } else {
                    $str = $key;
                    continue;
                }
                $str .= $key;
            }
            $this->sections = $str;
        }

        if (!$this->components) {
            $components1 = $this->getEncounterccdadispatchTable()->getCCDAComponents(1);
            foreach ($components1 as $key => $value) {
                if ($str1 ?? '') {
                    $str1 .= '|';
                } else {
                    $str1 = $key;
                    continue;
                }
                $str1 .= $key;
            }
            $this->components = $str1;
        }

        if (!empty($combination)) {
            $arr = explode('|', $combination);
            foreach ($arr as $row) {
                $arr = explode('_', $row);
                $this->patient_id = $arr[0];
                $this->encounter_id = (($arr[1] ?? '') > 0 ? $arr[1] : null);
                if ($this->latest_ccda) {
                    $this->encounter_id = $this->getEncounterccdadispatchTable()->getLatestEncounter($this->patient_id);
                }

                $this->create_data($this->patient_id, $this->encounter_id, $this->sections, $send, $this->components);
                $content = $this->socket_get($this->data);

                if ($content == 'Authentication Failure') {
                    echo $this->listenerObject::z_xlt($content);
                    die();
                }

                $content = trim($content);
                $this->getEncounterccdadispatchTable()->logCCDA($this->patient_id, $this->encounter_id, base64_encode($content), $this->createdtime, 0, $_SESSION['authUserID'], $view, $send, $emr_transfer);
                if (!$view) {
                    if ($hie_hook) {
                        echo $content;
                    } else {
                        echo $this->listenerObject::z_xlt("Queued for Transfer");
                    }
                }
            }

            if ($view && !$downloadccda) {
                $xml = simplexml_load_string($content);
                $xsl = new DOMDocument();
                // cda.xsl is self contained with bootstrap and jquery.
                // cda-web.xsl when used, is for referencing styles from internet.
                $xsl->load(__DIR__ . '/../../../../../public/xsl/cda.xsl');
                $proc = new XSLTProcessor();
                $proc->importStyleSheet($xsl); // attach the xsl rules
                $outputFile = sys_get_temp_dir() . '/out_' . time() . '.html';
                $proc->transformToURI($xml, $outputFile);

                $htmlContent = file_get_contents($outputFile);
                echo $htmlContent;
            }

            if ($downloadccda) {
                $pids = $this->params('pids') ?? $combination;
                $this->forward()->dispatch(EncountermanagerController::class, array('action' => 'downloadall', 'pids' => $pids));
            } else {
                die;
            }
        } else {
            $this->create_data($this->patient_id, $this->encounter_id, $this->sections, $send, '');
            $content = $this->socket_get($this->data);

            $content = trim($content);
            $this->getEncounterccdadispatchTable()->logCCDA($this->patient_id, $this->encounter_id, base64_encode($content), $this->createdtime, 0, $_SESSION['authUserID'], $view, $send, $emr_transfer);
            echo $content;
            die;
        }

        try {
            ob_clean();
            if (!empty($_POST['sent_by_app'] ?? '')) {
                echo $content;
                exit;
            }
            $practice_filename = "CCDA_{$this->patient_id}.xml";
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . $practice_filename);
            header("Content-Type: application/download");
            header("Content-Transfer-Encoding: binary");
            echo $content;
            exit;
        } catch (Exception $e) {
            die('SOAP Error');
        }
    }

    public function socket_get($data)
    {
        $output = "";
        $system = new System();

        // Create a TCP Stream Socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new Exception("Socket Creation Failed");
        }

        // Let's check if server is already running but suppress warning with @ operator
        $server_active = @socket_connect($socket, "localhost", "6661");

        if ($server_active === false) {
            // 1 -> Care coordination module, 2-> portal, 3 -> Both so the local service is on if it's greater than 0
            if ($GLOBALS['ccda_alt_service_enable'] > 0) { // we're local service
                $path = $GLOBALS['fileroot'] . "/ccdaservice";
                if (IS_WINDOWS) {
                    // node server is quite with errors(hidden process) so we'll do redirect of tty
                    // to generally Windows/Temp.
                    $redirect_errors = " > " .
                        $system->escapeshellcmd($GLOBALS['temporary_files_dir'] . "/ccdaserver.log") . " 2>&1";
                    $cmd = $system->escapeshellcmd("node " . $path . "/serveccda.js") . $redirect_errors;
                    $pipeHandle = popen("start /B " . $cmd, "r");
                    if ($pipeHandle === false) {
                        throw new Exception("Failed to start local ccdaservice");
                    }
                    if (pclose($pipeHandle) === -1) {
                        error_log("Failed to close pipehandle for ccdaservice");
                    }
                } else {
                    $command = 'nodejs';
                    if (!$system->command_exists($command)) {
                        if ($system->command_exists('node')) {
                            // older or custom Ubuntu systems that have node rather than nodejs command
                            $command = 'node';
                        } else {
                            error_log("Node is not installed on the system.  Connection failed");
                            throw new Exception('Connection Failed.');
                        }
                    }
                    $cmd = $system->escapeshellcmd("$command " . $path . "/serveccda.js");
                    exec($cmd . " > /dev/null &");
                }
                sleep(2); // give cpu a rest
                $result = socket_connect($socket, "localhost", "6661");
                if ($result === false) { // hmm something is amiss with service. user will likely try again.
                    error_log("Failed to start and connect to local ccdaservice server on port 6661");
                    throw new Exception("Connection Failed");
                }
            } else {
                error_log("C-CDA Service is not enabled in Global Settings");
                throw new Exception("Please Enable C-CDA Alternate Service in Global Settings");
            }
        }

        $data = chr(11) . $data . chr(28) . "\r";
        // Write to socket!
        $out = socket_write($socket, $data, strlen($data));

        socket_set_nonblock($socket);
        //Read from socket!
        do {
            $line = "";
            $line = trim(socket_read($socket, 1024, PHP_NORMAL_READ));
            $output .= $line;
        } while (!empty($line) && $line !== false);

        $output = substr(trim($output), 0, strlen($output) - 1);
        // Close and return.
        socket_close($socket);
        return $output;
    }

    public function create_data($pid, $encounter, $sections, int $send = null, $components)
    {
        if (!$send) {
            $send = 0;
        }
        global $assignedEntity;
        global $representedOrganization;
        $sections_list = explode('|', $sections);
        $components_list = explode('|', $components);
        $this->createdtime = time();
        $this->data .= "<CCDA>";
        $this->data .= "<serverRoot>" . $GLOBALS['webroot'] . "</serverRoot>";
        $this->data .= "<username></username>";
        $this->data .= "<password></password>";
        $this->data .= "<hie>MyHealth</hie>";
        $this->data .= "<time>" . $this->createdtime . "</time>";
        $this->data .= "<client_id></client_id>";
        $this->data .= "<created_time>" . date('YmdHis') . "</created_time>";
        $this->data .= "<created_time_timezone>" . date('YmdHisO') . "</created_time_timezone>";
        $this->data .= "<send>" . htmlspecialchars($send, ENT_QUOTES) . "</send>";
        $this->data .= "<assignedEntity>
                <streetAddressLine>" . htmlspecialchars($assignedEntity['streetAddressLine'] ?? '', ENT_QUOTES) . "</streetAddressLine>
                <city>" . htmlspecialchars($assignedEntity['city'] ?? '', ENT_QUOTES) . "</city>
                <state>" . htmlspecialchars($assignedEntity['state'] ?? '', ENT_QUOTES) . "</state>
                <postalCode>" . htmlspecialchars($assignedEntity['postalCode'] ?? '', ENT_QUOTES) . "</postalCode>
                <country>" . htmlspecialchars($assignedEntity['country'] ?? '', ENT_QUOTES) . "</country>
            </assignedEntity>
            <telecom use='WP' value='" . htmlspecialchars($assignedEntity['telecom'] ?? '', ENT_QUOTES) . "'/>
            <representedOrganization>
                <name>" . htmlspecialchars($representedOrganization['name'] ?? '', ENT_QUOTES) . "</name>
                <telecom use='WP' value='" . htmlspecialchars($representedOrganization['telecom'] ?? '', ENT_QUOTES) . "'/>
                <streetAddressLine>" . htmlspecialchars($representedOrganization['streetAddressLine'] ?? '', ENT_QUOTES) . "</streetAddressLine>
                <city>" . htmlspecialchars($representedOrganization['city'] ?? '', ENT_QUOTES) . "</city>
                <state>" . htmlspecialchars($representedOrganization['state'] ?? '', ENT_QUOTES) . "</state>
                <postalCode>" . htmlspecialchars($representedOrganization['postalCode'] ?? '', ENT_QUOTES) . "</postalCode>
                <country>" . htmlspecialchars($representedOrganization['country'] ?? '', ENT_QUOTES) . "</country>
            </representedOrganization>";
        $this->data .= "<referral_reason><text>" . htmlspecialchars($this->referral_reason, ENT_QUOTES) . "</text></referral_reason>";

        /***************CCDA Header Information***************/
        $this->data .= $this->getEncounterccdadispatchTable()->getPatientdata($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getProviderDetails($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getAuthor($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getDataEnterer($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getInformant($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getCustodian($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getInformationRecipient($pid, $encounter, $this->recipients, $this->params);
        $this->data .= $this->getEncounterccdadispatchTable()->getLegalAuthenticator($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getAuthenticator($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getPrimaryCareProvider($pid, $encounter);
        /***************CCDA Header Information***************/

        /***************CCDA Body Information***************/
        if (in_array('encounters', $components_list)) {
            $this->data .= $this->getEncounterccdadispatchTable()->getEncounterHistory($pid, $encounter);
        }

        if (in_array('continuity_care_document', $sections_list)) {
            $this->data .= $this->getContinuityCareDocument($pid, $encounter, $components_list);
        }

        // we're sending everything anyway. document type will tell engine what to include in cda.
        $this->data .= $this->getEncounterccdadispatchTable()->getClinicalNotes($pid, $encounter);

        if (in_array('progress_note', $sections_list)) {
            $this->data .= $this->getEncounterccdadispatchTable()->getProgressNotes($pid, $encounter);
        }

        if (in_array('discharge_summary', $sections_list)) {
            $this->data .= $this->getDischargeSummary($pid, $encounter);
        }

        if (in_array('procedure_note', $sections_list)) {
            $this->data .= $this->getProcedureNotes($pid, $encounter);
        }

        if (in_array('operative_note', $sections_list)) {
            $this->data .= $this->getOperativeNotes($pid, $encounter);
        }

        if (in_array('consultation_note', $sections_list)) {
            $this->data .= $this->getConsultationNote($pid, $encounter);
        }

        if (in_array('history_physical_note', $sections_list)) {
            $this->data .= $this->getHistoryAndPhysicalNotes($pid, $encounter, $components_list);
        }

        if (in_array('unstructured_document', $sections_list)) {
            $this->data .= $this->getEncounterccdadispatchTable()->getUnstructuredDocuments($pid, $encounter);
        }

        /***************CCDA Body Information***************/

        $this->data .= "</CCDA>";
    }

    public function get_file_name($dir_source)
    {
        $tmpfile = '';
        if (is_dir($dir_source)) {
            if ($dh = opendir($dir_source)) {
                while (($file = readdir($dh)) !== false) {
                    if (filetype($dir_source . $file) == 'file') {
                        $tmpfile = $dir_source . $file;
                        chmod($tmpfile, 0777);
                    }
                }

                closedir($dh);
            }
        }

        return $tmpfile;
    }

    public function download_file($tmpfile, $practice_filename, $file_size)
    {
        ob_clean();
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . $practice_filename);
        header("Content-Type: application/download");
        header("Content-Transfer-Encoding: binary");
        readfile($tmpfile);
    }

    public function getContinuityCareDocument($pid, $encounter, $components_list)
    {
        $ccd = '';
        if (in_array('allergies', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getAllergies($pid, $encounter);
        }

        if (in_array('medications', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getMedications($pid, $encounter);
        }

        if (in_array('problems', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getProblemList($pid, $encounter);
        }

        if (in_array('procedures', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getProcedures($pid, $encounter);
        }

        if (in_array('results', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getResults($pid, $encounter);
        }

        if (in_array('immunizations', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getImmunization($pid, $encounter);
        }

        if (in_array('plan_of_care', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getPlanOfCare($pid, $encounter);
        }

        if (in_array('functional_status', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getFunctionalCognitiveStatus($pid, $encounter);
        }

        if (in_array('instructions', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getClinicalInstructions($pid, $encounter);
        }

        if (in_array('medical_devices', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getMedicalDeviceList($pid, $encounter);
        }

        if (in_array('referral', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getReferrals($pid, $encounter);
        }
        return $ccd;
    }

    public function getDischargeSummary($pid, $encounter)
    {
        $discharge_summary = '';

        $discharge_summary .= $this->getEncounterccdadispatchTable()->getHospitalCourse($pid, $encounter);
        $discharge_summary .= $this->getEncounterccdadispatchTable()->getDischargeDiagnosis($pid, $encounter);
        $discharge_summary .= $this->getEncounterccdadispatchTable()->getDischargeMedications($pid, $encounter);

        return $discharge_summary;
    }

    /*
    #***********************************************#
    #       PROCEDURE NOTES section in CCDA.        #
    #***********************************************#
    This function contains call to different sub sections like
    * Complications
    * Postprocedure Diagnosis
    * Postprocedure Description
    * Postprocedure Indications

    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * $return   string  $procedure_notes      XML which contains the details collected from the patient.
    */
    public function getProcedureNotes($pid, $encounter)
    {
        $procedure_notes = '<procedure_notes>';
        $procedure_notes .= $this->getEncounterccdadispatchTable()->getComplications($pid, $encounter);
        $procedure_notes .= $this->getEncounterccdadispatchTable()->getPostProcedureDiag($pid, $encounter);
        $procedure_notes .= $this->getEncounterccdadispatchTable()->getProcedureDescription($pid, $encounter);
        $procedure_notes .= $this->getEncounterccdadispatchTable()->getProcedureIndications($pid, $encounter);
        $procedure_notes .= '</procedure_notes>';
        return $procedure_notes;
    }

    /*
    #***********************************************#
    #       OPERATIVE NOTES section in CCDA.        #
    #***********************************************#
    This function contains call to different sub sections like
    * Anesthesia
    * Complications (already exist in the CCDA section Procedure Notes)
    * Post Operative Diagnosis
    * Pre Operative Diagnosis
    * Procedure Estimated Blood Loss
    * Procedure Findings
    * Procedure Specimens Taken
    * Procedure Description (already exist in the CCDA section Procedure Notes)


    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * $return   string  $operative_notes      XML which contains the details collected from the patient.
    */
    public function getOperativeNotes($pid, $encounter)
    {
        $operative_notes = '<operative_notes>';
        $operative_notes .= $this->getEncounterccdadispatchTable()->getAnesthesia($pid, $encounter);
        $operative_notes .= $this->getEncounterccdadispatchTable()->getPostoperativeDiag($pid, $encounter);
        $operative_notes .= $this->getEncounterccdadispatchTable()->getPreOperativeDiag($pid, $encounter);
        $operative_notes .= $this->getEncounterccdadispatchTable()->getEstimatedBloodLoss($pid, $encounter);
        $operative_notes .= $this->getEncounterccdadispatchTable()->getProcedureFindings($pid, $encounter);
        $operative_notes .= $this->getEncounterccdadispatchTable()->getProcedureSpecimensTaken($pid, $encounter);
        $operative_notes .= '</operative_notes>';
        return $operative_notes;
    }

    /*
    #***********************************************#
    #       CONSULTATION NOTES section in CCDA.     #
    #***********************************************#
    This function contains call to different sub sections like
    * History of Present Illness
    * Physical Exam

    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * $return   string  $consultation_notes      XML which contains the details collected from the patient.
    */
    public function getConsultationNote($pid, $encounter)
    {
        $consultation_notes = '';
        $consultation_notes .= "<consultation_notes>";
        $consultation_notes .= $this->getEncounterccdadispatchTable()->getHP($pid, $encounter);
        $consultation_notes .= $this->getEncounterccdadispatchTable()->getPhysicalExam($pid, $encounter);
        $consultation_notes .= "</consultation_notes>";
        return $consultation_notes;
    }

    /*
    #********************************************************#
    #       HISTORY AND PHYSICAL NOTES section in CCDA.      #
    #********************************************************#
    This function contains call to different sub sections like
    * Chief Complaint / Reason for Visit
    * Family History
    * General Status
    * History of Past Illness
    * Review of Systems
    * Social History
    * Vital Signs

    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * $return   string  $history_and_physical_notes      XML which contains the details collected from the patient.
    */

    public function getHistoryAndPhysicalNotes($pid, $encounter, $components_list)
    {
        $history_and_physical_notes = '';
        $history_and_physical_notes .= "<history_physical>";
        $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getChiefComplaint($pid, $encounter);
        $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getGeneralStatus($pid, $encounter);
        $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getHistoryOfPastIllness($pid, $encounter);
        $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getReviewOfSystems($pid, $encounter);
        if (in_array('vitals', $components_list)) {
            $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getVitals($pid, $encounter);
        }

        if (in_array('social_history', $components_list)) {
            $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getSocialHistory($pid, $encounter);
        }

        $history_and_physical_notes .= "</history_physical>";
        return $history_and_physical_notes;
    }

    /**
     * Table Gateway
     *
     * @return EncounterccdadispatchTable
     */
    public function getEncounterccdadispatchTable()
    {
        return $this->encounterccdadispatchTable;
    }

    /*
    * Automatically send CCDA to HIE if the option is enabled in care coordination module
    *
    * @param    Post Variable       combination     Value format: pid_encounter
    * @return   Redirection         Redirects to Encounterccdadispatch controller for creating the CCDA, if auto send is enabled
    */
    public function autosendAction()
    {
        $auto_send = $this->getEncounterccdadispatchTable()->getSettings('Carecoordination', 'hie_auto_send_id');
        if ($auto_send != 'yes') {
            return;
        }

        $view = new ViewModel(array(
            'combination' => $combination,
            'listenerObject' => $this->listenerObject,
        ));
        $view->setTerminal(true);
        return $this->forward()->dispatch('encounterccdadispatch', array('action' => 'index'));
    }

    /*
    * Automatically sign off the combination forms
    *
    * @param    None
    * @return   None
    */
    public function autosignoffAction()
    {
        $auto_signoff_days = $this->getEncounterccdadispatchTable()->getSettings('Carecoordination', 'hie_auto_sign_off_id');
        $str_time = ((strtotime(date('Y-m-d'))) - ($auto_signoff_days * 60 * 60 * 24));
        $date = date('Y-m-d', $str_time);

        $encounter = $this->getEncounterccdadispatchTable()->getEncounterDate($date);
        foreach ($encounter as $row) {
            $result = $this->getEncounterccdadispatchTable()->signOff($row['pid'], $row['encounter']);
        }

        $view = new ViewModel(
            array('encounter' => $result, 'listenerObject' => $this->listenerObject)
        );
        $view->setTerminal(true);
        return $view;
    }
}
