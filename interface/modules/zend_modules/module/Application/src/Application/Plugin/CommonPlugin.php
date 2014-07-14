<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*    @author  Remesh Babu S <remesh@zhservices.com>
* +------------------------------------------------------------------------------+
*/
namespace Application\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Application\Model\ApplicationTable;
use Application\Listener\Listener;

class CommonPlugin extends AbstractPlugin 
{
  
  /**
   * Application Table Object 
   * Listener Oblect
   * @param type $sm Service Manager
   */
  public function __construct($sm)
  { 
    $sm->get('Zend\Db\Adapter\Adapter');
    $this->application    = new ApplicationTable();
    $this->listenerObject	= new Listener;
  }
  
  /**
   * Function checkACL
   * Plugin functions are easily access from any where in the project 
   * Call the ACL Check function zAclCheck from ApplicationTable
   *  
   * @param int     $useID
   * @param string  $sectionID
   * @return type
   */
  public function checkACL($useID, $sectionID)
  {
    return $this->application->zAclCheck($useID, $sectionID);
  }
}