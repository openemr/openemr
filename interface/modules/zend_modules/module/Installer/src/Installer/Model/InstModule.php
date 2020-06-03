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
    public $sql_version;
    public $acl_version;
    public function exchangeArray($data)
    {
        $this -> modId                  = isset($data['mod_id']) ? $data['mod_id'] : null;
        $this -> modName              = isset($data['mod_name']) ? $data['mod_name'] : null;
        $this -> modDirectory         = isset($data['mod_directory']) ? $data['mod_directory'] : null;
        $this -> modParent              = isset($data['mod_parent']) ? $data['mod_parent'] : null;
        $this -> modType              = isset($data['mod_type']) ? $data['mod_type'] : null;
        $this -> modActive              = isset($data['mod_active']) ? $data['mod_active'] : null;
        $this -> modUiName              = isset($data['mod_ui_name']) ? $data['mod_ui_name'] : null;
        $this -> modRelativeLink  = isset($data['mod_relative_link']) ? $data['mod_relative_link'] : null;
        $this -> modUiOrder             = isset($data['mod_ui_order']) ? $data['mod_ui_order'] : null;
        $this -> modUiActive          = isset($data['mod_ui_active']) ? $data['mod_ui_active'] : null;
        $this -> modDescription       = isset($data['mod_description']) ? $data['mod_description'] : null;
        $this -> modnickname          = isset($data['mod_nick_name']) ? $data['mod_nick_name'] : null;
        $this -> modencmenu             = isset($data['mod_enc_menu']) ? $data['mod_enc_menu'] : null;
        $this -> permissionItemTable  = isset($data['permission_item_table']) ? $data['permission_item_table'] : null;
        $this -> directory              = isset($data['directory']) ? $data['directory'] : null;
        $this -> date                   = isset($data['date']) ? $data['date'] : null;
        $this -> sqlRun                   = isset($data['sql_run']) ? $data['sql_run'] : null;
        $this -> type                   = isset($data['type']) ? $data['type'] : null;
        $this -> fld_type             = isset($data['fld_type']) ? $data['fld_type'] : null;
        $this -> obj_name             = isset($data['obj_name']) ? $data['obj_name'] : null;
        $this -> menu_name              = isset($data['menu_name']) ? $data['menu_name'] : null;
        $this -> id                       = isset($data['id']) ? $data['id'] : null;
        $this -> name                   = isset($data['name']) ? $data['name'] : null;
        $this -> group_id             = isset($data['group_id']) ? $data['group_id'] : null;
        $this -> user                   = isset($data['user']) ? $data['user'] : null;
        $this -> cnt                    = isset($data['cnt']) ? $data['cnt'] : null;
        $this -> mod_directory        = isset($data['mod_directory']) ? $data['mod_directory'] : null;
        $this -> enabled_hooks        = isset($data['enabled_hooks']) ? $data['enabled_hooks'] : null;
        $this -> attached_to          = isset($data['attached_to']) ? $data['attached_to'] : null;
        $this -> sql_version          = isset($data['sql_version']) ? $data['sql_version'] : null;
        $this -> acl_version          = isset($data['acl_version']) ? $data['acl_version'] : null;
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
