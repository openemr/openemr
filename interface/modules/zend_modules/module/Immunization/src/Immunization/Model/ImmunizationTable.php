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
namespace Immunization\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway; 
use Zend\Db\Adapter\Adapter; 
use Zend\Db\ResultSet\ResultSet; 
use Zend\Db\Sql\Select;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use \Application\Model\ApplicationTable;
class ImmunizationTable extends AbstractTableGateway
{
    public $tableGateway;
    protected $applicationTable;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway 		= 	$tableGateway;
		$adapter 					= 	\Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
		$this->adapter              = 	$adapter;
		$this->resultSetPrototype   = 	new ResultSet();
		$this->applicationTable	    = 	new ApplicationTable;
    }
	
	/**
	* function codeslist()
	* Codes List
    */
    public function codeslist()
	{
		$sql	=   "SELECT id, CONCAT('CVX:',CODE) AS NAME FROM codes LEFT JOIN code_types ct ON codes.code_type = ct.ct_id WHERE ct.ct_key='CVX' ORDER BY NAME";
		$result	=   $this->applicationTable->zQuery($sql);
		return $result;
    }
    
    /**
	* function immunized patient details
	* @param type $form_data
	* @return type
	*/
    public function immunizedPatientDetails($form_data,$getCount=null)
    {
		$query_data = array();
		$query_codes     = $form_data['query_codes'];
		$from_date       = $form_data['form_from_date'];
		$to_date         = $form_data['form_to_date'];
		$form_get_hl7    = $form_data['form_get_hl7'];
		$fdate           = '';
		$todate          = '';    
		$query = 
        "SELECT " .
        "i.patient_id AS patientid, " .
        "p.language, ".
        "i.cvx_code , " ;
        if ($form_get_hl7==='true') {
			$query .= 
				"DATE_FORMAT(p.DOB,'%Y%m%d') AS DOB, ".
				"concat(p.street, '^^', p.city, '^', p.state, '^', p.postal_code) AS address, ".
				"p.country_code, ".
				"p.phone_home, ".
				"p.phone_biz, ".
				"p.status, ".
				"p.sex, ".
				"p.ethnoracial, ".
				"p.race, ". 
				"p.ethnicity, ".   
				"c.code_text, ".
				"c.code, ".
				"c.code_type, ".
				"DATE_FORMAT(i.vis_date,'%Y%m%d') AS immunizationdate, ".
				"DATE_FORMAT(i.administered_date,'%Y%m%d') AS administered_date, ".
				"i.lot_number AS lot_number, ".
				"i.manufacturer AS manufacturer, ".
				"concat(p.fname, '^', p.lname) AS patientname, ".
				"f.facility_code,".
				"i.administered_by_id,i.note,";   
        } else {
			$query .= "concat(p.fname, ' ',p.mname,' ', p.lname) AS patientname, ".
				"i.vis_date AS immunizationdate, "  ;
        }
        $query .=
			"i.id AS immunizationid, c.code_text_short AS immunizationtitle ".
			"FROM (immunizations AS i, patient_data AS p, codes AS c) ".
			"LEFT JOIN code_types ct ON c.code_type = ct.ct_id ".
			"LEFT JOIN users AS u ON i.administered_by_id = u.id ".
			"LEFT JOIN facility AS f ON f.id = u.facility_id ".
			"WHERE ".
			"ct.ct_key='CVX' and ";
        if($from_date!=0) {
			$query .= "i.vis_date >= ? " ;
			$query_data[] = $from_date;
        }
        if($from_date!=0 and $to_date!=0) {
			$query .= " and " ;
        }
        if($to_date!=0) {
			$query .= "i.vis_date <= ? ";
			$query_data[] = $to_date;
        }
        if($from_date!=0 or $to_date!=0) {
			$query .= " and " ;
        }
        $query .= "i.patient_id=p.pid and ".
        add_escape_custom($query_codes) .
        "i.cvx_code = c.code ";
        
        if($getCount){
            $result		=	$this->applicationTable->zQuery($query,$query_data);
            $resCount 	=   $result->count();
            return $resCount;
        }
        
        $query .= " LIMIT ".\Application\Plugin\CommonPlugin::escapeLimit($form_data['limit_start']).",".\Application\Plugin\CommonPlugin::escapeLimit($form_data['results']);
        $result	=	$this->applicationTable->zQuery($query,$query_data);
        return $result;
	}     
    
}