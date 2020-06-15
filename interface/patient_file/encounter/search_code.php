<?php

/**
 * search_code.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

//the maximum number of records to pull out with the search:
$M = 30;

//the number of records to display before starting a second column:
$N = 15;

$code_type = $_GET['type'];
?>

<html>
<head>
<?php Header::setupHeader(); ?>

</head>
<body class="body_bottom">
<div id="patient_search_code">

<table class="table-borderless h-100" cellspacing='0' cellpadding='0'>
<tr>

<td class="align-top">

<form name="search_form" id="search_form" method="post" action="search_code.php?type=<?php echo attr_url($code_type); ?>">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<input type="hidden" name="mode" value="search" />

<span class="title"><?php echo text($code_type); ?> <?php echo xlt('Codes'); ?></span><br />

<input type="textbox" id="text" name="text" size="15" />

<input type='submit' id="submitbtn" name="submitbtn" value='<?php echo xla('Search'); ?>' />
<!-- TODO: Use BS4 classes here !-->
<div id="searchspinner" style="display: inline; visibility: hidden;"><img src="<?php echo $GLOBALS['webroot'] ?>/interface/pic/ajax-loader.gif"></div>

</form>

<?php
if (isset($_POST["mode"]) && $_POST["mode"] == "search" && $_POST["text"] == "") {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    echo "<div id='resultsummary bg-success'>";
    echo "Enter search criteria above</div>";
}

if (isset($_POST["mode"]) && $_POST["mode"] == "search" && $_POST["text"] != "") {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

  // $sql = "SELECT * FROM codes WHERE (code_text LIKE '%" . $_POST["text"] .
  //   "%' OR code LIKE '%" . $_POST["text"] . "%') AND code_type = '" .
  //   $code_types[$code_type]['id'] . "' ORDER BY code LIMIT " . ($M + 1);

  // The above is obsolete now, fees come from the prices table:
    $sql = "SELECT codes.*, prices.pr_price FROM codes " .
    "LEFT OUTER JOIN patient_data ON patient_data.pid = ? " .
    "LEFT OUTER JOIN prices ON prices.pr_id = codes.id AND " .
    "prices.pr_selector = '' AND " .
    "prices.pr_level = patient_data.pricelevel " .
    "WHERE (code_text LIKE ? OR " .
    "code LIKE ?) AND " .
    "code_type = ? " .
    "ORDER BY code " .
    " LIMIT " . escape_limit(($M + 1)) .
    "";

    if ($res = sqlStatement($sql, array($pid, "%" . $_POST["text"] . "%", "%" . $_POST["text"] . "%", $code_types[$code_type]['id']))) {
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result[$iter] = $row;
        }

        echo "<div id='resultsummary bg-success'>";
        if (count($result) > $M) {
            echo "Showing the first " . text($M) . " results";
        } elseif (count($result) == 0) {
            echo "No results found";
        } else {
            echo "Showing all " . text(count($result)) . " results";
        }

        echo "</div>";
        ?>
<div id="results">
<table>
  <tr class='text'>
    <td class='align-top'>
        <?php
        $count = 0;
        $total = 0;

        if ($result) {
            foreach ($result as $iter) {
                if ($count == $N) {
                    echo "</td><td class='align-top'>\n";
                    $count = 0;
                }

                echo "<div class='oneresult' style='padding: 3px 0 3px 0;'>";
                echo "<a target='" . xla('Diagnosis') . "' href='diagnosis.php?mode=add" .
                    "&type="     . attr_url($code_type) .
                    "&code="     . attr_url($iter["code"]) .
                    "&modifier=" . attr_url($iter["modifier"]) .
                    "&units="    . attr_url($iter["units"]) .
                    // "&fee="      . attr_url($iter["fee"]) .
                    "&fee="      . attr_url($iter['pr_price']) .
                    "&text="     . attr_url($iter["code_text"]) .
                    "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) .
                    "' onclick='top.restoreSession()'>";
                echo ucwords("<b>" . text(strtoupper($iter["code"])) . "&nbsp;" . text($iter['modifier']) .
                    "</b>" . " " . text(strtolower($iter["code_text"])));
                echo "</a><br />\n";
                echo "</div>";

                $count++;
                $total++;

                if ($total == $M) {
                    echo "</span><span class='alert-custom'>" . xlt('Some codes were not displayed.') . "</span>\n";
                    break;
                }
            }
        }
        ?>
</td></tr></table>
</div>
        <?php
    }
}
?>

</td>
</tr>
</table>

</div> <!-- end large outer patient_search_code DIV -->
</body>

<script>

// jQuery stuff to make the page a little easier to use

$(function () {
    $("#text").trigger("focus");
    $(".oneresult").on("mouseover", function() { $(this).toggleClass("highlight"); });
    $(".oneresult").on("mouseout", function() { $(this).toggleClass("highlight"); });
    //$(".oneresult").click(function() { SelectPatient(this); });
    $("#search_form").on("submit", function() { SubmitForm(this); });
});

// show the 'searching...' status and submit the form
var SubmitForm = function(eObj) {
    $("#submitbtn").attr("disabled", "true");
    $("#submitbtn").css("disabled", "true");
    $("#searchspinner").css("visibility", "visible");
    return top.restoreSession();
}

</script>

</html>
