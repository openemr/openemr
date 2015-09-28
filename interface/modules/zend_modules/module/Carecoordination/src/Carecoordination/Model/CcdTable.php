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
*    @author  Riju KP <rijukp@zhservices.com>
* +------------------------------------------------------------------------------+
*/
namespace Carecoordination\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Application\Model\ApplicationTable;
use Zend\Db\Adapter\Driver\Pdo\Result;
use Zend\XmlRpc\Generator;

use DOMDocument;
use DOMXpath;

use Document;
use CouchDB;

use Documents\Model\DocumentsTable;

class CcdTable extends AbstractTableGateway
{
    protected $ccd_data_array;
    
    /*
     *  Fetch the component values from the CCDA XML*
     *
     * @param	$components		Array of components
     */
     public function import($xml,$document_id)
     {
	$audit_master_approval_status        = $this->ccd_data_array['approval_status'] = 1;
	$this->ccd_data_array['ip_address']  = $_SERVER['REMOTE_ADDR'];
	$this->ccd_data_array['type'] 	     = '13';
    
	//Patient Details					
	$this->ccd_data_array['field_name_value_array']['patient_data'][1]['fname']        = $xml['recordTarget']['patientRole']['patient']['name']['given'][0];
	$this->ccd_data_array['field_name_value_array']['patient_data'][1]['mname']        = $xml['recordTarget']['patientRole']['patient']['name']['given'][1];
	$this->ccd_data_array['field_name_value_array']['patient_data'][1]['lname']        = $xml['recordTarget']['patientRole']['patient']['name']['family'];
	$this->ccd_data_array['field_name_value_array']['patient_data'][1]['DOB']          = $xml['recordTarget']['patientRole']['patient']['birthTime']['value'];
	$this->ccd_data_array['field_name_value_array']['patient_data'][1]['sex']          = $xml['recordTarget']['patientRole']['patient']['administrativeGenderCode']['displayName'];
	$this->ccd_data_array['field_name_value_array']['patient_data'][1]['street']       = $xml['recordTarget']['patientRole']['addr']['streetAddressLine'];
	$this->ccd_data_array['field_name_value_array']['patient_data'][1]['city']         = $xml['recordTarget']['patientRole']['addr']['city'];
	$this->ccd_data_array['field_name_value_array']['patient_data'][1]['state']        = $xml['recordTarget']['patientRole']['addr']['state'];
	$this->ccd_data_array['field_name_value_array']['patient_data'][1]['postal_code']  = $xml['recordTarget']['patientRole']['addr']['postalCode'];
	$this->ccd_data_array['field_name_value_array']['patient_data'][1]['phone_home']   = preg_replace('/[^0-9]+/i', '', $xml['recordTarget']['patientRole']['telecom']['value']);
        $this->ccd_data_array['field_name_value_array']['patient_data'][1]['extension']    = $xml['recordTarget']['patientRole']['id']['extension'];

		
	$audit_master_id = \Application\Plugin\CommonPlugin::insert_ccr_into_audit_data($this->ccd_data_array);  
        $this->update_document_table($document_id,$audit_master_id,$audit_master_approval_status);
    }
    
    public function update_document_table($document_id,$audit_master_id,$audit_master_approval_status)
   { 
    $appTable   = new ApplicationTable();
    $query = "UPDATE documents 
              SET audit_master_id = ?,
                  imported = ?,
                  audit_master_approval_status=? 
              WHERE id = ?";
    $appTable->zQuery($query, array($audit_master_id, 
                                    1,
                                    $audit_master_approval_status,
                                    $document_id));
   }
}