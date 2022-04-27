<?php

/**
 * Displays the documents
 * Only Lab documents for now.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Hema Bandaru <hemab@drcloudemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Harshal Lele <harshallele97@gmail.com>
 * @copyright Copyright (c) 2014 Ensoftek
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Harshal Lele <harshallele97@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'lab')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Lab Documents")]);
    exit;
}

$curdate = date_create(date("Y-m-d"));
date_sub($curdate, date_interval_create_from_date_string("7 days"));
$sub_date = date_format($curdate, 'Y-m-d');

// Set the default dates for Lab document search
$form_from_doc_date = ($_GET['form_from_doc_date'] ?? oeFormatShortDate($sub_date));
$form_to_doc_date = ($_GET['form_to_doc_date'] ?? oeFormatShortDate(date("Y-m-d")));

if ($GLOBALS['date_display_format'] == 1) {
    $title_tooltip = "MM/DD/YYYY";
} elseif ($GLOBALS['date_display_format'] == 2) {
    $title_tooltip = "DD/MM/YYYY";
} else {
    $title_tooltip = "YYYY-MM-DD";
}

$display_div = "style='display:block;'";

?>
<html>
<head>
<?php
    Header::setupHeader(['datetime-picker', 'common']);
    require_once("$srcdir/payment_jav.inc.php");
?>

<script>
    var global_date_format = '<?php echo DateFormatRead(); ?>';
    $(function () {
        $("#docdiv a").each(function() {

            let name = $(this).get(0);

            let tooltip = document.getElementsByClassName('tooltip_container')[0];
            let tooltipDoc = document.getElementsByClassName('tooltip_doc')[0];

            let tooltipVisible = false;

            name.addEventListener('mouseenter',() => {
                //check if the document is already visible
                if(!tooltipVisible){

                    //set the position of tooltip to that of the table cell
                    let rect = name.getBoundingClientRect();
                    let nameTop = rect.top + window.pageYOffset;
                    let nameLeft = rect.left + window.pageXOffset;
                    tooltip.style.left = nameLeft;
                    tooltip.style.top = nameTop;

                    tooltipDoc.src = $(this).attr('title');
                    tooltip.style.display = 'block';
                    tooltipDoc.style.maxHeight = '100%';

                    tooltipVisible = true;
                }

            });
            //hide the tooltip when the cursor goes out of the image
            tooltip.addEventListener('mouseleave',() => {
                tooltip.style.display = 'none';
                tooltipVisible = false;
            });

        })

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });

    function validateDate(fromDate,toDate){
        var frmdate = $("#" + fromDate).val();
        var todate = $("#" + toDate).val();
        if ( (frmdate.length > 0) && (todate.length > 0) ) {
            if ( DateCheckGreater(frmdate, todate, global_date_format) == false ){
                alert(<?php echo xlj('To date must be later than From date!'); ?>);
                return false;
            }
        }
        document.location='<?php echo $GLOBALS['webroot']; ?>/interface/main/display_documents.php?form_from_doc_date=' + encodeURIComponent(frmdate) + '&form_to_doc_date=' + encodeURIComponent(todate);
    }

</script>

<style>

.linkcell {
    max-width: 250px;
    text-overflow: ellipsis;
    overflow: hidden;
}

.tooltip_container{
    background-color: var(--gray);
    width: 75%;
    height: 50%;
    z-index: 1;
    display: none;
    position: absolute;
    box-sizing: border-box;
    border: 10px solid var(--gray);
}

.tooltip_doc {
    width: 100%;
    height: 100%;
}
</style>
</head>

<body>
<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <h2 class='title'><?php echo xlt('Lab Documents'); ?></h2>
            <br />
            <div id='docfilterdiv'<?php echo $display_div; ?>>
                <div class="form-inline mb-2">
                    <label for='form_from_doc_date' class='label_custom mx-1'><?php echo xlt('From'); ?>:</label>
                    <input type='text' class='form-control datepicker mx-1' name='form_from_doc_date' id="form_from_doc_date" size='10' value='<?php echo attr($form_from_doc_date) ?>' title='<?php echo attr($title_tooltip) ?>' />
                    <label for='form_to_doc_date' class='label_custom mx-1'><?php echo xlt('To{{Range}}'); ?>:</label>
                    <input type='text' class='form-control datepicker mx-1' name='form_to_doc_date' id="form_to_doc_date" size='10' value='<?php echo attr($form_to_doc_date) ?>' title='<?php echo attr($title_tooltip) ?>' />
                    <span id="docrefresh" class="mx-1">
                        <button type="button" class="btn btn-primary btn-refresh" onclick='return validateDate("form_from_doc_date","form_to_doc_date")'>
                            <?php echo xlt('Refresh'); ?>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class='col-12' id='docdiv' <?php echo $display_div; ?>>
        <?php
        $current_user = $_SESSION['authUserID'];
        $date_filter = '';
            $query_array = array();
        if ($form_from_doc_date) {
            $form_from_doc_date = DateToYYYYMMDD($form_from_doc_date);
            $date_filter = " DATE(d.date) >= ? ";
                    array_push($query_array, $form_from_doc_date);
        }

        if ($form_to_doc_date) {
            $form_to_doc_date = DateToYYYYMMDD($form_to_doc_date);
            $date_filter .= " AND DATE(d.date) <= ? ";
                    array_push($query_array, $form_to_doc_date);
        }

        // Get the category ID for lab reports.
        $query = "SELECT rght FROM categories WHERE name = ?";
        $catIDRs = sqlQuery($query, array($GLOBALS['lab_results_category_name']));
        $catID = $catIDRs['rght'];

        $query = "SELECT d.*,CONCAT(pd.fname,' ',pd.lname) AS pname,GROUP_CONCAT(n.note ORDER BY n.date DESC SEPARATOR '|') AS docNotes,
            GROUP_CONCAT(n.date ORDER BY n.date DESC SEPARATOR '|') AS docDates FROM documents d
            INNER JOIN patient_data pd ON d.foreign_id = pd.pid
            INNER JOIN categories_to_documents ctd ON d.id = ctd.document_id AND ctd.category_id = ?
            LEFT JOIN notes n ON d.id = n.foreign_id
            WHERE " . $date_filter . " GROUP BY d.id ORDER BY date DESC";
            array_unshift($query_array, $catID);
        $resultSet = sqlStatement($query, $query_array);
        ?>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class='thead-light'>
                    <tr class='text font-weight-bold text-left'>
                        <th width="10%"><?php echo xlt('Date'); ?></th>
                        <th class="linkcell" width="20%"><?php echo xlt('Name'); ?></th>
                        <th><?php echo xlt('Patient'); ?></th>
                        <th><?php echo xlt('Note'); ?></th>
                        <th width="10%"><?php echo xlt('Encounter ID'); ?></th>
                    </tr>
                </thead>
                <?php
                if (sqlNumRows($resultSet)) {
                    while ($row = sqlFetchArray($resultSet)) {
                        $url = $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=" . attr_url($row["foreign_id"]) . "&document_id=" . attr_url($row["id"]) . '&as_file=false';
                        // Get the notes for this document.
                        $notes = array();
                        $note = '';
                        if ($row['docNotes']) {
                            $notes = explode("|", $row['docNotes']);
                            $dates = explode("|", $row['docDates']);
                        }

                        for ($i = 0; $i < count($notes); $i++) {
                            $note .= text(oeFormatShortDate(date('Y-m-d', strtotime($dates[$i])))) . " : " . text($notes[$i]) . "<br />";
                        }
                        ?>
                        <tr class="text">
                            <td><?php echo text(oeFormatShortDate(date('Y-m-d', strtotime($row['date'])))); ?> </td>
                            <td class="linkcell">
                                <a id="<?php echo attr($row['id']); ?>" title='<?php echo $url; ?>' onclick='top.restoreSession()'><?php echo text(basename($row['url'])); ?></a>
                            </td>
                            <td><?php echo text($row['pname']); ?> </td>
                            <td><?php echo $note; ?> &nbsp;</td>
                            <td class="text-center"><?php echo ( $row['encounter_id'] ) ? text($row['encounter_id']) : ''; ?> </td>
                        </tr>
                        <?php
                    } ?>
                    <?php
                } ?>
            </table>
        </div>
    </div>
    <div class="tooltip_container">
        <iframe class="tooltip_doc"></iframe>
    </div>
</div>
</body>
</html>
