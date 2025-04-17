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
use Carecoordination\Controller\EncountermanagerController;
use Carecoordination\Model\CcdaGenerator;
use Carecoordination\Model\CcdaServiceConnectionException;
use Carecoordination\Model\EncounterccdadispatchTable;
use DOMDocument;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Exception;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Http\StatusCode;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
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

    protected $document_type;

    protected $components;

    protected $date_options;

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
        $this->patient_id = $request->getQuery('pid');
        $this->encounter_id = $request->getQuery('encounter');
        $combination = $request->getQuery('combination');
        $this->sections = $request->getQuery('sections');
        $sent_by = $request->getQuery('sent_by');
        $send = $request->getQuery('send') ?: 0;
        $view = $request->getQuery('view') ?: 0;
        $emr_transfer = $request->getQuery('emr_transfer') ?: 0;
        $this->recipients = $request->getQuery('recipient');
        $this->params = $request->getQuery('param');
        $this->referral_reason = $request->getQuery('referral_reason');
        $this->components = $request->getQuery('components') ?: $this->params('components');
        $downloadccda = $this->params('downloadccda');
        $downloadqrda = $this->params('downloadqrda');
        $downloadqrda3 = $this->params('downloadqrda3');
        $this->latest_ccda = $request->getQuery('latest_ccda') ?: $this->params('latest_ccda');
        $hie_hook = $request->getQuery('hiehook') || 0;
        $this->document_type = $request->getPost('downloadformat_type') ?? $request->getQuery('downloadformat_type');

        // Date Range format.
        $date_start = !empty($this->getRequest()->getPost('form_date_from') ?? null) ? date('Ymd', strtotime($this->getRequest()->getPost('form_date_from'))) : null;
        $date_end = !empty($this->getRequest()->getPost('form_date_to') ?? null) ? date('Ymd', strtotime($this->getRequest()->getPost('form_date_to'))) : null;
        $filter_content = !empty($this->getRequest()->getPost('form_filter_content') ?? null);
        $this->date_options = [
            'date_start' => $date_start,
            'date_end' => $date_end,
            'filter_content' => $filter_content
        ];

        // QRDA I user view html version
        if ($this->getRequest()->getQuery('doctype') === 'qrda') {
            $xmlController = new QrdaReportController();
            $document = $xmlController->getCategoryIReport($combination, '', 'html');
            echo $document;
            exit;
        }

        // QRDA III user view html version @todo create reports html in service
        if ($this->getRequest()->getQuery('doctype') === 'qrda3') {
            $xmlController = new QrdaReportController();
            $document = $xmlController->getCategoryIIIReport($combination, '');
            echo $document;
            EventAuditLogger::instance()->newEvent("qrda3-export", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "QRDA3 view");
            exit;
        }
        // QRDA I batch selected pids download as zip.
        if ($downloadqrda === 'download_qrda') {
            $xmlController = new QrdaReportController();
            $combination = $this->params('pids');
            $pids = explode('|', $combination);
            $measures = $_REQUEST['report_measures'] ?? "";
            if (is_array($measures)) {
                if (empty($measures[0])) {
                    $measures = ''; // defaults to all current one per patient.
                } elseif (($measures[0] ?? null) == 'all') {
                    $measures = 'all'; // defaults to all current measures per patient.
                }
            }
            $xmlController->downloadQrdaIAsZip($pids, $measures, 'xml');
            exit;
        }
        // QRDA III batch selected pids download as zip.
        if ($downloadqrda3 === 'download_qrda3') {
            $xmlController = new QrdaReportController();
            $combination = $this->params('pids');
            $pids = explode('|', $combination);
            $measures = $_REQUEST['report_measures_cat3'] ?? "";
            if (is_array($measures)) {
                if (empty($measures[0])) {
                    $measures = ''; // defaults to all current one per patient.
                } elseif (($measures[0] ?? null) == 'all') {
                    $measures = 'all'; // defaults to all current measures per patient.
                }
            }
            $xmlController->downloadQrdaIII($pids, $measures);
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

        try {
            $ccdaGenerator = new CcdaGenerator($this->getEncounterccdadispatchTable());
            if (!empty($combination)) {
                $arr = explode('|', $combination);
                foreach ($arr as $row) {
                    $arr = explode('_', $row);
                    $this->patient_id = $arr[0];
                    $this->encounter_id = (($arr[1] ?? '') > 0 ? $arr[1] : null);
                    if ($this->latest_ccda) {
                        $this->encounter_id = $this->getEncounterccdadispatchTable()->getLatestEncounter($this->patient_id);
                    }
                    $result = $ccdaGenerator->generate(
                        $this->patient_id,
                        $this->encounter_id,
                        $sent_by,
                        $send,
                        $view,
                        $emr_transfer,
                        $this->components,
                        $this->sections,
                        $this->recipients,
                        $this->params,
                        $this->document_type,
                        $this->referral_reason,
                        $this->date_options
                    );
                    $content = $result->getContent();
                    unset($result); // clear out our memory here as $content is a big string
                    if (!$view) {
                        if ($hie_hook) {
                            echo $content;
                        } else {
                            echo $this->listenerObject::z_xlt("Queued for Transfer");
                        }
                    }
                }

                // split content if unstructured is included from service.
                $unstructured = "";
                if (substr_count($content, '</ClinicalDocument>') === 2) {
                    $d = explode('</ClinicalDocument>', $content);
                    $content = $d[0] . '</ClinicalDocument>';
                    $unstructured = $d[1] . '</ClinicalDocument>';
                }

                if ($view && !$downloadccda) {
                    $xml = simplexml_load_string($content);
                    $xsl = new DOMDocument();
                    // cda.xsl is self-contained with bootstrap and jquery.
                    // cda-web.xsl when used, is for referencing styles from internet.
                    $xsl->load(__DIR__ . '/../../../../../public/xsl/cda.xsl');
                    $proc = new XSLTProcessor();
                    $proc->importStyleSheet($xsl); // attach the xsl rules
                    $outputFile = sys_get_temp_dir() . '/out_' . time() . '.html';
                    $proc->transformToURI($xml, $outputFile);

                    $htmlContent = file_get_contents($outputFile);
                    $result = unlink($outputFile); // remove the file so we don't have PHI left around on the filesystem
                    if (!$result) {
                        (new SystemLogger())->errorLogCaller("Failed to unlink temporary CDA output on hard drive. This could expose PHI and needs to be investigated.", ['filename' => $outputFile]);
                    }
                    echo $htmlContent;
                }

                if ($downloadccda) {
                    $pids = $this->params('pids') ?? $combination;
                    // TODO: this appears to be the only place this is used.  Looks at removing this action and bringing it into this controller
                    // no sense in having this forward piece at all...
                    $this->forward()->dispatch(EncountermanagerController::class, array('action' => 'downloadall', 'pids' => $pids, 'document_type' => $this->document_type));
                } else {
                    die;
                }
            } else {
                // oddly we send an empty string for our components here if there is no combination,
                // I don't know how this is even valid as the ccda node service fails if there is no encounters section in the component.
                // Probably should nullFlavor encounter section in generator and still render document. Looking into sjp
                $result = $ccdaGenerator->generate(
                    $this->patient_id,
                    $this->encounter_id,
                    $sent_by,
                    $send,
                    $view,
                    $emr_transfer,
                    '',
                    $this->sections,
                    $this->recipients,
                    $this->params,
                    $this->document_type,
                    $this->referral_reason,
                    $this->date_options
                );
                $content = $result->getContent();
                unset($result);
                echo $content;
                die;
            }
        } catch (CcdaServiceConnectionException $exception) {
            http_response_code(StatusCode::INTERNAL_SERVER_ERROR);
            echo xlt("Failed to connect to ccdaservice. Verify your environment is setup correctly by following the instructions in the ccdaservice's Readme file");
            (new SystemLogger())->errorLogCaller("Connection error with ccda service", ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
            die();
        }

        try {
            ob_clean();
            if (!empty($_POST['sent_by_app'] ?? '')) {
                echo $content;
                exit;
            }
            if (empty($downloadccda)) {
                $practice_filename = "CCDA_{$this->patient_id}.xml";
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=" . $practice_filename);
                header("Content-Type: application/download");
                header("Content-Transfer-Encoding: binary");
                echo $content;
            }
            exit;
        } catch (Exception $e) {
            die($e->getMessage());
        }
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
