<?php

/* +-----------------------------------------------------------------------------+
* Copyright 2016 matrix israel
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see
* http://www.gnu.org/licenses/licenses.html#GPL
*    @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
* +------------------------------------------------------------------------------+
 *
 */
namespace Ldap\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Ldap\Ldap;

class LdapController extends BaseController{

    public function __construct()
    {
        parent::__construct();

    }



    public function indexAction()
    {

        $this->getJsFiles();
        $this->getCssFiles();
        $this->layout()->setVariable('jsFiles', $this->jsFiles);
        $this->layout()->setVariable('cssFiles', $this->cssFiles);

        $options = array(
                'host'              => '127.0.0.1',
                'port'              => '389',
                'username'          => 'CN=admin,DC=test,DC=com',
                'password'          => 'pass',
                'bindRequiresDn'    => true,
                'accountDomainName' => 'test.com',
                'baseDn'            => 'DC=test,DC=com',
        );
        $ldap = new Ldap($options);
        $ldap->bind();
        $result = $ldap->getEntry('cn=admin,dc=test,dc=com');

        return array(
            'aaa'=>$result,
            );
    }



}
