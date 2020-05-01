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
require_once(dirname(__FILE__) . '/../globals.php');

/* For the addform() function */
require_once($GLOBALS['srcdir'] . '/forms.inc');

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
     * @var template_dir
     */
    var $template_dir;

    /**
     * @brief Initialize a newly created object belonging to this class
     *
     * @param template_mod
     *  template module name, passed to Controller's initializer.
     */
    function __construct($template_mod = "general")
    {
        parent::__construct();
        $returnurl = 'encounter_top.php';
        $this->template_mod = $template_mod;
        $this->template_dir = $GLOBALS['fileroot'] . "/interface/clickmap/template/";
        $this->assign("DONT_SAVE_LINK", $GLOBALS['webroot'] . "/interface/patient_file/encounter/$returnurl");
        $this->assign("FORM_ACTION", $GLOBALS['webroot']);
        $this->assign("STYLE", $GLOBALS['style']);
    }

    /**
     * @brief Override this abstract function with your implementation of createModel.
     *
     * @param $form_id
     *  An optional id of a form, to populate data from.
     *
     * @return Model
     *  An AbstractClickmapModel derived Object.
     */
    abstract public function createModel($form_id = "");

    /**
     * @brief Override this abstract function with your implememtation of getImage
     *
     * @return The path to the image backing this form relative to the webroot.
     */
    abstract function getImage();

    /**
     * @brief Override this abstract function to return the label of the optionlists on this form.
     *
     * @return The label used for all dropdown boxes on this form.
     */
    abstract function getOptionsLabel();

    /**
     * @brief Override this abstract functon to return a hash of the optionlist (key=>value pairs).
     *
     * @return A hash of key=>value pairs, representing all the possible options in the dropdown boxes on this form.
     */
    abstract function getOptionList();

    /**
     * @brief set up the passed in Model object to model the form.
     */
    private function set_context($model)
    {
        $root = $GLOBALS['webroot'] . "/interface/clickmap";
        $model->saveAction = $GLOBALS['webroot'] . "/interface/forms/" . $model->getCode() . "/save.php";
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
     *
     * @return the result of smarty's fetch() operation.
     */
    function default_action()
    {
        $model = $this->createModel();
        $this->assign("form", $model);
        $this->set_context($model);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    /**
     * @brief generate an html document from the 'new form' template, populated with form data from the passed in form_id.
     *
     * @param form_id
     *  The id of the form to populate data from.
     *
     * @return the result of smarty's fetch() operation.
     */
    function view_action($form_id)
    {
        $model = $this->createModel($form_id);
        $this->assign("form", $model);
        $this->set_context($model);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    /**
     * @brief generate a fragment of an HTML document from the 'new form' template, populated with form data from the passed in form_id.
     *
     * @param form_id
     *  The id of the form to populate data from.
     *
     * @return the result of smarty's fetch() operation.
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

        $this->model = $this->createModel($_POST['id']);
        parent::populate_object($this->model);
        $this->model->persist();
        if ($GLOBALS['encounter'] == "") {
            $GLOBALS['encounter'] = date("Ymd");
        }

        if (empty($_POST['id'])) {
            addForm(
                $GLOBALS['encounter'],
                $this->model->getTitle(),
                $this->model->id,
                $this->model->getCode(),
                $GLOBALS['pid'],
                $_SESSION['userauthorized']
            );
            $_POST['process'] = "";
        }
    }
}
