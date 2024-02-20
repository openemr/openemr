<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncountermanagerController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Riju K P <rijukp@zhservices.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>_
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Controller;

use Application\Listener\Listener;
use Carecoordination\Model\CcdaDocumentTemplateOids;
use Carecoordination\Model\CcdaGlobalsConfiguration;
use Carecoordination\Model\CcdaUserPreferencesTransformer;
use Carecoordination\Model\EncountermanagerTable;
use DOMDocument;
use Laminas\Filter\Compress\Zip;
use Laminas\Hydrator\Exception\RuntimeException;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Cqm\QrdaControllers\QrdaReportController;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\PractitionerService;
use OpenEMR\Services\Qrda\QrdaReportService;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;
use Symfony\Component\EventDispatcher\GenericEvent;
use XSLTProcessor;

class EncountermanagerController extends AbstractActionController
{
    // TODO: is there a better place for this?  These are the values from the applications/sendto/sendto.phtml for
    // the document types.  We should probably extract these into a model somewhere...
    const VALID_CCDA_DOCUMENT_TYPES = ['ccd', 'referral', 'toc', 'careplan', 'unstructured'];

    const DEFAULT_DATE_SEARCH_TYPE = "encounter";
    const DATE_SEARCH_TYPE_PATIENT_CREATION = "patient_date_created";
    /**
     * @var EncountermanagerTable
     */
    protected $encountermanagerTable;
    protected $listenerObject;

    public function __construct(EncountermanagerTable $table)
    {
        $this->encountermanagerTable = $table;
        $this->listenerObject = new Listener();
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $fromDate = $request->getPost('form_date_from', null);
        $fromDate = $this->CommonPlugin()->date_format($fromDate, 'yyyy-mm-dd', $GLOBALS['date_display_format']);
        $toDate = $request->getPost('form_date_to', null);
        $toDate = $this->CommonPlugin()->date_format($toDate, 'yyyy-mm-dd', $GLOBALS['date_display_format']);
        // encounter_date
        // patient_date_created

        $form_search_type_date = $request->getPost('form_search_type_date', "encounter");
        $form_provider_id = $request->getPost("form_provider_id", null);
        $form_billing_facility_id = $request->getPost("form_billing_facility_id", null);
        $pid = $request->getPost('form_pid', null);
        $encounter = $request->getPost('form_encounter', null);
        $status = $request->getPost('form_status', null);

        if (!$pid && !$encounter && !$status) {
            $fromDate = $request->getPost('form_date_from', null) ? $this->CommonPlugin()->date_format($request->getPost('form_date_from', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d', strtotime("-3 months", $fromDate));
            $toDate = $request->getPost('form_date_to', null) ? $this->CommonPlugin()->date_format($request->getPost('form_date_to', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d');
        }

        $results = $request->getPost('form_results', 500);
        $results = ($results > 0) ? $results : 500;
        $current_page = $request->getPost('form_current_page', 1);
        $expand_all = $request->getPost('form_expand_all', 0);
        $select_all = $request->getPost('form_select_all', 0);
        $end = $current_page * $results;
        $start = ($end - $results);
        $new_search = $request->getPost('form_new_search', null);
        $form_sl_no = $request->getPost('form_sl_no', 0);
        $form_measures = $request->getPost('form_measures', null);

        $downloadccda = $request->getPost('downloadccda') ?: $request->getQuery()->downloadccda;
        $downloadqrda = $request->getPost('downloadqrda') ?: $request->getQuery()->downloadqrda;
        $downloadqrda3 = $request->getPost('downloadqrda3') ?: $request->getQuery()->downloadqrda3;
        $latest_ccda = $request->getPost('latestccda') ?: $this->getRequest()->getQuery('latest_ccda');
        $reportController = new QrdaReportController();
        $reportService = new QrdaReportService();
        $measures = $reportController->reportMeasures;
        $m_resolved = $reportService->resolveMeasuresPath($measures);
        foreach ($m_resolved as $k => $m) {
            $measures[$k]['measure_path'] = $m;
        }
        if (($downloadccda == 'download_ccda') || ($downloadqrda == 'download_qrda') || ($downloadqrda3 == 'download_qrda3')) {
            $pids = '';
            if ($request->getQuery('pid_ccda')) {
                $pid = $request->getQuery('pid_ccda');
                if ($pid != '') {
                    $combination = $pid;
                }
            } else {
                $combination = $request->getPost('ccda_pid');
            }

            for ($i = 0, $iMax = count($combination ?? []); $i < $iMax; $i++) {
                if ($i == ((count($combination ?? [])) - 1)) {
                    if ($combination == $pid) {
                        $pids = $pid;
                    } else {
                        $pids .= $combination[$i];
                    }
                } else {
                    $pids .= $combination[$i] . '|';
                }
            }
            $components = $request->getPost('components') ? $request->getPost('components') : $request->getQuery()->components;
            $send_params = array(
                'action' => 'index',
                'pids' => $pids,
                'view' => 1,
                'downloadccda' => $downloadccda,
                'components' => $components,
                'latest_ccda' => $latest_ccda,
                'form_date_from' => $fromDate,
                'form_date_to' => $toDate
            );
            if ($downloadqrda == 'download_qrda') {
                $send_params = array(
                    'action' => 'index',
                    'pids' => $pids,
                    'view' => 1,
                    'downloadqrda' => $downloadqrda
                );
            }
            if ($downloadqrda3 == 'download_qrda3') {
                $send_params = array(
                    'action' => 'index',
                    'pids' => $pids,
                    'view' => 1,
                    'downloadqrda3' => $downloadqrda3
                );
            }
            $this->forward()->dispatch(EncounterccdadispatchController::class, $send_params);
        }
        // view
        $params = array(
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'pid' => $pid,
            'encounter' => $encounter,
            'status' => $status,
            'results' => $results,
            'current_page' => $current_page,
            'limit_start' => $start,
            'limit_end' => $end,
            'select_all' => $select_all,
            'expand_all' => $expand_all,
            'sl_no' => $form_sl_no,
            'measures' => $form_measures,
            'search_type_date' => $form_search_type_date,
            'provider_id' => $form_provider_id,
            "billing_facility_id" => $form_billing_facility_id
        );
        if ($new_search) {
            $count = $this->getEncountermanagerTable()->getEncounters($params, 1);
        } else {
            $count = $request->getPost('form_count', $this->getEncountermanagerTable()->getEncounters($params, 1));
        }

        $totalpages = ceil($count / $results);

        $details = $this->getEncountermanagerTable()->getEncounters($params);
        $status_details = $this->getEncountermanagerTable()->getStatus($this->getEncountermanagerTable()->getEncounters($params));

        $params['res_count'] = $count;
        $params['total_pages'] = $totalpages;

        $layout = $this->layout();
        $layout->setTemplate('carecoordination/layout/encountermanager');

        $practitionerService = new PractitionerService();
        $practitioners = ProcessingResult::extractDataArray($practitionerService->getAll()) ?? [];

        $facilityService = new FacilityService();
        $billingLocations = $facilityService->getAllBillingLocations();

        $index = new ViewModel(array(
            'details' => $details,
            'form_data' => $params,
            'current_measures' => $measures,
            'table_obj' => $this->getEncountermanagerTable(),
            'status_details' => $status_details,
            'listenerObject' => $this->listenerObject,
            'commonplugin' => $this->CommonPlugin(),
            'providers' => $practitioners,
            'billing_facilities' => $billingLocations
        ));
        return $index;
    }

    /**
     * Action handle for previewing a ccda document.  Given the id of a document in
     * @return ViewModel
     */
    public function previewDocumentAction()
    {

        $request = $this->getRequest();
        $docId = $request->getQuery("docId");

        $document = new \Document($docId);
        try {
            $twig = new TwigContainer(null, $GLOBALS['kernel']);
            // can_access will check session if no params are passed.
            if (!$document->can_access()) {
                echo $twig->getTwig()->render("templates/error/400.html.twig", ['statusCode' => 401, 'errorMessage' => 'Access Denied']);
                exit;
            } else if ($document->is_deleted()) {
                echo $twig->getTwig()->render("templates/error/404.html.twig");
                exit;
            }

            $content = $document->get_data();
            if (empty($content)) {
                echo $twig->getTwig()->render("templates/error/404.html.twig");
                exit;
            }
            $content = $document->get_data();

            $ccdaGlobalsConfiguration = new CcdaGlobalsConfiguration();
            $ccdaUserPreferencesTransformer = new CcdaUserPreferencesTransformer(
                $ccdaGlobalsConfiguration->getMaxSections(),
                $ccdaGlobalsConfiguration->getSectionDisplayOrder()
            );
            $updatedContent = $ccdaUserPreferencesTransformer->transform($content);

            // time to use our stylesheets
            // TODO: @adunsulag we need to put this transformation process into its own class that we can reuse
            $stylesheet = dirname(__FILE__) . "/../../../../../public/xsl/cda.xsl";

            if (!file_exists($stylesheet)) {
                throw new \RuntimeException("Could not find stylesheet file at location: " . $stylesheet);
            }
            $xmlDom = new DOMDocument();
            $xmlDom->loadXML($updatedContent);
            $ss = new DOMDocument();
            $ss->load($stylesheet);
            $proc = new XSLTProcessor();
            $proc->importStylesheet($ss);
            $updatedContent = $proc->transformToXml($xmlDom);
            echo $updatedContent;
        } catch (\Exception $exception) {
            echo "Failed to generate preview for docId " . text($docId);
            (new SystemLogger())->errorLogCaller(
                "Failed to generate preview for ccda document",
                ['docId' => $docId, 'message' => $exception, 'trace' => $exception->getTraceAsString()]
            );
        }
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    public function buildCCDAHtml($content)
    {
        return $this->getEncountermanagerTable()->getCcdaAsHTML($content);
    }

    public function downloadAction()
    {
        $id = $this->getRequest()->getQuery('id');
        $dir = sys_get_temp_dir() . "/CCDA_$id/";
        $filename = "CCDA_$id.xml";
        $filename_html = "CCDA_$id.html";

        if (!is_dir($dir)) {
            if (!mkdir($dir, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
            chmod($dir, 0777);
        }

        $zip_dir = sys_get_temp_dir() . "/";
        $zip_name = "CCDA_$id.zip";

        $ccdaDocument = new \Document($id);
        $content = $ccdaDocument->get_data();
        $f = fopen($dir . $filename, "w");
        fwrite($f, $content);
        fclose($f);
        // html version for viewing
        $content = $this->buildCCDAHtml($content);
        $f = fopen($dir . $filename_html, "w");
        fwrite($f, $content);
        fclose($f);

        copy(__DIR__ . "/../../../../../public/xsl/cda.xsl", $dir . "CDA.xsl");

        $zip = new Zip();
        $zip->setArchive($zip_dir . $zip_name);
        $zip->compress($dir);

        ob_clean();
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$zip_name");
        header("Content-Type: application/download");
        header("Content-Transfer-Encoding: binary");
        readfile($zip_dir . $zip_name);

        // we need to unlink both the directory and the zip file once are done... as its a security hazard
        // to have these files just hanging around in a tmp folder
        unlink($zip_dir . $zip_name);

        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    public function downloadallAction()
    {
        $pids = $this->params('pids');
        $document_type = $this->params('document_type') ?? '';
        if ($pids != '') {
            $zip = new Zip();
            $parent_dir = sys_get_temp_dir() . "/CCDA_Patient_Documents_" . time();
            if (!is_dir($parent_dir)) {
                if (!mkdir($parent_dir, true) && !is_dir($parent_dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $parent_dir));
                }
                chmod($parent_dir, 0777);
            }

            $dir = $parent_dir . "/";
            $arr = explode('|', $pids);
            foreach ($arr as $row) {
                $pid = $row;
                $ids = $this->getEncountermanagerTable()->getFileID($pid, 2);
                $doc_type = $document_type;
                foreach ($ids as $row_inner) {
                    $id = $row_inner['id'];
                    // xml version for parsing or transfer.
                    $ccdaDocuments = \Document::getDocumentsForForeignReferenceId('ccda', $id);
                    $content = !empty($ccdaDocuments) ? $ccdaDocuments[0]->get_data() : ""; // nothing here to export
                    // From header oid for an unstructured document.
                    // used if the auto create patient document export is on in ccda service.
                    // else document_type is correctly set if purposely sent as unstructured from CCM
                    if (stripos($content, '2.16.840.1.113883.10.20.22.1.10') > 0) {
                        $doc_type = 'unstructured';
                    }
                    /* let's not have a dir per patient for now! though we'll keep for awhile.
                     * $dir = $parent_dir . "/CCDA_{$row_inner['lname']}_{$row_inner['fname']}/";*/
                    $filename = "CCDA_{$row_inner['lname']}_{$row_inner['fname']}";
                    if (!empty($doc_type) && in_array($doc_type, self::VALID_CCDA_DOCUMENT_TYPES)) {
                        $filename .= "_" . $doc_type;
                    }
                    $filename .= "_" . date("Y_m_d_His"); // ensure somewhat unique
                    $filename_html = $filename . ".html";
                    $filename .= ".xml";
                    if (!is_dir($dir)) {
                        if (!mkdir($dir, true) && !is_dir($dir)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                        }
                        chmod($dir, 0777);
                    }
                    $f2 = fopen($dir . $filename, "w");
                    fwrite($f2, $content);
                    fclose($f2);
                    if ($doc_type != 'unstructured') {
                        // html version for viewing
                        $content = $this->buildCCDAHtml($content);
                        $f2 = fopen($dir . $filename_html, "w");
                        fwrite($f2, $content);
                        fclose($f2);
                    }
                }
                copy(__DIR__ . "/../../../../../public/xsl/cda.xsl", $dir . "CDA.xsl");
            }

            $zip_dir = sys_get_temp_dir() . "/";
            $zip_name = "CCDA";
            // since we are sending this out to the filesystem we need to whitelist these document types so that we don't
            // get any kind of filesystem injection attack here.
            if (!empty($document_type) && in_array($document_type, self::VALID_CCDA_DOCUMENT_TYPES)) {
                $zip_name .= "_All";
            }
            $zip_name .= "_" . date("Y_m_d_His") . ".zip";
            $zip->setArchive($zip_dir . $zip_name);
            $zip->compress($parent_dir);

            ob_clean();
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$zip_name");
            header("Content-Type: application/download");
            header("Content-Transfer-Encoding: binary");
            readfile($zip_dir . $zip_name);

            // we need to unlink both the directory and the zip file once are done... as its a security hazard
            // to have these files just hanging around in a tmp folder
            unlink($zip_dir . $zip_name);

            $view = new ViewModel();
            $view->setTerminal(true);
            return $view;
        } else {
            // we return just empty Json, otherwise it triggers an error if we don't return some kind of HTTP response.
            $view = new JsonModel();
            $view->setTerminal(true);
            return $view;
        }
    }

    // note this gets called from the frontend javascript (see public/js/application/sendTo.js::send()
    public function transmitCCDAction()
    {
        $combination = $this->getRequest()->getQuery('combination');
        $recipients = $this->getRequest()->getQuery('recipients');
        $xml_type = $this->getRequest()->getQuery('xml_type');
        $result = $this->getEncountermanagerTable()->transmitCcdToRecipients(array("ccda_combination" => $combination, "recipients" => $recipients, "xml_type" => $xml_type));
        // need to make sure we escape this since we are escaping this into html
        echo text($result);
        return $this->response;
    }

    /**
     * Table Gateway
     *
     * @return EncountermanagerTable
     */
    public function getEncountermanagerTable()
    {
        return $this->encountermanagerTable;
    }
}
