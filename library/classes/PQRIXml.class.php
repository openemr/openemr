<?php

 // Copyright (C) 2011 Ensoftek
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This program implements the XML Writer to generate PQRI 2009 XML.


class PQRIXml extends XmlWriterOemr
{
    function __construct($indent = '  ')
    {
        parent::__construct($indent);
    }

    function open_submission()
    {

        $this->push('submission', array('type' => 'PQRI-REGISTRY', 'option' => 'payment',
           'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance', 'xsi:noNamespaceSchemaLocation' => 'Registry_Payment.xsd'));
    }

    function close_submission()
    {
        $this->pop();
    }


    function add_file_audit_data()
    {

        $res = sqlQuery("select * from users where username=?", array($_SESSION["authUser"]));


        $this->push('file_audit_data');
        $this->element('create-date', date("m-d-Y"));
        $this->element('create-time', date("H:i"));
        $this->element('create-by', $res["fname"] . ' ' . $res["lname"]);
        $this->element('version', '1.0');
        $this->element('file-number', '1');
        $this->element('number-of-files', '1');
        $this->pop();
    }

    function add_registry($submission_method)
    {

        $this->push('registry');
        $this->element('registry-name', $GLOBALS['pqri_registry_name']);
        $this->element('registry-id', $GLOBALS['pqri_registry_id']);
        $this->element('submit-method', $submission_method);
        $this->pop();
    }

    function add_measure_group_stats($arrStats)
    {
        $this->push('measure-group-stat');

        foreach ($arrStats as $key => $value) {
            $this->element($key, $value);
        }

        $this->pop();
    }

    function add_pqri_measures($arrStats)
    {
        $this->push('pqri-measure');

        foreach ($arrStats as $key => $value) {
            $this->element($key, $value);
        }

        $this->pop();
    }


    function open_provider($arrStats)
    {
        $this->push('provider');

        foreach ($arrStats as $key => $value) {
            $this->element($key, $value);
        }
    }

    function close_provider()
    {
        $this->pop();
    }

    function open_measure_group($id)
    {
        $this->push('measure-group', array('ID' => $id));
    }

    function close_measure_group()
    {
        $this->pop();
    }
}
