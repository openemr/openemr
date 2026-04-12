<?php

/*
 * Copyright Medical Information Integration,LLC info@mi-squared.com
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @file C_AbstractClickmap.php
 *
 * @brief This file contains the C_AbstractClickmap class, used to control smarty.
 */

/* for encounter','fileroot','pid','srcdir','style','webroot']
 * remember that include paths are calculated relative to the including script, not this file.
 * to lock the path to this script (so if called from different scripts) use the dirname(FILE) variable
*/

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

require_once(__DIR__ . '/../globals.php');

/* For the addform() function */
require_once(OEGlobalsBag::getInstance()->get('srcdir') . '/forms.inc.php');

/**
 * @class C_AbstractClickmap
 *
 * @brief This class extends the Controller class, which is used to control the smarty templating engine.
 *
 */
abstract class C_AbstractClickmap extends Controller
{
    /**
     * the directory to find our template file in.
     *
     * @var string
     */
    public $template_dir;

    /**
     * @brief Initialize a newly created object belonging to this class
     *
     * @param string $template_mod template module name, passed to Controller's initializer.
     */
    function __construct($template_mod = "general")
    {
        parent::__construct();
        $returnurl = 'encounter_top.php';
        $this->template_mod = $template_mod;
        $this->template_dir = OEGlobalsBag::getInstance()->get('fileroot') . "/interface/clickmap/template/";
        $this->assign("DONT_SAVE_LINK", OEGlobalsBag::getInstance()->get('webroot') . "/interface/patient_file/encounter/$returnurl");
        $this->assign("FORM_ACTION", OEGlobalsBag::getInstance()->get('webroot'));
        $this->assign("STYLE", OEGlobalsBag::getInstance()->get('style'));
    }

    /**
     * @brief Override this abstract function with your implementation of createModel.
     *
     * @param string $form_id An optional id of a form, to populate data from.
     * @return AbstractClickmapModel An AbstractClickmapModel derived Object.
     */
    abstract public function createModel($form_id = "");

    /**
     * @brief Override this abstract function with your implementation of getImage
     *
     * @return string The path to the image backing this form relative to the webroot.
     */
    abstract function getImage();

    /**
     * @brief Override this abstract function to return the label of the optionlists on this form.
     *
     * @return string The label used for all dropdown boxes on this form.
     */
    abstract function getOptionsLabel();

    /**
     * @brief Override this abstract function to return a hash of the optionlist (key=>value pairs).
     *
     * @return array A hash of key=>value pairs, representing all the possible options in the dropdown boxes on this form.
     */
    abstract function getOptionList();

    /**
     * @brief set up the passed in Model object to model the form.
     */
    private function set_context($model)
    {
        $root = OEGlobalsBag::getInstance()->get('webroot') . "/interface/clickmap";
        $model->saveAction = OEGlobalsBag::getInstance()->get('webroot') . "/interface/forms/" . $model->getCode() . "/save.php";
        $model->template_dir = $root . "/template";
        $model->image = $this->getImage();
        $optionList = $this->getOptionList();
        $model->optionList = $optionList != null ? json_encode($optionList) : "null";
        $optionsLabel = $this->getOptionsLabel();
        $model->optionsLabel = isset($optionsLabel) ? "'" . $optionsLabel . "'" : "null";

        $data = $model->get_data();
        $model->data = $data != "" ? "'" . $data . "'" : "null";
        $model->hideNav = "false";
    }

    /**
     * @brief generate an html document from the 'new form' template
     * @return string
     */
    function default_action()
    {
        $model = $this->createModel();
        $this->assign("form", $model);
        $this->set_context($model);
        $this->assign("reportMode", false);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    /**
     * @brief generate an html document from the 'new form' template, populated with form data from the passed in form_id.
     * @param string $form_id The id of the form to populate data from.
     * @return string
     */
    function view_action($form_id)
    {
        $model = $this->createModel($form_id);
        $this->assign("form", $model);
        $this->set_context($model);
        $this->assign("reportMode", false);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    /**
     * @brief generate a fragment of an HTML document from the 'new form' template, populated with form data from the passed in form_id.
     * @param string $form_id The id of the form to populate data from.
     * @return string
     */
    function report_action($form_id)
    {
        $model = $this->createModel($form_id);
        $this->assign("form", $model);
        $this->set_context($model);
        $model->hideNav = "true";
        $this->assign("reportMode", true);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

     /**
     * @brief called to store the submitted form's contents to the database, adding the form to the encounter if necissary.
     */
    function default_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $model = $this->createModel($_POST['id']);
        parent::populate_object($model);
        $model->persist();
        if (OEGlobalsBag::getInstance()->get('encounter') == "") {
            OEGlobalsBag::getInstance()->set('encounter', date("Ymd"));
        }

        if (empty($_POST['id'])) {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            addForm(
                OEGlobalsBag::getInstance()->get('encounter'),
                $model->getTitle(),
                $model->id,
                $model->getCode(),
                OEGlobalsBag::getInstance()->get('pid'),
                $session->get('userauthorized')
            );
            $_POST['process'] = "";
        }
    }
}
