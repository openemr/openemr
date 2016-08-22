<?php
/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */

$tabs=true;
if(isset($_REQUEST['tabs']))
{
    if($_REQUEST['tabs']==='false')
    {
        $tabs=false;
        ?>
        <script type="text/javascript">
            top.tab_mode=false;
        </script>
        <?php
    }
}
if ($tabs===true)
{
    $tabs_base_url=$web_root."/interface/main/tabs/main.php?url=".urlencode($frame1url);
    header('Location: '.$tabs_base_url);
    exit();
}
if(isset($_REQUEST['analysis']))
{
    if($_REQUEST['analysis']==='true')
    {
        ?>
            <script type="text/javascript" src="tabs/js/menu_analysis.js"></script>
        <?php
    }
}


?>
