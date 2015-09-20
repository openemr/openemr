<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*    @author  Vinish K <vinish@zhservices.com>
* +------------------------------------------------------------------------------+
*/
namespace Syndromicsurveillance\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Listener\Listener;

class SyndromicsurveillanceController extends AbstractActionController
{
    protected $encounterccdadispatchTable;

    protected $listenerObject;
    
    public function __construct()
    {
      	$this->listenerObject	= new Listener;
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
        $request        = $this->getRequest();
        $this->search   = $request->getPost('search', null);        
        $fromDate       = $request->getPost('form_date_from', null) ? $this->CommonPlugin()->date_format($request->getPost('form_date_from', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d',strtotime(date('Ymd')) - (86400*7));		
        $toDate         = $request->getPost('form_date_to', null) ? $this->CommonPlugin()->date_format($request->getPost('form_date_to', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d');
		$code_selected  = $request->getPost('form_icd_codes', null);
        $provider_selected  = $request->getPost('form_provider_id', null);        
        
        $results        = $request->getPost('form_results', 100);
        $results        = ($results > 0) ? $results : 100;
        $current_page   = $request->getPost('form_current_page', 1);
        $end            = $current_page*$results; 
        $start          = ($end - $results);
        $new_search     = $request->getPost('form_new_search',null);
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
        
        if($new_search){
            $count = $this->getSyndromicsurveillanceTable()->fetch_result($fromDate, $toDate, $code_selected, $provider_selected, $start, $end, 1);
        }
        else{
            $count = $request->getPost('form_count', $this->getSyndromicsurveillanceTable()->fetch_result($fromDate, $toDate, $code_selected, $provider_selected, $start, $end, 1));
        }
        $totalpages     = ceil($count/$results);
        
        $params['res_count']    = $count;
        $params['total_pages']  = $totalpages;
        if($download_hl7){
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
            'listenerObject'=> $this->listenerObject,
			'commonplugin'  => $this->CommonPlugin(),
        ));
        return $view;
    }
    
    /**
    * Table Gateway
    * 
    * @return type
    */
    public function getSyndromicsurveillanceTable()
    {
        if (!$this->syndromicsurveillanceTable) {
            $sm = $this->getServiceLocator();
            $this->syndromicsurveillanceTable = $sm->get('Syndromicsurveillance\Model\SyndromicsurveillanceTable');
        }
        return $this->syndromicsurveillanceTable;
    }
}