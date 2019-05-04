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

namespace Application\Helper;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Decorates the OpenEMR functions making it so a module can avoid hard coding global functions
 */
class TranslatorViewHelper extends \Zend\View\Helper\AbstractHelper
{

    /**
     * Translates a string.
     */
    public function xl($str)
    {
        return xl($str);
    }
  
  
    /**
     * Translates a function and escapes any html rendering it as strictly text.
     */
    public function escape($str)
    {
        return xlt($str);
    }
  
  /**
   * Translates a function escaping html attribute values
   * @param string $str
   * @return string
   */
    public function safeAttribute($str)
    {
        return xla($str);
    }
  
    /**
   * Language converter
   * @param string $str
   * @return string
   */
    public function safeJavascript($str)
    {
        return xls($str);
    }
}
