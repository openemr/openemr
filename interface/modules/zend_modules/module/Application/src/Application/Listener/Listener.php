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

namespace Application\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * This class is supposed to listen for events in the module like the aclcheckEvent and trigger actions
 * based on those events.  However, it doesn't appear to be used for that at all.  Instead it acts as
 * an adapter for the OpenEMR language conversion within the module system.
 * TODO: We should look at deleting this class or renaming it to be a TranslatorAdapter since that appears
 * to be its functionality here...
 */
class Listener extends AbstractActionController implements ListenerAggregateInterface
{
  /**
   * @var \Zend\Stdlib\CallbackHandler[]
   */
    protected $listeners = array();
    protected $applicationTable;
  /**
   * {@inheritDoc}
   */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        // TODO: This aclcheckEvent doesn't appear to be in the system or used... especially since the callable onAclcheckEvent doesn't exist
        // in this class.  We should look at removing this.
        $sharedEvents      = $events->getSharedManager();
        $this->listeners[] = $events->attach('aclcheckEvent', array($this, 'onAclcheckEvent'));
    }
  

    public function detach(EventManagerInterface $events, $priority = 1)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
  
  /**
   * Language converter
   * @param string $str
   * @return string
   */
    public function z_xl($str)
    {
        return xl($str);
    }
  
  /**
   * Language converter
   * @param string $str
   * @return string
   */
    public function z_xlt($str)
    {
        return xlt($str);
    }
  
  /**
   * Language converter
   * @param string $str
   * @return string
   */
    public function z_xla($str)
    {
        return xla($str);
    }
  
    /**
   * Language converter
   * @param string $str
   * @return string
   */
    public function z_xls($str)
    {
        return xls($str);
    }
}
