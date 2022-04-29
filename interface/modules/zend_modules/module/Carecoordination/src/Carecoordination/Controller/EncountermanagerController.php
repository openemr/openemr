<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncountermanagerController.php
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
use Carecoordination\Model\EncountermanagerTable;
use DOMDocument;
use Laminas\Filter\Compress\Zip;
use Laminas\Hydrator\Exception\RuntimeException;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use XSLTProcessor;

class EncountermanagerController extends AbstractActionController
{
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
        $pid = $request->getPost('form_pid', null);
        $encounter = $request->getPost('form_encounter', null);
        $status = $request->getPost('form_status', null);

        if (!$pid && !$encounter && !$status) {
            $fromDate = $request->getPost('form_date_from', null) ? $this->CommonPlugin()->date_format($request->getPost('form_date_from', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d', strtotime(date('Ymd')) - (86400 * 7 * 52 * 3));
            $toDate = $request->getPost('form_date_to', null) ? $this->CommonPlugin()->date_format($request->getPost('form_date_to', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d', strtotime('2022-12-31')); // TODO remove date adjustment production
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

        $downloadccda = $request->getPost('downloadccda') ?: $request->getQuery()->downloadccda;
        $downloadqrda = $request->getPost('downloadqrda') ?: $request->getQuery()->downloadqrda;
        $downloadqrda3 = $request->getPost('downloadqrda3') ?: $request->getQuery()->downloadqrda3;
        $latest_ccda = $request->getPost('latestccda') ?: $this->getRequest()->getQuery('latest_ccda');

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

        $index = new ViewModel(array(
            'details' => $details,
            'form_data' => $params,
            'table_obj' => $this->getEncountermanagerTable(),
            'status_details' => $status_details,
            'listenerObject' => $this->listenerObject,
            'commonplugin' => $this->CommonPlugin(),
        ));
        return $index;
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

        $content = $this->getEncountermanagerTable()->getFile($id);
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

        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    public function downloadallAction()
    {
        $pids = $this->params('pids');
        if ($pids != '') {
            $zip = new Zip();
            $parent_dir = sys_get_temp_dir() . "/CCDA_" . time();
            if (!is_dir($parent_dir)) {
                if (!mkdir($parent_dir, true) && !is_dir($parent_dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $parent_dir));
                }
                chmod($parent_dir, 0777);
            }

            $arr = explode('|', $pids);
            foreach ($arr as $row) {
                $pid = $row;
                $row = $this->getEncountermanagerTable()->getFileID($pid);
                $id = $row['id'];
                $dir = $parent_dir . "/CCDA_{$row['lname']}_{$row['fname']}/";
                $filename = "CCDA_{$row['lname']}_{$row['fname']}.xml";
                $filename_html = "CCDA_{$row['lname']}_{$row['fname']}.html";
                if (!is_dir($dir)) {
                    if (!mkdir($dir, true) && !is_dir($dir)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                    }
                    chmod($dir, 0777);
                }
                // xml version for parsing or transfer.
                $content = $this->getEncountermanagerTable()->getFile($id);
                $f2 = fopen($dir . $filename, "w");
                fwrite($f2, $content);
                fclose($f2);
                // html version for viewing
                $content = $this->buildCCDAHtml($content);
                $f2 = fopen($dir . $filename_html, "w");
                fwrite($f2, $content);
                fclose($f2);
                copy(__DIR__ . "/../../../../../public/xsl/cda.xsl", $dir . "CDA.xsl");
            }

            $zip_dir = sys_get_temp_dir() . "/";
            $zip_name = "CCDA.zip";
            $zip->setArchive($zip_dir . $zip_name);
            $zip->compress($parent_dir);

            ob_clean();
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$zip_name");
            header("Content-Type: application/download");
            header("Content-Transfer-Encoding: binary");
            readfile($zip_dir . $zip_name);

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
