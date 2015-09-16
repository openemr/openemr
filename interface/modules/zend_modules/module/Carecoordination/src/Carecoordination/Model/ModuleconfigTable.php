<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Vinish K  <vinish@zhservices.com>
// +------------------------------------------------------------------------------+
namespace Carecoordination\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway; 
use Zend\Db\Adapter\Adapter; 
use Zend\Db\ResultSet\ResultSet; 
use Zend\Db\Sql\Select;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ModuleconfigTable extends AbstractTableGateway
{
    public function getUsers()
    {
		$users = array('0' => '');
		$res = $this->applicationTable->zQuery(("SELECT id, fname, lname, street, city, state, zip  FROM users WHERE abook_type='ccda'"));
		foreach($res as $row){
			$users[$row['id']] = $row['fname']." ".$row['lname'];
		}
		return $users;
    }
}
?>