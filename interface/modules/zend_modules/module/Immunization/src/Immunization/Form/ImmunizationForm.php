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
    public function __construct()
    {
        global $pid, $encounter;
        parent::__construct('immunization');
        $this->setAttribute('method', 'post');

        // Codes
        $this->add([
            'name' => 'codes',
            'type' => \Laminas\Form\Element\Select::class,
            'attributes' => [
                'multiple' => 'multiple',
                'size' => '3',
                'class' => 'select',
                'style' => 'width:150px',
                'editable' => 'false',
                'id' => 'codes'
            ],
            'options' => [
                'value_options' => [
                    '' => Listener::z_xlt('Unassigned'),
                ],],
        ]);

        $this->add([
            'name' => 'from_date',
            'type' => \Laminas\Form\Element\Text::class,
            'attributes' => [
                'id' => 'from_date',
                'placeholder' => 'From Date',
                'value' => date('Y-m-d', strtotime(date('Ymd')) - (86400 * 7)),
                'class' => 'date_field',
                'style' => 'width: 42%;cursor:pointer;',
            ],
        ]);

        $this->add([
            'name' => 'to_date',
            'type' => 'Date',
            'attributes' => [
                'id' => 'to_date',
                'placeholder' => 'To Date',
                'class' => 'date_field',
                'value' => date('Y-m-d'),
                'style' => 'width: 42%;cursor:pointer;',
                'type' => 'text',
                'onchange' => 'validate_search();'
            ],
        ]);

        $this->add([
            'name' => 'search',
            'type' => 'submit',
            'attributes' => [
                'value' => Listener::z_xlt('SEARCH'),
                'id' => 'search_form_button',
            ],
        ]);
        $this->add([
            'name' => 'print',
            'attributes' => [
                'type' => 'button',
                'value' => Listener::z_xlt('Print'),
                'id' => 'printbutton',
            ],
        ]);
        $this->add([
            'name' => 'hl7button',
            'type' => 'submit',
            'attributes' => [
                'value' => Listener::z_xlt('GET HL7'),
                'id' => 'hl7button',
                'onclick' => 'getHl7(this.value);',
                // the button is hidden as we apparently use it to submit the form when the
                // shared sendTo will trigger this button to click...
                // @see sendTo.js and immunization.js and search for #hl7button
                'style' => 'display:none;'
            ],
        ]);
    }
}
