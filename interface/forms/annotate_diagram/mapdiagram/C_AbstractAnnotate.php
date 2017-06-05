<?php
/**
 * Copyright Medical Information Integration,LLC info@mi-squared.com
 * 
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * Rewrite and modifications by sjpadgett@gmail.com Padgetts Consulting 2016.
 *
 * @file C_AbstractAnnotate.php
 *
 * @brief This file contains the C_AbstractAnnotate class, used to control smarty.
 *
 * @package OpenEMR
 * @author  Medical Information Integration,LLC <info@mi-squared.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com> 
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://www.open-emr.org
 */

/* for $GLOBALS['concurrent_layout','encounter','fileroot','pid','srcdir','style','webroot'] 
 * remember that include paths are calculated relative to the including script, not this file.
 * to lock the path to this script (so if called from different scripts) use the dirname(FILE) variable
*/
require_once('../../globals.php');

/* For Controller, the class we're extending. */
require_once ($GLOBALS['srcdir'] . '/classes/Controller.class.php');

/* For the addform() function */
require_once ($GLOBALS['srcdir'] . '/forms.inc');

/**
 * @class C_AbstractAnnotate
 *
 * @brief This class extends the Controller class, which is used to control the smarty templating engine.
 *
 */
abstract class C_AbstractAnnotate extends Controller {
    /**
     * the directory to find our template file in.
     *
     * @var template_dir
     */
    var $template_dir;
	var $newTitle;
    /**
     * @brief Initialize a newly created object belonging to this class
     *
     * @param template_mod
     *  template module name, passed to Controller's initializer.
     */
    function C_AbstractAnnotate($template_mod = "annotate") {
    	parent::__construct();
    	$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
    	$this->template_mod = $template_mod;
    	$this->template_dir = $GLOBALS['fileroot'] . "/interface/forms/annotate_diagram/mapdiagram/template/";
    	$this->assign("DONT_SAVE_LINK",$GLOBALS['webroot'] . "/interface/patient_file/encounter/$returnurl");
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
     *  An AbstractAnnotateModel derived Object.
     */
    abstract public function createModel($form_id="");

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
    private function set_context( $model ) {
        $root = $GLOBALS['webroot'] . "/interface/forms/annotate_diagram/mapdiagram";
        $model->saveAction = $GLOBALS['webroot'] . "/interface/forms/" . $model->getCode() . "/save.php";
        $model->template_dir = $root . "/template";
        
        $optionList = $this->getOptionList();
        $model->optionList = $optionList != null ? json_encode($optionList) : "null";
        $optionsLabel = $this->getOptionsLabel();
        $model->optionsLabel = isset($optionsLabel) ? "'" . $optionsLabel . "'" : "null";
        $data = $model->get_data();
        $model->data = $data != "" ? "'" . $data . "'" : "null";
        $model->hideNav = "false";
		$model->image = $this->getImage();
		$imagedata = $model->get_imagedata();
		$model->image = $imagedata;
		$dyntitle = $model->get_dyntitle();
		$model->dyntitle = $dyntitle;
    }

    /**
     * @brief generate an html document from the 'new form' template
     *
     * @return the result of smarty's fetch() operation.
     */
    function default_action() {
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
    function view_action($form_id) {
        $model = $this->createModel($form_id);
    	$this->assign("form",$model);
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
    function report_action($form_id) {
        $model = $this->createModel($form_id);
    	$this->assign("form",$model);
        $this->set_context($model);
        $model->hideNav = "true";
    	return $this->fetch($this->template_dir . $this->template_mod . "_rpt.html");
    }

     /**
     * @brief called to store the submitted form's contents to the database, adding the form to the encounter if necissary.
     */
   function default_action_process() {
        if ($_POST['process'] != "true") {
            return;
        }
        $this->model = $this->createModel($_POST['id']);
        parent::populate_object($this->model);
        $this->model->persist();
        if ($GLOBALS['encounter'] == "") {
            $GLOBALS['encounter'] = date("Ymd");
        }
        if(empty($_POST['id'])) {
            addForm($GLOBALS['encounter'], 
                    $_POST['dyntitle'],
                    $this->model->id,
                    $this->model->getCode(),
                    $GLOBALS['pid'],
                    $_SESSION['userauthorized']
            );
            $_POST['process'] = "";
        }
    }
}
?>