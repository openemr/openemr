<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/view/layout/mapper.phtml
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Vipin Kumar <vipink@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Installer\Model;

use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class InstModule implements InputFilterAwareInterface
{
    public $modId;
    public $modName;
    public $modDirectory;
    public $modParent;
    public $modType;
    public $modActive;
    public $modUiName;
    public $modRelativeLink;
    public $modUiOrder;
    public $modUiActive;
    public $modDescription;
    public $modnickname;
    public $modencmenu;
    public $permissionItemTable;
    public $directory;
    public $date;
    public $sqlRun;
    public $type;
    public $fld_type;
    public $obj_name;
    public $menu_name;
    public $id;
    public $name;
    public $group_id;
    public $user;
    public $cnt;
    public $mod_directory;
    public $enabled_hooks;
    public $attached_to;
    public $sql_action;
    public $sql_version;
    public $acl_action;
    public $acl_version;

    public ?InputFilterInterface $inputFilter = null;
    public function exchangeArray($data)
    {
        $this -> modId                  = $data['mod_id'] ?? null;
        $this -> modName              = $data['mod_name'] ?? null;
        $this -> modDirectory         = $data['mod_directory'] ?? null;
        $this -> modParent              = $data['mod_parent'] ?? null;
        $this -> modType              = $data['mod_type'] ?? null;
        $this -> modActive              = $data['mod_active'] ?? null;
        $this -> modUiName              = $data['mod_ui_name'] ?? null;
        $this -> modRelativeLink  = $data['mod_relative_link'] ?? null;
        $this -> modUiOrder             = $data['mod_ui_order'] ?? null;
        $this -> modUiActive          = $data['mod_ui_active'] ?? null;
        $this -> modDescription       = $data['mod_description'] ?? null;
        $this -> modnickname          = $data['mod_nick_name'] ?? null;
        $this -> modencmenu             = $data['mod_enc_menu'] ?? null;
        $this -> permissionItemTable  = $data['permission_item_table'] ?? null;
        $this -> directory              = $data['directory'] ?? null;
        $this -> date                   = $data['date'] ?? null;
        $this -> sqlRun                   = $data['sql_run'] ?? null;
        $this -> type                   = $data['type'] ?? null;
        $this -> fld_type             = $data['fld_type'] ?? null;
        $this -> obj_name             = $data['obj_name'] ?? null;
        $this -> menu_name              = $data['menu_name'] ?? null;
        $this -> id                       = $data['id'] ?? null;
        $this -> name                   = $data['name'] ?? null;
        $this -> group_id             = $data['group_id'] ?? null;
        $this -> user                   = $data['user'] ?? null;
        $this -> cnt                    = $data['cnt'] ?? null;
        $this -> mod_directory        = $data['mod_directory'] ?? null;
        $this -> enabled_hooks        = $data['enabled_hooks'] ?? null;
        $this -> attached_to          = $data['attached_to'] ?? null;
        $this -> sql_version          = $data['sql_version'] ?? null;
        $this -> acl_version          = $data['acl_version'] ?? null;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        return $this->inputFilter;
    }
}
