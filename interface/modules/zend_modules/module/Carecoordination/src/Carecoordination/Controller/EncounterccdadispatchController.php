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
use Application\Listener\Listener;
use Exception;

class EncounterccdadispatchController extends AbstractActionController
{
    protected $data;
    
    protected $patient_id;
    
    protected $encounter_id;
    
    protected $sections;
    
    protected $encounterccdadispatchTable;
    
    protected $createdtime;
    
    protected $serviceManager;
    
    protected $listenerObject;
    
    protected $recipients;
    
    protected $params;
        
    protected $referral_reason;
    
    protected $latest_ccda;
    
    public function __construct($serviceManager = null)
    {
        $this->listenerObject   = new Listener;
        $this->serviceManager = $serviceManager;
    }
    
    /**
     * API function for CCDA fetching
     */
    public function ccdaFetching($parameterArray = array())
    {
        $validResult = $this->getEncounterccdadispatchTable()->valid($parameterArray[0]);
        // validate credentials
        if ($validResult == 'existingpatient') {
            global $assignedEntity;
            global $representedOrganization;
    
            $mirth_ip   = $this->getEncounterccdadispatchTable()->getSettings('Carecoordination', 'hie_mirth_ip');
            $representedOrganization = $this->getEncounterccdadispatchTable()->getRepresentedOrganization();
            
            $this->patient_id   = $this->getEncounterccdadispatchTable()->getPatientId($parameterArray[0][6]);//$this->getRequest()->getQuery('pid');
            $this->patient_username   = $parameterArray[0][6];
            $this->encounter_id = isset($parameterArray['encounter']) ? $parameterArray['encounter'] : '';
            $combination        = isset($parameterArray['combination']) ? $parameterArray['combination'] : '';
            $this->sections     = isset($parameterArray['sections']) ? $parameterArray['sections'] : '';
            $sent_by            = isset($parameterArray['sent_by']) ? $parameterArray['sent_by'] : '';
            $send               = isset($parameterArray['send']) ? $parameterArray['send'] : 0;
            $view               = isset($parameterArray['view']) ? $parameterArray['view'] : 0;
                        $emr_transfer       = isset($parameterArray['emr_transfer']) ? $parameterArray['emr_transfer'] : 0;
            $this->recipients   = isset($parameterArray['recipients']) ? $parameterArray['recipients'] : '';

            if ($this->recipients == 'patient') {
                $this->params   = $this->patient_id;
            } else {
                $this->params       = isset($parameterArray['param']) ? $parameterArray['param'] : '';
            }
              
            
            
            if ($sent_by!= '') {
                $_SESSION['authId'] = $sent_by;
            }
            
            if (!$this->sections) {
                $components0  = $this->getEncounterccdadispatchTable()->getCCDAComponents(0);
                foreach ($components0 as $key => $value) {
                    if ($str) {
                        $str .= '|';
                    }

                    $str .= $key;
                }

                $this->sections = $str;
            }

            if (!$this->components) {
                            $components1  = $this->getEncounterccdadispatchTable()->getCCDAComponents(1);
                foreach ($components1 as $key => $value) {
                    if ($str1) {
                        $str1 .= '|';
                    }

                    $str1 .= $key;
                }

                            $this->components = $str1;
            }

            if ($combination != '') {
                $arr = explode('|', $combination);
                foreach ($arr as $row) {
                    $arr = explode('_', $row);
                    $this->patient_id   = $arr[0];
                    $this->encounter_id = ($arr[1] > 0 ? $arr[1] : null);
                    $this->create_data($this->patient_id, $this->encounter_id, $this->sections, $send, $this->components);
                    $content            = $this->socket_get("$mirth_ip", "6661", $this->data);
                    
                    if ($content=='Authetication Failure') {
                        return  $content;
                        die();
                    }
                    
                    $to_replace = '<?xml version="1.0" encoding="UTF-8"?>
					<?xml-stylesheet type="text/xsl" href="CDA.xsl"?>
					<ClinicalDocument xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
					xsi:schemaLocation="urn:hl7-org:v3 http://xreg2.nist.gov:8080/hitspValidation/schema/cdar2c32/infrastructure/cda/C32_CDA.xsd"
					xmlns="urn:hl7-org:v3"
					xmlns:mif="urn:hl7-org:v3/mif">
					<!--';
                    $content = preg_replace('/<ClinicalDocument.*><!--/', $to_replace, trim($content));
                    $ccdaDocumentId = $this->getEncounterccdadispatchTable()->logCCDA($this->patient_id, $this->encounter_id, base64_encode($content), $this->createdtime, 0, $_SESSION['authId'], $view, $send, $emr_transfer);
                    try {
                        $event = isset($parameterArray['event']) ? $parameterArray['event'] : 'patient-record';
                        $menu_item = isset($parameterArray['menu_item']) ? $parameterArray['menu_item'] : 'Dashboard';

                        newEvent($event, $this->patient_username, '', 1, '', $this->patient_id, $log_from = 'patient-portal', $menu_item, $ccdaDocumentId);
                    } catch (Exception $e) {
                    }
                }

                if (!$view) {
                    return  "Queued for Transfer";
                }

                if ($view) {
                    $xml = simplexml_load_string($content);
                    $xsl = new \DOMDocument;
                    $xsl->load(dirname(__FILE__).'/../../../../../public/xsl/ccda.xsl');
                    $proc = new \XSLTProcessor;
                    $proc->importStyleSheet($xsl); // attach the xsl rules
                    $outputFile = sys_get_temp_dir() . '/out_'.time().'.html';
                    $proc->transformToURI($xml, $outputFile);
                    
                    $htmlContent = file_get_contents($outputFile);
                    return $htmlContent;
                }

                die;
            } else {
                $practice_filename  = "CCDA_{$this->patient_id}.xml";
                $this->create_data($this->patient_id, $this->encounter_id, $this->sections, $send, $this->components);
                $content            = $this->socket_get("$mirth_ip", "6661", $this->data);
                $to_replace = '<?xml version="1.0" encoding="UTF-8"?>
				<?xml-stylesheet type="text/xsl" href="CDA.xsl"?>
				<ClinicalDocument xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
				xsi:schemaLocation="urn:hl7-org:v3 http://xreg2.nist.gov:8080/hitspValidation/schema/cdar2c32/infrastructure/cda/C32_CDA.xsd"
				xmlns="urn:hl7-org:v3"
				xmlns:mif="urn:hl7-org:v3/mif">
				<!--';
                $content = preg_replace('/<ClinicalDocument.*><!--/', $to_replace, trim($content));
                $ccdaDocumentId = $this->getEncounterccdadispatchTable()->logCCDA($this->patient_id, $this->encounter_id, base64_encode($content), $this->createdtime, 0, $_SESSION['authId'], $view, $send, $emr_transfer);
                try {
                    $event = isset($parameterArray['event']) ? $parameterArray['event'] : 'patient-record';
                    $menu_item = isset($parameterArray['menu_item']) ? $parameterArray['menu_item'] : 'Dashboard';

                    newEvent($event, $this->patient_username, '', 1, '', $this->patient_id, $log_from = 'patient-portal', $menu_item, $ccdaDocumentId);
                } catch (Exception $e) {
                }

                return $content;
                die;
            }
            
            try {
                ob_clean();
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=".$practice_filename);
                header("Content-Type: application/download");
                header("Content-Transfer-Encoding: binary");
                return $content;
                exit;
            } catch (Exception $e) {
                die('SOAP Error');
            }
        } else {
            return '<?xml version="1.0" encoding="UTF-8"?>
			<!-- Edited by XMLSpy -->
			<note>

				<heading>Authetication Failure</heading>
				<body></body>
			</note>
			';
        }
    }
    
    public function downloadCcdaLogDoc($parameterArray = array())
    {
        $validResult = $this->getEncounterccdadispatchTable()->valid($parameterArray[0]);
        
        // validate credentials
        if ($validResult == 'existingpatient' && isset($parameterArray['docid']) && $parameterArray['docid'] > 0) {
            $id         = $parameterArray['docid'];
            $dir        = sys_get_temp_dir()."/CCDA_$id/";
            $filename   = "CCDA_$id.xml";
            if (!is_dir($dir)) {
                mkdir($dir, true);
                chmod($dir, 0777);
            }

            $zip_dir    = sys_get_temp_dir()."/";
            $zip_name   = "CCDA_$id.zip";
            
            $content  = '';
            // if document_storage_method is hard disk read file contents
            if ($GLOBALS['document_storage_method'] == 0) {
                $rowCcdaLog  = $this->getEncounterccdadispatchTable()->getCcdaLogDetails($parameterArray['docid']);
                $content  = file_get_contents($rowCcdaLog['ccda_data']);
            } elseif ($GLOBALS['document_storage_method'] == 1) {
                $content    = $this->getEncountermanagerTable()->getFile($id);
            }

            return $content;
        }
    }
    
    public function indexAction()
    {
        
        global $assignedEntity;
        global $representedOrganization;
        $mirth_ip   = $this->getEncounterccdadispatchTable()->getSettings('Carecoordination', 'hie_mirth_ip');
        
        //$assignedEntity['streetAddressLine']    = '17 Daws Rd.';
        //$assignedEntity['city']                 = 'Blue Bell';
        //$assignedEntity['state']                = 'MA';
        //$assignedEntity['postalCode']           = '02368';
        //$assignedEntity['country']              = 'US';
        //$assignedEntity['telecom']              = '5555551234';

        $representedOrganization = $this->getEncounterccdadispatchTable()->getRepresentedOrganization();
        
        $request            = $this->getRequest();
        $this->patient_id   = $this->getRequest()->getQuery('pid');
        $this->encounter_id = $this->getRequest()->getQuery('encounter');
        $combination        = $this->getRequest()->getQuery('combination');
        $this->sections     = $this->getRequest()->getQuery('sections');
        $sent_by            = $this->getRequest()->getQuery('sent_by');
        $send               = $this->getRequest()->getQuery('send') ? $this->getRequest()->getQuery('send') : 0;
        $view               = $this->getRequest()->getQuery('view') ? $this->getRequest()->getQuery('view') : 0;
        $emr_transfer     = $this->getRequest()->getQuery('emr_transfer') ? $this->getRequest()->getQuery('emr_transfer') : 0;
        $this->recipients   = $this->getRequest()->getQuery('recipient');
        $this->params       = $this->getRequest()->getQuery('param');
                $this->referral_reason  = $this->getRequest()->getQuery('referral_reason');
        $this->components       = $this->getRequest()->getQuery('components') ? $this->getRequest()->getQuery('components') :$this->params('components');
        $downloadccda           = $this->params('downloadccda');
        $this->latest_ccda      = $this->getRequest()->getQuery('latest_ccda') ? $this->getRequest()->getQuery('latest_ccda') : $this->params('latest_ccda');
        if ($downloadccda == 'download_ccda') {
            $combination      = $this->params('pids');
            $view             = $this->params('view');
        }

        if ($sent_by!= '') {
            $_SESSION['authId'] = $sent_by;
        }
        
        if (!$this->sections) {
            $components0  = $this->getEncounterccdadispatchTable()->getCCDAComponents(0);
            foreach ($components0 as $key => $value) {
                if ($str) {
                    $str .= '|';
                }

                $str .= $key;
            }

            $this->sections = $str;
        }

        if (!$this->components) {
            $components1  = $this->getEncounterccdadispatchTable()->getCCDAComponents(1);
            foreach ($components1 as $key => $value) {
                if ($str1) {
                    $str1 .= '|';
                }

                $str1 .= $key;
            }

            $this->components = $str1;
        }

        if ($combination != '') {
            $arr = explode('|', $combination);
            foreach ($arr as $row) {
                $arr = explode('_', $row);
                $this->patient_id   = $arr[0];
                $this->encounter_id = ($arr[1] > 0 ? $arr[1] : null);
                if ($this->latest_ccda) {
                    $this->encounter_id = $this->getEncounterccdadispatchTable()->getLatestEncounter($this->patient_id);
                }
                
                $this->create_data($this->patient_id, $this->encounter_id, $this->sections, $send, $this->components);
                $content            = $this->socket_get("$mirth_ip", "6661", $this->data);
                
                if ($content=='Authetication Failure') {
                    echo $this->listenerObject->z_xlt($content);
                    die();
                }
        
                $to_replace = '<?xml version="1.0" encoding="UTF-8"?>
		<?xml-stylesheet type="text/xsl" href="CDA.xsl"?>
		<ClinicalDocument xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		xsi:schemaLocation="urn:hl7-org:v3 http://xreg2.nist.gov:8080/hitspValidation/schema/cdar2c32/infrastructure/cda/C32_CDA.xsd"
		xmlns="urn:hl7-org:v3"
		xmlns:mif="urn:hl7-org:v3/mif">
		<!--';
                $content = preg_replace('/<ClinicalDocument.*><!--/', $to_replace, trim($content));
                $this->getEncounterccdadispatchTable()->logCCDA($this->patient_id, $this->encounter_id, base64_encode($content), $this->createdtime, 0, $_SESSION['authId'], $view, $send, $emr_transfer);
                if (!$view) {
                    echo $this->listenerObject->z_xlt("Queued for Transfer");
                }
            }

            if ($view && !$downloadccda) {
                $xml = simplexml_load_string($content);
                $xsl = new \DOMDocument;
                $xsl->load(dirname(__FILE__).'/../../../../../public/xsl/ccda.xsl');
                $proc = new \XSLTProcessor;
                $proc->importStyleSheet($xsl); // attach the xsl rules
                $outputFile = sys_get_temp_dir() . '/out_'.time().'.html';
                $proc->transformToURI($xml, $outputFile);
            
                $htmlContent = file_get_contents($outputFile);
                echo $htmlContent;
            }

            if ($downloadccda) {
                $this->forward()->dispatch('encountermanager', array('action'    => 'downloadall',
                                                            'pids'      => $this->params('pids')));
            } else {
                die;
            }
        } else {
            $practice_filename  = "CCDA_{$this->patient_id}.xml";
            $this->create_data($this->patient_id, $this->encounter_id, $this->sections, $send);
            $content            = $this->socket_get("$mirth_ip", "6661", $this->data);
            $to_replace = '<?xml version="1.0" encoding="UTF-8"?>
            <?xml-stylesheet type="text/xsl" href="CDA.xsl"?>
            <ClinicalDocument xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="urn:hl7-org:v3 http://xreg2.nist.gov:8080/hitspValidation/schema/cdar2c32/infrastructure/cda/C32_CDA.xsd"
            xmlns="urn:hl7-org:v3"
            xmlns:mif="urn:hl7-org:v3/mif">
            <!--';
            $content = preg_replace('/<ClinicalDocument.*><!--/', $to_replace, trim($content));
            $this->getEncounterccdadispatchTable()->logCCDA($this->patient_id, $this->encounter_id, base64_encode($content), $this->createdtime, 0, $_SESSION['authId'], $view, $send, $emr_transfer);
            echo $content;
            die;
        }
        
        try {
            ob_clean();
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=".$practice_filename);
            header("Content-Type: application/download");
            header("Content-Transfer-Encoding: binary");
            echo $content;
            exit;
        } catch (Exception $e) {
            die('SOAP Error');
        }
    }
    
    public function socket_get($ip, $port, $data)
    {
        $output = "";
      
        // Create a TCP Stream Socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new Exception("Socket Creation Failed");
        }
      
        // Connect to the server.
        $result = socket_connect($socket, $ip, $port);
        if ($result === false) {
            if ($GLOBALS['ccda_alt_service_enable'] > 1) { // we're local service
                $path = $GLOBALS['fileroot'] . "/ccdaservice";
                if (IS_WINDOWS) {
                    $cmd = "node " . $path . "/serveccda.js";
                    pclose(popen("start /B " . $cmd, "r"));
                } else {
                    $cmd = "nodejs " . $path . "/serveccda.js";
                    exec($cmd . " > /dev/null &");
                }
                sleep(2); // give cpu a rest
                $result = socket_connect($socket, $ip, $port);
                if ($result === false) { // hmm something is amiss with service. user will likely try again.
                    throw new Exception("Connection Failed");
                }
            } else {
                throw new Exception("Connection Failed");
            }
        }
        
        $data = chr(11).$data.chr(28)."\r";
        // Write to socket!
        $out = socket_write($socket, $data, strlen($data));
        
        //Read from socket!
        do {
            $line = "";
            $line = socket_read($socket, 1024, PHP_NORMAL_READ);
            $output .= $line;
        } while ($line != "");
        
        $output = substr(trim($output), 0, strlen($output)-3);
        // Close and return.
        socket_close($socket);
        return $output;
    }
    
    public function create_data($pid, $encounter, $sections, $send = 0, $components)
    {
        global $assignedEntity;
        global $representedOrganization;
        $sections_list = explode('|', $sections);
        $components_list = explode('|', $components);
        $this->createdtime = time();
        $username = $this->getEncounterccdadispatchTable()->getSettings('Carecoordination', 'hie_mirth_username');
        $password = $this->getEncounterccdadispatchTable()->getSettings('Carecoordination', 'hie_mirth_password');
        $client_id = $this->getEncounterccdadispatchTable()->getSettings('Carecoordination', 'hie_mirth_clientid');
        $this->data .= "<CCDA>";
        $this->data .= "<username>$username</username>";
        $this->data .= "<password>$password</password>";
        $this->data .= "<hie>MyHealth</hie>";
        $this->data .= "<time>".$this->createdtime."</time>";
        $this->data .= "<client_id>".$client_id."</client_id>";
        $this->data .= "<created_time>".date('YmdHis')."</created_time>";
        $this->data .= "<created_time_timezone>".date('YmdHisO')."</created_time_timezone>";
        $this->data .= "<send>".htmlspecialchars($send, ENT_QUOTES)."</send>";
        $this->data .= "<assignedEntity>
                <streetAddressLine>".htmlspecialchars($assignedEntity['streetAddressLine'], ENT_QUOTES)."</streetAddressLine>
                <city>".htmlspecialchars($assignedEntity['city'], ENT_QUOTES)."</city>
                <state>".htmlspecialchars($assignedEntity['state'], ENT_QUOTES)."</state>
                <postalCode>".htmlspecialchars($assignedEntity['postalCode'], ENT_QUOTES)."</postalCode>
                <country>".htmlspecialchars($assignedEntity['country'], ENT_QUOTES)."</country>
            </assignedEntity>
            <telecom use='WP' value='".htmlspecialchars($assignedEntity['telecom'], ENT_QUOTES)."'/>
            <representedOrganization>
                <name>".htmlspecialchars($representedOrganization['name'], ENT_QUOTES)."</name>
                <telecom use='WP' value='".htmlspecialchars($representedOrganization['telecom'], ENT_QUOTES)."'/>
                <streetAddressLine>".htmlspecialchars($representedOrganization['streetAddressLine'], ENT_QUOTES)."</streetAddressLine>
                <city>".htmlspecialchars($representedOrganization['city'], ENT_QUOTES)."</city>
                <state>".htmlspecialchars($representedOrganization['state'], ENT_QUOTES)."</state>
                <postalCode>".htmlspecialchars($representedOrganization['postalCode'], ENT_QUOTES)."</postalCode>
                <country>".htmlspecialchars($representedOrganization['country'], ENT_QUOTES)."</country>
            </representedOrganization>";
        $this->data .="<referral_reason><text>".htmlspecialchars($this->referral_reason, ENT_QUOTES)."</text></referral_reason>";
        
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
        
        $this->data .="</CCDA>";
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
        header("Content-Disposition: attachment; filename=".$practice_filename);
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

//        if(in_array('referral',$components_list))
//            $ccd .= $this->getEncounterccdadispatchTable()->getRefferals($pid,$encounter);
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
    * @return type
    */
    public function getEncounterccdadispatchTable()
    {
        if (!$this->encounterccdadispatchTable) {
            if (($this->serviceManager == null)) {
                $this->serviceManager = $this->getServiceLocator();
            }

            $this->encounterccdadispatchTable = $this->serviceManager->get('Carecoordination\Model\EncounterccdadispatchTable');
        }

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
        $auto_send   = $this->getEncounterccdadispatchTable()->getSettings('Carecoordination', 'hie_auto_send_id');
        if ($auto_send != 'yes') {
            return;
        }

        $view        =  new ViewModel(array(
            'combination' => $combination,
            'listenerObject' => $this->listenerObject,
        ));
        $view->setTerminal(true);
        return $this->forward()->dispatch('encounterccdadispatch', array('action'=>'index'));
    }
    
    /*
    * Automatically sign off the combination forms
    *
    * @param    None
    * @return   None
    */
    public function autosignoffAction()
    {
        $auto_signoff_days  = $this->getEncounterccdadispatchTable()->getSettings('Carecoordination', 'hie_auto_sign_off_id');
        $str_time           = ((strtotime(date('Y-m-d')))-($auto_signoff_days*60*60*24));
        $date               = date('Y-m-d', $str_time);
        
        $encounter          = $this->getEncounterccdadispatchTable()->getEncounterDate($date);
        foreach ($encounter as $row) {
            $result = $this->getEncounterccdadispatchTable()->signOff($row['pid'], $row['encounter']);
        }

        $view               =  new ViewModel(array(
            'encounter'     => $result,
        'listenerObject' => $this->listenerObject,
        ));
        $view->setTerminal(true);
        return $view;
    }
}
