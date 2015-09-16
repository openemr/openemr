<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
*    @author  Basil PT <basil@zhservices.com>
* +------------------------------------------------------------------------------+
*/

namespace Application\Helper;
use Zend\View\Helper\AbstractHelper;
 
class Javascript extends AbstractHelper
{
  public function __invoke()
  {
    switch (true) {
      case (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)):
      case (isset($_SERVER['HTTP_SCHEME']) && ($_SERVER['HTTP_SCHEME'] == 'https')):
      case (443 === $_SERVER['SERVER_PORT']):
          $scheme = 'https://';
          break;
      default:
          $scheme = 'http://';
          break;
    }
    $basePath = str_replace("/index.php","",$_SERVER['PHP_SELF']);
    echo '<script type="text/javascript">';
    echo 'var basePath    = "'.$scheme.$_SERVER['SERVER_NAME'].$basePath.'";';
    echo 'var dateFormat = "yy-mm-dd"';
    echo '</script>';
  }
}
?>