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
*    @author  Bindia Nandakumar <bindia@zhservices.com>
* +------------------------------------------------------------------------------+
*/
namespace Immunization\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Immunization\Form\ImmunizationForm;
use Application\Listener\Listener;

class ImmunizationController extends AbstractActionController
{
    protected $immunizationTable;
    
    protected $listenerObject;
    
    protected $date_format;
    
    public function __construct()
    {
        $this->listenerObject	= new Listener;
    }
    
    /**
    * Index Page
    * @return \Zend\View\Model\ViewModel
    */
    public function indexAction()
    {
        $form            =   new ImmunizationForm();
        $request         =   $this->getRequest();
        $form->setData($request->getPost());
        $isPost          =   '';
        $data            =   $request->getPost();   
        $isFormRefresh   =   'true';
        $form_code       =   isset($data['codes']) ? $data['codes'] : Array();
        $from_date       =   $request->getPost('from_date', null) ? $this->CommonPlugin()->date_format($request->getPost('from_date', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d',strtotime(date('Ymd')) - (86400*7));
        $to_date         =   $request->getPost('to_date', null) ? $this->CommonPlugin()->date_format($request->getPost('to_date', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d');
        $form_get_hl7    =   '';
        //pagination
        $results         =   $request->getPost('form_results', 100);
        $results         =   ($results > 0) ? $results : 100;
        $current_page    =   $request->getPost('form_current_page', 1);
        $end             =   $current_page*$results; 
        $start           =   ($end - $results);
        $new_search      = $request->getPost('form_new_search',null);
        //end pagination

        if(empty($form_code)){
            $query_codes = '';
        } 
        else {
            $query_codes = 'c.id in ( ';
            foreach($form_code as $code){
                $query_codes .= $code . ","; 
            }
            $query_codes = substr($query_codes ,0,-1);
            $query_codes .= ') and ';
        }
        $params     = array(
            'form_from_date'        => $from_date,
            'form_to_date'          => $to_date,
            'form_get_hl7'          => $form_get_hl7,
            'query_codes'           => $query_codes,
            'results'               => $results,
            'current_page'          => $current_page,
            'limit_start'           => $start,
            'limit_end'             => $end,
        );

        if($new_search) {
            $count  = $this->getImmunizationTable()->immunizedPatientDetails($params,1);
        } 
        else {
            $count  = $request->getPost('form_count');
            if($count == ''){
                $count = $this->getImmunizationTable()->immunizedPatientDetails($params,1);
            }
        }

        $totalpages     = ceil($count/$results);
        $details        = $this->getImmunizationTable()->immunizedPatientDetails($params);
        $rows = array(); 
        foreach ($details as $row){
            $rows[] =  $row;
        }
        $params['res_count'] = $count;
        $params['total_pages'] = $totalpages;
      
        $codes  = $this->getAllCodes($data);
        if($codes != '')
        $form->get('codes')->setValueOptions($codes);
        
        $view = new ViewModel(array(
                'listenerObject'    =>  $this->listenerObject,
                'form'             =>  $form, 
                'view'             =>  $rows, 
                'isFormRefresh'    =>  $isFormRefresh,
                'form_data'        =>  $params,
				'commonplugin'  => $this->CommonPlugin(),
            ));
        //$view->setTerminal(true);
        return $view;
    }
    
    /**
    * function getAllCodes
    * List All Codes in the combobox
    */
    public function getAllCodes($data)
    {
        $defaultCode   =   isset($data['codes']) ? $data['codes'] : ''; 
        $res           =   $this->getImmunizationTable()->codeslist();
        $i             =   0;
        foreach ($res as $value) {
            if ($value == $defaultCode){
                $select =   TRUE;
    	    } else{
                $select =   FALSE;
    	    }
            $rows[$i]   =   array (
                            'value' => $value['id'],
                            'label' => $value['NAME'],
                            'selected' => $select,
                            );
    	    $i++;
        }
        return $rows; 
    }
    
    /**
    * function getHL7
    * generating HL7 format
    */
    public function reportAction()
    {
        $request    = 	$this->getRequest();
        $data       = $request->getPost();
        if(isset($data['hl7button'])){
            $form_code        =   isset($data['codes']) ? $data['codes'] : Array();
            $from_date       =   $request->getPost('from_date', null) ? $this->CommonPlugin()->date_format($request->getPost('from_date', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d',strtotime(date('Ymd')) - (86400*7));
            $to_date         =   $request->getPost('to_date', null) ? $this->CommonPlugin()->date_format($request->getPost('to_date', null), 'yyyy-mm-dd', $GLOBALS['date_display_format']) : date('Y-m-d');
            $form_get_hl7     =   'true'; 
            //pagination
            $results          =   $request->getPost('form_results', 100);
            $results          =   ($results > 0) ? $results : 100;
            $current_page     =   $request->getPost('form_current_page', 1);
            $end              =   $current_page*$results; 
            $start            =   ($end - $results);
            $new_search     = $request->getPost('form_new_search',null);
            //endpagination

            if ( empty ($form_code) ) {
                $query_codes = '';
            }
            else {
                $query_codes = 'c.id in ( ';
                foreach( $form_code as $code ){ 
                    $query_codes .= $code . ","; 
                }
                $query_codes = substr($query_codes ,0,-1);
                $query_codes .= ') and ';
            }
            $params     = array(
                'form_from_date'     => $from_date,
                'form_to_date'       => $to_date,
                'form_get_hl7'       => $form_get_hl7,
                'query_codes'        => $query_codes,    
                'results'            => $results,
                'current_page'       => $current_page,
                'limit_start'        => $start,
                'limit_end'          => $end,
            );
          
            if($new_search) {
                $count  = $this->getImmunizationTable()->immunizedPatientDetails($params,1);
            } else {
                $count  = $request->getPost('form_count');
                if($count == ''){
                    $count = $this->getImmunizationTable()->immunizedPatientDetails($params,1);
                }
            }
          
            $totalpages     = ceil($count/$results);

            $details        = $this->getImmunizationTable()->immunizedPatientDetails($params);
            $rows = array(); 
            foreach ($details as $row){
                $rows[] =  $row;
            }

            $D          =   "\r";
            $nowdate    = date('YmdHis');
            $now        = date('YmdGi');
            $now1       = date('Y-m-d G:i');
            $filename   = "imm_reg_". $now . ".hl7";

            // GENERATE HL7 FILE
            if ($form_get_hl7==='true'){
                $content = ''; 
                foreach($rows as $r){
                    $content .= "MSH|^~\&|OPENEMR|" . $r['facility_code'] . "|||$nowdate||".
                    "VXU^V04^VXU_V04|OPENEMR-110316102457117|P|2.5.1" .
                    "$D" ;
                    if ($r['sex']==='Male') $r['sex'] = 'M';
                    if ($r['sex']==='Female') $r['sex'] = 'F';
                    if ($r['status']==='married') $r['status'] = 'M';
                    if ($r['status']==='single') $r['status'] = 'S';
                    if ($r['status']==='divorced') $r['status'] = 'D';
                    if ($r['status']==='widowed') $r['status'] = 'W';
                    if ($r['status']==='separated') $r['status'] = 'A';
                    if ($r['status']==='domestic partner') $r['status'] = 'P';
                    $content .= "PID|" . // [[ 3.72 ]]
                        "|" . // 1. Set id
                        "|" . // 2. (B)Patient id
                        $r['patientid']. "^^^MPI&2.16.840.1.113883.19.3.2.1&ISO^MR" . "|". // 3. (R) Patient indentifier list. TODO: Hard-coded the OID from NIST test. 
                        "|" . // 4. (B) Alternate PID
                        $r['patientname']."|" . // 5.R. Name
                        "|" . // 6. Mather Maiden Name
                        $r['DOB']."|" . // 7. Date, time of birth
                        $r['sex']."|" . // 8. Sex
                        "|" . // 9.B Patient Alias
                        "2106-3^" . $r['race']. "^HL70005" . "|" . // 10. Race // Ram change
                        $r['address'] . "^^M" . "|" . // 11. Address. Default to address type  Mailing Address(M)
                        "|" . // 12. county code
                        "^PRN^^^^" . $this->format_phone($r['phone_home']) . "|" . // 13. Phone Home. Default to Primary Home Number(PRN)
                        "^WPN^^^^" . $this->format_phone($r['phone_biz']) . "|" . // 14. Phone Work.
                        "|" . // 15. Primary language
                        $r['status']."|" . // 16. Marital status
                        "|" . // 17. Religion
                        "|" . // 18. patient Account Number
                        "|" . // 19.B SSN Number
                        "|" . // 20.B Driver license number
                        "|" . // 21. Mathers Identifier
                        $this->format_ethnicity($r['ethnicity']) . "|" . // 22. Ethnic Group
                        "|" . // 23. Birth Plase
                        "|" . // 24. Multiple birth indicator
                        "|" . // 25. Birth order
                        "|" . // 26. Citizenship
                        "|" . // 27. Veteran military status
                        "|" . // 28.B Nationality
                        "|" . // 29. Patient Death Date and Time
                        "|" . // 30. Patient Death Indicator
                        "|" . // 31. Identity Unknown Indicator
                        "|" . // 32. Identity Reliability Code
                        "|" . // 33. Last Update Date/Time
                        "|" . // 34. Last Update Facility
                        "|" . // 35. Species Code
                        "|" . // 36. Breed Code
                        "|" . // 37. Breed Code
                        "|" . // 38. Production Class Code
                        ""  . // 39. Tribal Citizenship
                        "$D" ;
                    $content .= "ORC" . // ORC mandatory for RXA
                        "|" . 
                        "RE" .
                        "$D" ;
                    $content .= "RXA|" . 
                        "0|" . // 1. Give Sub-ID Counter
                        "1|" . // 2. Administrattion Sub-ID Counter
                        $r['administered_date']."|" . // 3. Date/Time Start of Administration
                        $r['administered_date']."|" . // 4. Date/Time End of Administration
                        $this->format_cvx_code($r['code']). "^" . $r['immunizationtitle'] . "^" . "CVX" ."|" . // 5. Administration Code(CVX)
                        "999|" . // 6. Administered Amount. TODO: Immunization amt currently not captured in database, default to 999(not recorded)
                        "|" . // 7. Administered Units
                        "|" . // 8. Administered Dosage Form
                        "00^".$r['note']."^NIP001|" . // 9. Administration Notes
                        "|" . // 10. Administering Provider
                        "|" . // 11. Administered-at Location
                        "|" . // 12. Administered Per (Time Unit)
                        "|" . // 13. Administered Strength
                        "|" . // 14. Administered Strength Units
                        $r['lot_number']."|" . // 15. Substance Lot Number
                        "|" . // 16. Substance Expiration Date
                        "MSD" . "^" . $r['manufacturer']. "^" . "HL70227" . "|" . // 17. Substance Manufacturer Name
                        "|" . // 18. Substance/Treatment Refusal Reason
                        "|" . // 19.Indication
                        "|" . // 20.Completion Status
                        "A" . // 21.Action Code - RXA
                        "$D" ; 
                }
                header('Content-type: text/plain');
                header('Content-Disposition: attachment; filename=' . $filename );

                // put the content in the file
                echo($content);
                exit;
            }
        }
    }
    
    /**
    * 
    * @param type $ethnicity
    * @return type
    */
    public function format_ethnicity($ethnicity) 
    {
        switch ($ethnicity){
            case "hisp_or_latin":
                return ("H^Hispanic or Latino^HL70189");
            case "not_hisp_or_latin":
                return ("N^not Hispanic or Latino^HL70189");
            default: // Unknown
                return ("U^Unknown^HL70189");
        }
    }
    
    /**
    * 
    * @param type   $a
    * @return type
    */
    public function tr($a) 
    {
        return (str_replace(' ','^',$a));
    }
    
    /**
    * 
    * @param type   $cvx_code
    * @return type
    */
    public function format_cvx_code($cvx_code) 
    {
        if ( $cvx_code < 10 ){
            return "0$cvx_code"; 
        }
        return $cvx_code;
    }
    
    /**
    * 
    * @param   $phone      String          phone number
    * @return              String          formatted phone
    */
    public function format_phone($phone) 
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        switch (strlen($phone)){
            case 7:
                return $this->tr(preg_replace("/([0-9]{3})([0-9]{4})/", "000 $1$2", $phone));
            case 10:
                return $this->tr(preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 $2$3", $phone));
            default:
                return $this->tr("000 0000000");
        }
    }
    
    /*
    *   Table Gateway
    */
    public function getImmunizationTable()
    {
        if (!$this->immunizationTable){
            $sm = $this->getServiceLocator();
            $this->immunizationTable = $sm->get('Immunization\Model\ImmunizationTable');
        }
        return $this->immunizationTable;
    }
}