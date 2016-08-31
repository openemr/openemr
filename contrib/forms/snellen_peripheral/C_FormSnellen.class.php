<?php
/**
 * Copyright (C) 2008-2016
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
 * @package OpenEMR
 * @author  Aron Racho <aron@mi-squared.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
 */

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once ($GLOBALS['fileroot'] . "/library/forms.inc");
require_once("FormSnellen.class.php");

class C_FormSnellen extends Controller {

  var $template_dir;

  function C_FormSnellen($template_mod = "general") {
    parent::Controller();
    $this->template_mod = $template_mod;
    $this->template_dir = dirname(__FILE__) . "/templates/";
    $this->assign("FORM_ACTION", $GLOBALS['web_root']);
    $this->assign("DONT_SAVE_LINK",$GLOBALS['form_exit_url']);
    $this->assign("STYLE", $GLOBALS['style']);
  }

  function default_action() {
    $form = new FormSnellen();
    $this->assign("data",$form);
    return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
  }

  function view_action($form_id) {
    if (is_numeric($form_id)) {
      $form = new FormSnellen($form_id);
    }
    else {
      $form = new FormSnellen();
    }
    $dbconn = $GLOBALS['adodb']['db'];
    $this->assign("data",$form);
    return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
  }

  function default_action_process() {
    if ($_POST['process'] != "true")
      return;
    $this->form = new FormSnellen($_POST['id']);
    parent::populate_object($this->form);
    $this->form->persist();
    if ($GLOBALS['encounter'] == "") {
      $GLOBALS['encounter'] = date("Ymd");
    }
    if (empty($_POST['id'])) {
      addForm($GLOBALS['encounter'], "Snellen with Peripheral Exam", $this->form->id, "snellen_peripheral",
        $GLOBALS['pid'], $_SESSION['userauthorized']);
      $_POST['process'] = "";
    }
    return;
  }

}
?>
