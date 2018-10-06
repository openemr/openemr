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
                // // Get the document ID of the patient ID card if access to it is wanted here.
                // $idcard_doc_id = false;
                // if ($GLOBALS['patient_id_category_name']) {
                    // $idcard_doc_id = get_document_by_catg($pid, $GLOBALS['patient_id_category_name']);
                // }
                echo '<li class="oe-bold-black" id = "nav-list1">';
                echo '<a href="../summary/demographics.php" onclick="top.restoreSession()">'. xlt('Dashboard') .' </a>';
                echo '</li>';
                
                $li_id = 2; // first id given to dashboard
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
                //temporarily disabled
                //echo '<li class="oe-bold-black" id = "nav-list'. attr($li_id) .'">';
                //echo '<a href="../encounter/dashboard_encounters.php" onclick="top.restoreSession()">'. xlt('Encounters') .' </a>';
                //echo '</li>';
                ?>
            </ul>
        </div>
    </div>
</nav>