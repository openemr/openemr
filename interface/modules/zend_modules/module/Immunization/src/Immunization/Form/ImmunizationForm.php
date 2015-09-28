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
namespace Immunization\Form;

use Zend\Form\Form;

class ImmunizationForm extends Form
{
    public function __construct($name = null)
    {
	global $pid,$encounter;
	parent::__construct('immunization');
	$this->setAttribute('method', 'post'); 
		
	 // Codes
        $this->add(array(
        		'name' 			=> 'codes',
        		'type'  		=> 'Zend\Form\Element\Select',
        		'attributes' 		=> array(
                                        'multiple'      => 'multiple',  
                                        'size'          => '3',
        				'class' 	=> 'select',
        				'style' 	=> 'width:150px',
        				'editable' 	=> 'false',
        				'id' 		=> 'codes'
        		),
        		'options' => array(
        				'value_options' => array(
        						'' => \Application\Listener\Listener::z_xlt('Unassigned'),
        				),),
        ));
        
        $this->add(array( 
                            'name' => 'from_date', 
                            'type' => 'Zend\Form\Element\Text', 
                            'attributes' => array( 
                                            'id'          => 'from_date', 
                                            'placeholder' => 'From Date',
                                            'value'       => date('Y-m-d',strtotime(date('Ymd')) - (86400*7)),
                                            'class'       => 'date_field',
                                            'style'       => 'width: 42%;cursor:not-allowed;',
                            ),
                          ));
       
        $this->add(array( 
                        'name' => 'to_date', 
                        'type' => 'Date', 
                        'attributes' => array( 
                                        'id' 		=> 'to_date', 
                                        'placeholder' 	=> 'To Date',
                                        'class'         => 'date_field',
                                        'value'         => date('Y-m-d'),
                                        'style'         => 'width: 42%;cursor:not-allowed;',
                                        'type'          => 'text',
                                        'onchange' => 'validate_search();'
                        ),
                    ));
        
        $this->add(array(
                        'name' => 'search',
                        'type' => 'submit',
                        'attributes' => array(
                                        'value' => \Application\Listener\Listener::z_xlt('SEARCH'),
                                        'id'    => 'search_form_button',
                                        ),
                    ));
        $this->add(array(
                        'name' => 'print',
                        'attributes' => array(
                                        'type'  => 'button',
                                        'value' => \Application\Listener\Listener::z_xlt('Print'),
                                        'id'    => 'printbutton',
                                         ),
                    ));
        $this->add(array(
                        'name' => 'hl7button',
                        'type'  => 'submit',
                        'attributes' => array(
                                        'value' => \Application\Listener\Listener::z_xlt('GET HL7'),
                                        'id'    => 'hl7button',
                                        'onclick'=> 'getHl7(this.value);',
                                        'style' => 'display:none;'
                                        ),
                    ));       
    }
}

