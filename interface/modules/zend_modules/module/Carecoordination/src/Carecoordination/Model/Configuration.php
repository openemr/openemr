<?php

namespace Carecoordination\Model;

use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Form\Form;

class Configuration extends Form implements InputFilterAwareInterface
{
    protected $inputFilter;

    public function __construct()
    {
        parent::__construct('configuration');
        $this->setAttribute('method', 'post');

    /*
    * Automatic SignOff settings
    */
        $this->add(array(
            'name'      => 'hie_auto_sign_off_id',
            'attributes'    => array(
                    'type'      => 'text',
                    'id'        => 'hie_auto_sign_off_id'
                ),
            'options' => array(
                    'label'     => \Application\Listener\Listener::z_xlt('Auto Sign-Off [days]'),
                ),
        ));

    /*
    * Automatic Tranfer settings
    */
        $this->add(array(
        'type' => 'Laminas\Form\Element\Checkbox',
            'name' => 'hie_auto_send_id',
            'attributes'    => array(
                    'id'        => 'hie_auto_send_id'
                ),
            'options' => array(
                    'label'         => \Application\Listener\Listener::z_xlt('Auto Send'),
            'checked_value'     => 'yes',
                    'unchecked_value'   => 'no'
                ),
        ));

    /*
    * Author settings
    */
        $this->add(array(
            'name'  => 'hie_author_id',
        'type'      => 'Laminas\Form\Element\Select',
            'attributes' => array(
        'class'     => '',
        'data-options'  => 'required:true',
        'editable'  => 'false',
        'required'  => 'required',
        'id'        => 'hie_author_fname'
            ),
            'options' => array(
                'label'     => \Application\Listener\Listener::z_xlt('Author'),
            'value_options' => $this->getUsers(),
            ),
        ));

    /*
    * Data Enterer settings
    */
        $this->add(array(
            'name'  => 'hie_data_enterer_id',
        'type'      => 'Laminas\Form\Element\Select',
            'attributes' => array(
        'class'     => '',
        'data-options'  => 'required:true',
        'editable'  => 'false',
        'required'  => 'required',
        'id'        => 'hie_data_enterer_id'
            ),
            'options' => array(
                'label'     => \Application\Listener\Listener::z_xlt('Data Enterer'),
            'value_options' => $this->getUsers(),
            ),
        ));

    /*
    * Informant settings
    */
        $this->add(array(
            'name'  => 'hie_informant_id',
        'type'      => 'Laminas\Form\Element\Select',
            'attributes' => array(
        'class'     => '',
        'data-options'  => 'required:true',
        'editable'  => 'false',
        'required'  => 'required',
        'id'        => 'hie_informant_id'
            ),
            'options' => array(
                'label'     => \Application\Listener\Listener::z_xlt('Informant'),
            'value_options' => $this->getUsers(),
            ),
        ));

    /*
    * Personal Informant settings
    */
        $this->add(array(
            'name'  => 'hie_personal_informant_id',
            'attributes' => array(
                    'type'  => 'text',
                    'id'    => 'hie_personal_informant_id'
                ),
            'options' => array(
                    'label' => \Application\Listener\Listener::z_xlt('Informant'),
                ),
        ));

    /*
    * Custodian settings
    */
        $this->add(array(
            'name'  => 'hie_custodian_id',
        'type'      => 'Laminas\Form\Element\Select',
            'attributes' => array(
        'class'     => '',
        'data-options'  => 'required:true',
        'editable'  => 'false',
        'required'  => 'required',
        'id'        => 'hie_custodian_id'
            ),
            'options' => array(
                'label'     => \Application\Listener\Listener::z_xlt('Custodian'),
            'value_options' => $this->getUsers(),
            ),
        ));

    /*
    * Recipient settings
    */
        $this->add(array(
            'name'  => 'hie_recipient_id',
        'type'      => 'Laminas\Form\Element\Select',
            'attributes' => array(
        'class'     => '',
        'data-options'  => 'required:true',
        'editable'  => 'false',
        'required'  => 'required',
        'id'        => 'hie_recipient_id'
            ),
            'options' => array(
                'label'     => \Application\Listener\Listener::z_xlt('Recipient'),
            'value_options' => $this->getUsers(),
            ),
        ));

    /*
    * Legal Authenticator settings
    */
        $this->add(array(
            'name'  => 'hie_legal_authenticator_id',
        'type'      => 'Laminas\Form\Element\Select',
            'attributes' => array(
        'class'     => '',
        'data-options'  => 'required:true',
        'editable'  => 'false',
        'required'  => 'required',
        'id'        => 'hie_legal_authenticator_id'
            ),
            'options' => array(
                'label'     => \Application\Listener\Listener::z_xlt('Legal Authenticator'),
            'value_options' => $this->getUsers(),
            ),
        ));

    /*
    * Authenticator settings
    */
        $this->add(array(
            'name'  => 'hie_authenticator_id',
        'type'      => 'Laminas\Form\Element\Select',
            'attributes' => array(
        'class'     => '',
        'data-options'  => 'required:true',
        'editable'  => 'false',
        'required'  => 'required',
        'id'        => 'hie_authenticator_id'
            ),
            'options' => array(
                'label'     => \Application\Listener\Listener::z_xlt('Authenticator'),
            'value_options' => $this->getUsers(),
            ),
        ));

    /*
    * Primary Care Provider settings
    */
        $this->add(array(
            'name'  => 'hie_primary_care_provider_id',
        'type'      => 'Laminas\Form\Element\Select',
            'attributes' => array(
        'class'     => '',
        'data-options'  => 'required:true',
        'editable'  => 'false',
        'required'  => 'required',
        'id'        => 'hie_primary_care_provider_id'
            ),
            'options' => array(
                'label'     => \Application\Listener\Listener::z_xlt('Primary Care Provider'),
            'value_options' => $this->getUsers(),
            ),
        ));
    }

    public function exchangeArray($data)
    {
    }
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();


            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getHookConfig()
    {
    //SOECIFY HOOKS DETAILS OF A MODULE IN AN ARRAY, WITH MODULE NAME AS KEY
    //SHOULD SPECIFY THE CONTROLLER AND ITS ACTION IN THE PATH, INCLUDING INDEX ACTION
        $hooks  =  array(
                '0' => array(
                        'name'  => "send_to_hie",
                        'title' => "Send To HIE",
                        'path'  => "encountermanager",
                    ),
               );

        return $hooks;
    }

    public function getUsers()
    {
        $users = array('0' => '');
        $res = sqlStatement("SELECT id, fname, lname, street, city, state, zip  FROM users WHERE abook_type='ccda'");
        while ($row = sqlFetchArray($res)) {
            $users[$row['id']] = $row['fname'] . " " . $row['lname'];
        }

        return $users;
    }

    public function getDependedModulesConfig()
    {
    }

    public function getAclConfig()
    {
        $acl = array(
        array(
        'section_id' => 'send_to_hie',
        'section_name' => 'Send To HIE',
        'parent_section' => 'carecoordination',
        ),
        );
        return $acl;
    }
}
