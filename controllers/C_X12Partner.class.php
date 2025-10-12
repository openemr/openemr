<?php

/**
 * controller class for x-12 partner screen
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Daniel Pflieger <daniel@mi-squared.com>, <daniel@growlingflea.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Daniel Pflieger <daniel@mi-squared.com>, <daniel@growlingflea.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Crypto\CryptoGen;

class C_X12Partner extends Controller
{
    public $providers;
    public $x12_partners;

    function __construct(public $template_mod = "general")
    {
        parent::__construct();
        $this->x12_partners = [];
        $this->assign("FORM_ACTION", $GLOBALS['webroot'] . "/controller.php?" . attr($_SERVER['QUERY_STRING']));
        $this->assign("CURRENT_ACTION", $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&x12_partner&");
        $this->assign("STYLE", $GLOBALS['style']);
    }

    function default_action()
    {
        return $this->list_action();
    }

    function edit_action($id = "", $x_obj = null)
    {
        if ($x_obj != null && $x_obj::class == "x12partner") {
            $this->x12_partners[0] = $x_obj;
        } elseif (is_numeric($id)) {
            $this->x12_partners[0] = new X12Partner($id);
        } else {
            $this->x12_partners[0] = new X12Partner();
        }

        // If we have an SFTP password set, decrypt it
        if ($this->x12_partners[0]->get_x12_sftp_pass()) {
            $cryptoGen = new CryptoGen();
            $this->x12_partners[0]->set_x12_sftp_pass($cryptoGen->decryptStandard($this->x12_partners[0]->get_x12_sftp_pass()));
        }

        $this->assign("partner", $this->x12_partners[0]);
        return $this->fetch($GLOBALS['template_dir'] . "x12_partners/" . $this->template_mod . "_edit.html");
    }

    function list_action()
    {

        $x = new X12Partner();
        $this->assign("partners", $x->x12_partner_factory());
        return $this->fetch($GLOBALS['template_dir'] . "x12_partners/" . $this->template_mod . "_list.html");
    }


    function edit_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        //print_r($_POST);
        $this->x12_partners[0] = is_numeric($_POST['id']) ? new X12Partner($_POST['id']) : new X12Partner();

        parent::populate_object($this->x12_partners[0]);

        // If we are setting the SFTP password, encrypt it
        if (!empty($_POST['x12_sftp_pass'])) {
            $cryptoGen = new CryptoGen();
            $this->x12_partners[0]->x12_sftp_pass = $cryptoGen->encryptStandard($this->x12_partners[0]->x12_sftp_pass);
        }

        $this->x12_partners[0]->persist();
        //insurance numbers need to be repopulated so that insurance_company_name recieves a value
        $this->x12_partners[0]->populate();

        //echo "action processeed";
        $_POST['process'] = "";
        $this->_state = false;
        header('Location:' . $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&x12_partner&action=list");//Z&H
        //return $this->edit_action(null,$this->x12_partner[0]);
    }
}
