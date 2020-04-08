<?php
/**
 * transfer summary form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Naina Mohamed <naina@capminds.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2013 Naina Mohamed <naina@capminds.com> CapMinds Technologies
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__FILE__).'/../../globals.php');
require_once(dirname(__FILE__) ."/../../../library/acl.inc");
require_once(dirname(__FILE__) ."/../../../library/api.inc");
require_once(dirname(__FILE__) ."/../../../library/lists.inc");
require_once(dirname(__FILE__) ."/../../../library/forms.inc");
require_once(dirname(__FILE__) ."/../../../library/patient.inc");
require_once(dirname(__FILE__) ."/../../../controllers/C_Document.class.php");


function telemed_report($pid, $encounter, $cols, $id)
{
    
    $data = formFetch("form_telemed", $id);
    echo "<div class='container'><div class='row'><div class='col-sm-5 offset-2'>";
    echo "<h4>".xlt('Subjective').":</h4>";
    echo "<span>".text($data['tm_subj'])."</span>";
    echo "<h4>".xlt('Objective').":</h4>";
    echo "<span>".text($data['tm_obj'])."</span>";
    echo "<h4>".xlt('Impression').":</h4>";
    echo "<span>".text($data['tm_imp'])."</span>";
    echo "<h4>".xlt('Plan').":</h4>";
    echo "<span>".text($data['tm_plan'])."</span>";
    echo "</div></div></div>";
}

