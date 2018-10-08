<?php
 /**
 * Dash Board nav for reports.
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
                $arr_pat_menu_items = array (
                    "Dashboard"=>"../patient_file/summary/demographics.php",
                    "History"=>"../patient_file/history/history.php",
                    "Report"=>"../patient_file/report/patient_report.php",
                    "Documents"=>"../../controller.php?document&list&patient_id=",
                    "Transactions"=>"../patient_file/transaction/transactions.php",
                    "Issues"=>"../patient_file/summary/stats_full.php?active=all",
                    "Ledger"=>"../reports/pat_ledger.php?form=1&patient_id=",
                    "External Data"=>"../reports/external_data.php"
                );

                $li_id = 1;

                foreach ($arr_pat_menu_items as $pat_menu_item => $url) {
                    echo '<li class="oe-bold-black" id="nav-list'. $li_id.'">';
                    echo '<a href="'. $url.'" onclick="top.restoreSession()">'. xlt($pat_menu_item) .' </a>';
                    echo '</li>';
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