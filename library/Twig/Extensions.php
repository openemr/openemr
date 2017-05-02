<?php
/**
 * Custom OpenEMR Extensions for Twig
 *
 * Call this from a globally accessible place so we can have custom extensions
 * added from a central place
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @subpackage Twig
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (C) 2017 Robert Down
 */

namespace OpenEMR\Twig;

require_once dirname(__FILE__) . "/../../interface/globals.php";

use Twig_SimpleFilter;

class Extension
{

    static public function translate($string)
    {
        return xl($string);
    }
}

