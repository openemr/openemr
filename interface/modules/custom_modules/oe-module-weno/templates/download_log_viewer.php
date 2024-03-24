<?php

require_once(dirname(__DIR__, 4) . "/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'super')) {
    // renders in MM iFrame
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Must be an Admin")]);
    exit;
}

$startDate = $_GET['startDate'] ?? date('m/d/Y'); // just default to today
$endDate = $_GET['endDate'] ?? date('m/d/Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xlt('Weno Log'); ?></title>
    <?php Header::setupHeader(['datetime-picker']); ?>
</head>
<script>
    $(function () {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        });
    });
</script>
<body>
    <div class="container mt-5">
        <h1 class="mb-0"><?php echo xlt('Weno Download Log'); ?></h1>
        <cite class="h6 text-warning p-1 mx-1">
            <span><?php echo xlt("Note: Only prescription logs are deleted. Pharmacy status and errors are preserved."); ?></span>
        </cite>
        <form method="GET" class="mt-4 mb-2">
            <div class="form-row">
                <div class="col-md-4 mb-3">
                    <label for="startDate"><?php echo xlt("Start Date"); ?></label>
                    <input type="text" class="form-control datepicker" id="startDate" name="startDate" required value="<?php echo attr($startDate); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="endDate"><?php echo xlt("End Date"); ?></label>
                    <input type="text" class="form-control datepicker" id="endDate" name="endDate" required value="<?php echo attr($endDate); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" name="search" class="btn btn-primary"><?php echo xlt("Filter Logs"); ?></button>
                        <button type="submit" onclick="function areYouSure() {
                            top.restoreSession();
                            return confirm('<?php echo xlt("Are you sure you want to delete date range logs?"); ?>');
                        } return areYouSure();" name="delete" class="btn btn-danger"><?php echo xlt("Delete Date Range"); ?></button>
                    </div>
                </div>
            </div>
        </form>
        <?php
        $fmtStartDate = date('Y-m-d', strtotime($startDate));
        $fmtEndDate = date('Y-m-d', strtotime($endDate));

        if (isset($_GET['delete']) || isset($_GET['search'])) {
            if ($fmtStartDate > $fmtEndDate) {
                echo '<div class="alert alert-danger" role="alert">' . xlt("End date must be after start date!") . '</div>';
                exit;
            }
        }
        if (isset($_GET['delete'])) {
            if ($startDate == date('m/d/Y')) {
                echo '<div class="alert alert-danger" role="alert">' . xlt("Cannot delete today's logs!") . '</div>';
                exit;
            }
            if ($endDate == date('m/d/Y')) {
                $fmtEndDate = date('m/d/Y', strtotime('-1 day')); // only up till yesterday
            }
            // keep pharmacy and error history. Only delete prescription history as only concerned with current status.
            // TODO possibly allow cleaning of all logs separate button.
            $sql = "DELETE FROM `weno_download_log` WHERE `created_at` BETWEEN ? AND ? AND `value` = 'prescription'";
            sqlStatement($sql, [$fmtStartDate . ' 00:00:00', $fmtEndDate . ' 23:59:59']);
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">' .
                xlt("Prescription Logs deleted successfully. Showing results.") .
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            echo $message;
        }
        if (isset($_GET['search']) || isset($_GET['delete'])) {
            $sql = "SELECT `id`, `value`, `status`, `created_at` FROM `weno_download_log` WHERE `created_at` BETWEEN ? AND ? ORDER BY `created_at` DESC";
            $result = sqlStatement($sql, [$fmtStartDate . ' 00:00:00', $fmtEndDate . ' 23:59:59']);
            // Display logs in a table
            if ($result ?? false) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>' . xlt("ID") . '</th>';
                echo '<th>' . xlt("Value") . '</th>';
                echo '<th>' . xlt("Status") . '</th>';
                echo '<th>' . xlt("Created At") . '</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                while ($row = sqlFetchArray($result)) {
                    echo '<tr>';
                    echo '<td>' . text($row['id']) . '</td>';
                    echo '<td>' . text($row['value']) . '</td>';
                    echo '<td>' . text($row['status']) . '</td>';
                    echo '<td>' . text($row['created_at']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-info" role="alert">' . xlt("No logs found within the selected date range.") . '</div>';
            }
        }
        unset($_GET['delete']);
        ?>
    </div>
</body>

</html>

