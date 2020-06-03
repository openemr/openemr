<?php

/**
 * interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Controller/SyndromicsurveillanceController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Syndromicsurveillance\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Listener\Listener;

class SyndromicsurveillanceController extends AbstractActionController
{
    /**
     * @var \Syndromicsurveillance\Model\SyndromicsurveillanceTable
     */
    protected $syndromicsurveillanceTable;

    protected $listenerObject;

    public function __construct(\Syndromicsurveillance\Model\SyndromicsurveillanceTable $table)
    {
        $this->listenerObject   = new Listener();
        $this->syndromicsurveillanceTable = $table;
    }

    /*
    * Display the list of patients having ICD9 codes which are reportable
    *
    * @param    form_date_from      date        Encounter date
    * @param    form_date_to        date        Encounter date
    * @param    form_icd_codes      string      ICD9 code
    * @param    form_provider_id    integer     Selected provider id
    */
    public function indexAction()
    {
        $date_display_format = $GLOBALS['date_display_format'];
        $default_from_date = date('Y-m-d', strtotime(date('Ymd')) - (86400 * 7));
        $default_to_date = date('Y-m-d');  // be inclusive of today.
        $request        = $this->getRequest();
        $this->search   = $request->getPost('search', null);
        $fromDate       = $request->getPost('form_date_from', null) ? $this->CommonPlugin()->date_format($request->getPost('form_date_from', null), 'yyyy-mm-dd', $date_display_format) : $default_from_date;
        $toDate         = $request->getPost('form_date_to', null) ? $this->CommonPlugin()->date_format($request->getPost('form_date_to', null), 'yyyy-mm-dd', $date_display_format) : $default_to_date;
        $code_selected  = $request->getPost('form_icd_codes', null);
        $provider_selected  = $request->getPost('form_provider_id', null);

        $results        = $request->getPost('form_results', 100);
        $results        = ($results > 0) ? $results : 100;
        $current_page   = $request->getPost('form_current_page', 1);
        $end            = $current_page * $results;
        $start          = ($end - $results);
        $new_search     = $request->getPost('form_new_search', null);
        $form_sl_no     = $request->getPost('form_sl_no', 0);
        $download_hl7   = $request->getPost('download_hl7', 0);

        $params     = array(
                        'form_date_from'    => $fromDate,
                        'form_date_to'      => $toDate,
                        'form_icd_codes'    => $code_selected,
                        'form_provider_id'  => $provider_selected,
                        'results'       => $results,
                        'current_page'  => $current_page,
                        'limit_start'   => $start,
                        'limit_end'     => $end,
                        'sl_no'         => $form_sl_no,
                    );
        $params['form_icd_codes'][] = $code_selected;

        if ($new_search) {
            $count = $this->getSyndromicsurveillanceTable()->fetch_result($fromDate, $toDate, $code_selected, $provider_selected, $start, $end, 1);
        } else {
            $count = $request->getPost('form_count', $this->getSyndromicsurveillanceTable()->fetch_result($fromDate, $toDate, $code_selected, $provider_selected, $start, $end, 1));
        }

        $totalpages     = ceil($count / $results);

        $params['res_count']    = $count;
        $params['total_pages']  = $totalpages;
        if ($download_hl7) {
            $this->getSyndromicsurveillanceTable()->generate_hl7($fromDate, $toDate, $code_selected, $provider_selected, $start, $end);
        }

        $search_result  = $this->getSyndromicsurveillanceTable()->fetch_result($fromDate, $toDate, $code_selected, $provider_selected, $start, $end);

        $code_list  = $this->getSyndromicsurveillanceTable()->non_reported_codes();
        $provider   = $this->getSyndromicsurveillanceTable()->getProviderList();

        $view               =  new ViewModel(array(
            'code_list'     => $code_list,
            'provider'      => $provider,
            'result'        => $search_result,
            'form_data'     => $params,
            'table_obj'     => $this->getSyndromicsurveillanceTable(),
            'listenerObject' => $this->listenerObject,
            'commonplugin'  => $this->CommonPlugin(),
        ));
        return $view;
    }

    /**
    * Table Gateway
    *
    * @return \Syndromicsurveillance\Model\SyndromicsurveillanceTable
    */
    public function getSyndromicsurveillanceTable()
    {
        return $this->syndromicsurveillanceTable;
    }
}
