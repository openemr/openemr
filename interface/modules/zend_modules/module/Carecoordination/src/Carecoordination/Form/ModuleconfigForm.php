<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Form/ModuleconfigForm.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Form;

use Laminas\Form\Form;
use Application\Model\ApplicationTable;
use Application\Listener\Listener;

class ModuleconfigForm extends Form
{
    protected $zListener;

    protected $application;

    public function __construct()
    {
        $this->application  = new ApplicationTable();
        $this->zListener = new Listener();
        parent::__construct('configuration');
        $this->setAttribute('method', 'post');

        /*
        * Automatic SignOff settings
        */
        $this->add([
                'name'      => 'hie_auto_sign_off_id',
                'attributes'    => [
                        'type'      => 'text',
                        'id'        => 'hie_auto_sign_off_id'
                    ],
                'options' => [
                        'label'     => $this->zListener->z_xlt('Auto Sign-Off [days]'),
                    ],
            ]);

        /*
        * Automatic Tranfer settings
        */
        $this->add([
            'type' => \Laminas\Form\Element\Checkbox::class,
                'name' => 'hie_auto_send_id',
                'attributes'    => [
                        'id'        => 'hie_auto_send_id'
                    ],
                'options' => [
                        'label'         => $this->zListener->z_xlt('Auto Send'),
                'checked_value'     => 'yes',
                        'unchecked_value'   => 'no'
                    ],
            ]);

        /*
        * Author settings
        */
        $this->add([
                'name'  => 'hie_author_id',
                'type'      => \Laminas\Form\Element\Select::class,
                'attributes' => [
                    'class'     => '',
                    'data-options'  => 'required:true',
                    'editable'  => 'false',
                    'required'  => 'required',
                    'id'        => 'hie_author_fname'
                ],
                'options' => [
                    'label'     => $this->zListener->z_xlt('Author'),
                    'value_options' => $this->getProviders(),
                ],
            ]);

        /*
        * Data Enterer settings
        */
        $this->add([
                'name'      => 'hie_data_enterer_id',
                'type'      => \Laminas\Form\Element\Select::class,
                'attributes' => [
                    'class'     => '',
                    'data-options'  => 'required:true',
                    'editable'  => 'false',
                    'required'  => 'required',
                    'id'        => 'hie_data_enterer_id'
                ],
                'options' => [
                    'label'     => $this->zListener->z_xlt('Data Enterer'),
                    'value_options' => $this->getUsersList(),
                ],
            ]);

        /*
        * Informant settings
        */
        $this->add([
                'name'  => 'hie_informant_id',
                'type'      => \Laminas\Form\Element\Select::class,
                'attributes' => [
                    'class'     => '',
                    'data-options'  => 'required:true',
                    'editable'  => 'false',
                    'required'  => 'required',
                    'id'        => 'hie_informant_id'
                ],
                'options' => [
                    'label'     => $this->zListener->z_xlt('Informant'),
                    'value_options' => $this->getProviders(),
                ],
            ]);

        /*
        * Personal Informant settings
        */
        $this->add([
                'name'  => 'hie_personal_informant_id',
                'attributes' => [
                        'type'  => 'text',
                        'id'    => 'hie_personal_informant_id'
                    ],
                'options' => [
                        'label' => $this->zListener->z_xlt('Informant'),
                    ],
            ]);

        /*
        * Custodian settings
        */
        $this->add([
                'name'  => 'hie_custodian_id',
                'type'      => \Laminas\Form\Element\Select::class,
                'attributes' => [
                    'class'     => '',
                    'data-options'  => 'required:true',
                    'editable'  => 'false',
                    'required'  => 'required',
                    'id'        => 'hie_custodian_id'
                ],
                'options' => [
                    'label'     => $this->zListener->z_xlt('Custodian'),
                    'value_options' => $this->getFacilities(),
                ],
            ]);

        /*
        * Legal Authenticator settings
        */
        $this->add([
                'name'  => 'hie_legal_authenticator_id',
                'type'      => \Laminas\Form\Element\Select::class,
                'attributes' => [
                    'class'     => '',
                    'data-options'  => 'required:true',
                    'editable'  => 'false',
                    'required'  => 'required',
                    'id'        => 'hie_legal_authenticator_id'
                ],
                'options' => [
                    'label'     => $this->zListener->z_xlt('Legal Authenticator'),
                    'value_options' => $this->getUsers(),
                ],
            ]);

        /*
        * Authenticator settings
        */
        $this->add([
                'name'  => 'hie_authenticator_id',
                'type'      => \Laminas\Form\Element\Select::class,
                'attributes' => [
                    'class'     => '',
                    'data-options'  => 'required:true',
                    'editable'  => 'false',
                    'required'  => 'required',
                    'id'        => 'hie_authenticator_id'
                ],
                'options' => [
                    'label'     => $this->zListener->z_xlt('Authenticator'),
                    'value_options' => $this->getUsers(),
                ],
            ]);

        /*
        * Primary Care Provider settings
        */
        $this->add([
                'name'  => 'hie_primary_care_provider_id',
                'type'      => \Laminas\Form\Element\Select::class,
                'attributes' => [
                    'class'     => '',
                    'data-options'  => 'required:true',
                    'editable'  => 'false',
                    'required'  => 'required',
                    'id'        => 'hie_primary_care_provider_id'
                ],
                'options' => [
                    'label'     => $this->zListener->z_xlt('Primary Care Provider'),
                    'value_options' => $this->getProviders(),
                ],
            ]);
        $this->add([
            'type' => \Laminas\Form\Element\Checkbox::class,
            'name' => 'hie_force_latest_encounter_provenance_date',
            'attributes'    => [
                'id'        => 'hie_force_latest_encounter_provenance_date'
            ],
            'options' => [
                'label'         => $this->zListener->z_xlt('Force Provenance Author Date to be most recent encounter'),
                'checked_value'     => 'yes',
                'unchecked_value'   => 'no'
            ],
        ]);
        $this->add([

            'name' => 'hie_author_date',
            'type' => \Laminas\Form\Element\DateTimeLocal::class,
            'attributes' => [
                //'min' => '2000-01-01T00:00Z',
                //'max' => '2030-01-01T00:00:00Z',
                'step' => '1',
                'id' => 'hie_author_date',
            ],
            'options' => [
                //'format' => 'Y-m-d\T:HP',
                'label' => $this->zListener->z_xlt('Provenance Author Date')
            ],

        ]);
        /*
        * Authenticator settings
        */
        $this->add([
            'name'  => 'hie_office_contact',
            'type'      => \Laminas\Form\Element\Select::class,
            'attributes' => [
                'class'     => '',
                'data-options'  => 'required:true',
                'editable'  => 'false',
                'required'  => 'required',
                'id'        => 'hie_office_contact'
            ],
            'options' => [
                'label'     => $this->zListener->z_xlt('Office Contact'),
                'value_options' => $this->getUsers(),
            ],
        ]);
    }

    /**
    * Function getOptions
    * Get Select Options
    *
    * @return array
    */
    public function getUsers()
    {
        $users = ['0' => ''];
        $res = $this->application->zQuery(("SELECT id, fname, lname, street, city, state, zip  FROM users WHERE authorized=1 AND active='1' "));
        foreach ($res as $row) {
            $users[$row['id']] = $row['fname'] . " " . $row['lname'];
        }

        return $users;
    }

    public function getFacilities()
    {
        $users = ['0' => ''];
        $res = $this->application->zQuery(("SELECT `id`,`name` FROM `facility`"));
        foreach ($res as $row) {
            $users[$row['id']] = $row['name'];
        }

        return $users;
    }

    public function getProviders()
    {
        $users = ['0' => ''];
        $res = $this->application->zQuery(("SELECT id, fname, lname FROM users WHERE authorized=1 AND active ='1'"));
        foreach ($res as $row) {
            $users[$row['id']] = $row['fname'] . " " . $row['lname'];
        }

        return $users;
    }

    public function getUsersList()
    {
        $users = ['0' => ''];
        $res = $this->application->zQuery(("SELECT id, fname, lname FROM users WHERE active ='1' AND `username` IS NOT NULL AND `password` IS NOT NULL"));
        foreach ($res as $row) {
            $users[$row['id']] = $row['fname'] . " " . $row['lname'];
        }

        return $users;
    }
}
