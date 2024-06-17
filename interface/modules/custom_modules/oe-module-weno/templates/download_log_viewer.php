<?php

require_once(dirname(__DIR__, 4) . "/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;

if (!AclMain::aclCheckCore('admin', 'super')) {
    // renders in MM iFrame
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Must be an Admin")]);
    exit;
}

$logService = new WenoLogService();
$pres_log = $logService->getLastPrescriptionLogStatus();
$pharm_log = $logService->getLastPharmacyDownloadStatus();

$startDate = $_GET['startDate'] ?? date('m/d/Y'); // just default to today
$endDate = $_GET['endDate'] ?? date('m/d/Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xlt('Weno Downloads'); ?></title>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <style>
      .hide {
        display: none;
      }
    </style>
    <script>
        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });
        });
    </script>
    <script>
        function downloadPharmacies(daily){
            if (!window.confirm(
                xl("This import takes anywhere from a couple seconds to less than a minute depending on your connection speed but will normally take 25-30 seconds for a full import.") +
                "\n\n" + xl("Do you want to continue?")
            )) {
                return false;
            }
            let notchPhar = daily === 'Y' ? $('#notch-pharm') : $('#notch-pharm-full');
            notchPhar.removeClass("hide");
            $('#btn-pharm').attr("disabled", true);
            $('#btn-pharm-full').attr("disabled", true);
            $('#presc-btn').attr("disabled", true);
            $.ajax({
                url: "<?php echo $GLOBALS['webroot']; ?>" + "/interface/modules/custom_modules/oe-module-weno/scripts/file_download.php?daily=" + encodeURIComponent(daily),
                type: "GET",
                success: function (data) {
                    if (data.includes('Error') || data.includes('failed')) {
                        let alertDiv = document.getElementById('alertDiv');
                        let errorMsgSpan = document.getElementById('error-msg');
                        errorMsgSpan.textContent = jsText(data);
                        $("#alertDiv").removeClass("d-none");
                        setTimeout(function() {
                            window.location.replace(window.location.href);
                        }, 10000);
                    }
                    notchPhar.addClass("hide");
                    $('#presc-btn').attr("disabled", false);
                    if (!data.includes('Error') && !data.includes('failed')) {
                        window.location.replace(window.location.href);
                    }
                },
                // Error handling
                error: function (error) {
                    notchPhar.addClass("hide");
                    $('#presc-btn').attr("disabled", false);
                    console.log(`Error ${error}`);
                    window.location.replace(window.location.href);
                }
            });
        }
        function downloadPresLog(){
            $('#notch-presc').removeClass("hide");
            $('#btn-pharm').attr("disabled", true);
            $('#btn-pharm-full').attr("disabled", true);
            $('#presc-btn').attr("disabled", true);
            $.ajax({
                url: "<?php echo $GLOBALS['webroot']; ?>" + "/interface/modules/custom_modules/oe-module-weno/templates/synch.php",
                type: "GET",
                data: {key:'downloadLog'},
                success: function (data) {
                    if (data.includes('Error') || data.includes('failed')) {
                        let alertDiv = document.getElementById('alertDiv');
                        let errorMsgSpan = document.getElementById('error-msg');
                        errorMsgSpan.textContent = jsText(data);
                        $("#alertDiv").removeClass("d-none");
                        setTimeout(function() {
                            window.location.replace(window.location.href);
                        }, 10000);
                    }
                    $('#notch-presc').addClass("hide");
                    $('#presc-btn').attr("disabled", false);
                    if (!data.includes('Error') && !data.includes('failed')) {
                        window.location.replace(window.location.href);
                    }
                },
                // Error handling
                error: function (error) {
                    $('#notch-presc').addClass("hide");
                    $('#presc-btn').attr("disabled", false);
                    console.log(`Error ${error}`);
                    window.location.replace(window.location.href);
                }
            });
        }
    </script>
</head>
<body>
    <div class="container mt-2">
        <h1><?php print xlt("Weno Downloads Management") ?></h1>
    </div>
    <div class="container mt-3" id="pharmacy">
        <?php
        $backGroundTask = sqlStatement("SELECT `name`, `title`, `next_run` FROM `background_services` WHERE `name` LIKE ? ORDER BY `next_run` DESC", ['%weno%']);
        // first show some download info. Why not!
        if ($backGroundTask ?? false) {
            echo '<h6 class="mb-2">';
            while ($task = sqlFetchArray($backGroundTask)) {
                $title = $task['title'];
                $nextRun = $task['next_run'];
                echo '<span class="mr-5 text-success">' . text($title) . '  ' . xlt("next run") . ': <span class="text-dark">' . text($nextRun) . '</span></span>';
            }
            echo '</h6>';
        }
        ?>
        <h3><?php print xlt("Weno Downloads") ?></h3>
        <div>
            <cite class="text-info text-center p-1 mx-1"><?php echo xlt("Use this section to download Weno Pharmacy Directory and Weno Prescription Log"); ?></cite>
        </div>
        <div id="alertDiv" class="alert alert-danger d-none">
            <button type="button" class="close" onclick="window.location.replace(window.location.href);">&times;</button>
            <strong><?php echo xlt("Error!"); ?></strong>
            <span id="error-msg"></span>
        </div>
        <table class="table table-sm table-borderless mt-3">
            <thead>
            <tr>
                <th scope="col"><?php echo xlt("Description"); ?></th>
                <th scope="col"><?php echo xlt("Last Update"); ?></th>
                <th scope="col"><?php echo xlt("Status"); ?></th>
                <th scope="col"><?php echo xlt("Action"); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo xlt("Weno Pharmacy Directory"); ?></td>
                <td><?php echo text($pharm_log['created_at'] ?? 'Never'); ?></td>
                <td><?php echo xlt($pharm_log['status'] ?? 'Needs download'); ?></td>
                <td>
                    <button type="button" id="btn-pharm" onclick="downloadPharmacies('Y');" class="btn btn-primary btn-sm">
                        <?php echo xlt("Update Directory") ?>
                        <span class="hide" id="notch-pharm">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </span>
                    </button>
                    <button type="button" id="btn-pharm-full" onclick="downloadPharmacies('N');" class="btn btn-primary btn-sm">
                        <?php echo xlt("Full Directory") ?>
                        <span class="hide" id="notch-pharm-full">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </span>
                    </button>
                </td>
            </tr>
            <tr>
                <td><?php echo xlt("Prescription log"); ?></td>
                <td><?php echo text($pres_log['created_at'] ?? ''); ?></td>
                <td><?php echo xlt($pres_log['status'] ?? ''); ?></td>
                <td>
                    <button type="button" id="presc-btn" onclick="downloadPresLog();" class="btn btn-primary btn-sm">
                        <?php echo xlt("Download") ?>
                        <span class="hide" id="notch-presc"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="container mt-2">
        <h3 class="mb-0"><?php echo xlt('Weno Download Log'); ?></h3>
        <cite class="h6 text-info p-1 mx-1">
            <span><?php echo xlt("Note: Only prescription logs are deleted. Pharmacy status and errors are preserved."); ?></span>
        </cite>
        <form method="GET" class="mb-2">
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
            $sql = "SELECT `id`, `value`, `status`, `created_at` FROM `weno_download_log` WHERE `created_at` BETWEEN ? AND ? ORDER BY `created_at` DESC, `id` DESC";
            $result = sqlStatement($sql, [$fmtStartDate . ' 00:00:00', $fmtEndDate . ' 23:59:59']);
            // Display logs in a table
            if ($result ?? false) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-hover table-striped table-sm table-borderless">';
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

