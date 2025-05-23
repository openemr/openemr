<?php

/**
 * interface/modules/zend_modules/module/Immunization/src/Immunization/Form/ImmunizationForm.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Bindia Nandakumar <bindia@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Immunization\Form;

use Application\Listener\Listener;
use Laminas\Form\Form;

class ImmunizationForm extends Form
{
    public function __construct($name = null)
    {
        global $pid, $encounter;
        parent::__construct('immunization');
        $this->setAttribute('method', 'post');

        // Codes
        $this->add(array(
            'name' => 'codes',
            'type' => 'Laminas\Form\Element\Select',
            'attributes' => array(
                'multiple' => 'multiple',
                'size' => '3',
                'class' => 'select',
                'style' => 'width:150px',
                'editable' => 'false',
                'id' => 'codes'
            ),
            'options' => array(
                'value_options' => array(
                    '' => Listener::z_xlt('Unassigned'),
                ),),
        ));

        $this->add(array(
            'name' => 'from_date',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'id' => 'from_date',
                'placeholder' => 'From Date',
                'value' => date('Y-m-d', strtotime(date('Ymd')) - (86400 * 7)),
                'class' => 'date_field',
                'style' => 'width: 42%;cursor:pointer;',
            ),
        ));

        $this->add(array(
            'name' => 'to_date',
            'type' => 'Date',
            'attributes' => array(
                'id' => 'to_date',
                'placeholder' => 'To Date',
                'class' => 'date_field',
                'value' => date('Y-m-d'),
                'style' => 'width: 42%;cursor:pointer;',
                'type' => 'text',
                'onchange' => 'validate_search();'
            ),
        ));

        $this->add(array(
            'name' => 'search',
            'type' => 'submit',
            'attributes' => array(
                'value' => Listener::z_xlt('SEARCH'),
                'id' => 'search_form_button',
            ),
        ));
        $this->add(array(
            'name' => 'print',
            'attributes' => array(
                'type' => 'button',
                'value' => Listener::z_xlt('Print'),
                'id' => 'printbutton',
            ),
        ));
        $this->add(array(
            'name' => 'hl7button',
            'type' => 'submit',
            'attributes' => array(
                'value' => Listener::z_xlt('GET HL7'),
                'id' => 'hl7button',
                'onclick' => 'getHl7(this.value);',
                // the button is hidden as we apparently use it to submit the form when the
                // shared sendTo will trigger this button to click...
                // @see sendTo.js and immunization.js and search for #hl7button
                'style' => 'display:none;'
            ),
        ));
    }
}
