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
*
*    @author  Vinish K <vinish@zhservices.com>
*    @author  Riju K P <rijukp@zhservices.com>
* +------------------------------------------------------------------------------+
*/
namespace Carecoordination\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Filter\Compress\Zip;
use Application\Listener\Listener;

class EncountermanagerController extends AbstractActionController
{
    protected $encountermanagerTable;
    protected $listenerObject;
    
    public function __construct()
    {
        $this->listenerObject	= new Listener;
    }
    
    public function indexAction()
    {
        $request        = $this->getRequest();
        $fromDate 		= $request->getPost('form_date_from', null);
        $fromDate       = $this->CommonPlugin()->date_format($fromDate, 'yyyy-mm-dd', $GLOBALS['date_display_format']);
        $toDate         = $request->getPost('form_date_to', null);
        $toDate         = $this->CommonPlugin()->date_format($toDate, 'yyyy-mm-dd', $GLOBALS['date_display_format']);
        $pid            = $request->getPost('form_pid', null);
        $encounter      = $request->getPost('form_encounter', null);
        $status         = $request->getPost('form_status', null);
        
        if(!$pid && !$encounter && !$status){
            $fromDate       = $request->getPost('form_date_from', null) ? $this->CommonPlugin()->date_format($request->getPost('form_date_from', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d',strtotime(date('Ymd')) - (86400*7));
            $toDate         = $request->getPost('form_date_to', null) ? $this->CommonPlugin()->date_format($request->getPost('form_date_to', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d');
        }
        
        $results        = $request->getPost('form_results', 100);
        $results        = ($results > 0) ? $results : 100;
        $current_page   = $request->getPost('form_current_page', 1);
        $expand_all     = $request->getPost('form_expand_all', 0);
        $select_all     = $request->getPost('form_select_all', 0);
        $end            = $current_page*$results; 
        $start          = ($end - $results);
        $new_search     = $request->getPost('form_new_search',null);
        $form_sl_no     = $request->getPost('form_sl_no', 0);
        
        $downloadccda       = $request->getPost('downloadccda') ? $request->getPost('downloadccda') : $request->getQuery()->downloadccda;
        if($downloadccda == 'download_ccda') {
          $pids           = '';
          if($request->getQuery('pid_ccda')) {
            $pid             = $request->getQuery('pid_ccda');
            if($pid !='') {
              $combination = $pid;
            }
          }
          else {
            $combination     = $request->getPost('ccda_pid');
          }
          
          for($i=0 ; $i<count($combination) ; $i++){
            if($i == (count($combination)-1)) {
              if($combination == $pid)
                $pids = $pid;
              else
                $pids .= $combination[$i];
            }
            else
              $pids .= $combination[$i].'|';
          }
          $components   = $request->getPost('components') ? $request->getPost('components') : $request->getQuery()->components;
          $this->forward()->dispatch('encounterccdadispatch',array('action'       => 'index',
                                                                   'pids'         => $pids,
                                                                   'view'         => 1,
                                                                   'downloadccda' => $downloadccda,
                                                                   'components'   => $components));
        }
        
        $params     = array(
                        'from_date'     => $fromDate,
                        'to_date'       => $toDate,
                        'pid'           => $pid,
                        'encounter'     => $encounter,
                        'status'        => $status,
                        'results'       => $results,
                        'current_page'  => $current_page,
                        'limit_start'   => $start,
                        'limit_end'     => $end,
                        'select_all'    => $select_all,
                        'expand_all'    => $expand_all,
                        'sl_no'         => $form_sl_no,
                    );
        
        if($new_search) {
            $count  = $this->getEncountermanagerTable()->getEncounters($params,1);
        } else {
            $count  = $request->getPost('form_count',$this->getEncountermanagerTable()->getEncounters($params,1));
        }
        $totalpages     = ceil($count/$results);
        
        $details        = $this->getEncountermanagerTable()->getEncounters($params);
        $status_details = $this->getEncountermanagerTable()->getStatus($this->getEncountermanagerTable()->getEncounters($params));
        
        $params['res_count'] = $count;
        $params['total_pages'] = $totalpages;
        
        $layout     = $this->layout();
        $layout->setTemplate('carecoordination/layout/encountermanager');
        
        $index = new ViewModel(array(
            'details'       => $details,
            'form_data'     => $params,
            'table_obj'     => $this->getEncountermanagerTable(),
            'status_details'=> $status_details,
            'listenerObject'=> $this->listenerObject,
            'commonplugin' 	=> $this->CommonPlugin(),
        ));
        return $index;
    }
    
    public function downloadAction()
    {        
        $id         = $this->getRequest()->getQuery('id');
        $dir        = sys_get_temp_dir()."/CCDA_$id/";
        $filename   = "CCDA_$id.xml";
        if(!is_dir($dir)){
            mkdir($dir, true);
            chmod($dir, 0777);
        }
        
        $zip_dir    = sys_get_temp_dir()."/";
        $zip_name   = "CCDA_$id.zip";
        
        $content    = $this->getEncountermanagerTable()->getFile($id);        
        $f          = fopen($dir.$filename, "w");
        fwrite($f, $content);
        fclose($f);
        
        copy(dirname(__FILE__)."/../../../../../public/css/CDA.xsl", $dir."CDA.xsl");
        
        $zip = new Zip();
        $zip->setArchive($zip_dir.$zip_name);
        $zip->compress($dir);
        
        ob_clean();
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$zip_name");
        header("Content-Type: application/download");
        header("Content-Transfer-Encoding: binary");
        readfile($zip_dir.$zip_name);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }
    public function downloadallAction()
    {
      $pids     = $this->params('pids');
      if($pids != ''){
        $zip        = new Zip();
        $parent_dir = sys_get_temp_dir()."/CCDA_".time();
        if(!is_dir($parent_dir)){
          mkdir($parent_dir, true);
          chmod($parent_dir, 0777);
        }
        $arr = explode('|', $pids); 
        foreach($arr as $row){ 
            $pid      = $row; 
            $id       = $this->getEncountermanagerTable()->getFileID($pid); 
            $dir      = $parent_dir."/CCDA_$id/";
            $filename = "CCDA_$id.xml";
            if(!is_dir($dir)){
              mkdir($dir, true);
              chmod($dir, 0777);
            }
            $content = $this->getEncountermanagerTable()->getFile($id);        
            $f2      = fopen($dir.$filename, "w");
            fwrite($f2, $content);
            fclose($f2);
            copy(dirname(__FILE__)."/../../../../../public/css/CDA.xsl", $dir."CDA.xsl");
        }
        $zip_dir  = sys_get_temp_dir()."/";
        $zip_name = "CCDA.zip";
        $zip->setArchive($zip_dir.$zip_name);
        $zip->compress($parent_dir);
        
        ob_clean();
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$zip_name");
        header("Content-Type: application/download");
        header("Content-Transfer-Encoding: binary");
        readfile($zip_dir.$zip_name);
        
        exit;
      }
    }
    public function transmitCCDAction()
    {
        $combination  = $this->getRequest()->getQuery('combination');
        $recipients   = $this->getRequest()->getQuery('recipients');
        $xml_type     = $this->getRequest()->getQuery('xml_type');
        $result       = $this->getEncountermanagerTable()->transmitCCD(array("ccda_combination"=>$combination,"recipients"=>$recipients,"xml_type"=>$xml_type));
        echo $result;
        return $this->response;
    }
    
    /**
    * Table Gateway
    * 
    * @return type
    */
    public function getEncountermanagerTable()
    {	
        if (!$this->encountermanagerTable) {
            $sm = $this->getServiceLocator();
            $this->encountermanagerTable = $sm->get('Carecoordination\Model\EncountermanagerTable');
        }
        return $this->encountermanagerTable;
    }
}
?>