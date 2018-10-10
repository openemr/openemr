<?php
 /**
 * Dash Board nav.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
    ?>

<nav class="navbar navbar-default navbar-color navbar-static-top" >
    <div class="container-fluid">
        <div class="navbar-header">
            <button class="navbar-toggle" data-target="#myNavbar" data-toggle="collapse" type="button"><span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span></button>
        </div>
        <div class="collapse navbar-collapse" id="myNavbar" >
            <ul class="nav navbar-nav" >
            
                <?php
                $li_id = 1; // first id given to dashboard
                foreach ($menu_restrictions as $key => $value) {
                    if (!empty($value->children)) {
                        // flatten to only show children items
                        foreach ($value->children as $children_key => $children_value) {
                            $link = ($children_value->pid != "true") ? $children_value->url : $children_value->url . attr($pid);
                            echo '<li class="oe-bold-black" id="nav-list'. $li_id.'">';
                            echo '<a href="' . $link . '" onclick="' . $children_value->on_click .'"> ' . text($children_value->label) . ' </a>';
                            echo '</li>';
                        }
                    } else {
                        $link = ($value->pid != "true") ? $value->url : $value->url . attr($pid);
                        echo '<li class="oe-bold-black"id="nav-list'. $li_id.'">';
                        echo '<a href="' . $link . '" onclick="' . $value->on_click .'"> ' . text($value->label) . ' </a>';
                        echo '</li>';
                    }
                    $li_id++;
                }
                ?>
            </ul>
        </div>
    </div>
</nav>