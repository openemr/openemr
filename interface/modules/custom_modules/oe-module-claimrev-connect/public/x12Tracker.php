<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

    require_once "../../../../globals.php";

    use OpenEMR\Common\Acl\AccessDeniedHelper;
    use OpenEMR\Common\Acl\AclMain;
    use OpenEMR\Core\Header;
    use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
    use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
    use OpenEMR\Modules\ClaimRevConnector\TypeCoerce;
    use OpenEMR\Modules\ClaimRevConnector\X12TrackerPage;

    $tab = "x12";

    //ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill: ClaimRev Connect - X12 Tracker", xl("ClaimRev Connect - X12 Tracker"));
}

    $startDate = ModuleInput::postString('startDate');
    $endDate = ModuleInput::postString('endDate');
    $datas = [];
    //check if form was submitted
if (ModuleInput::postExists('SubmitButton')) {
    $datas = X12TrackerPage::searchX12Tracker([
        'startDate' => $startDate,
        'endDate' => $endDate,
    ]);
}
?>

<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - X12 Tracker"); ?></title>
        <?php Header::setupHeader(); ?>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>
            <p class="mt-3">
                <?php echo xlt("This tab helps give visibility to files that are in the x12 Tracker table."); ?>
            </p>
            <form method="post" action="x12Tracker.php">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="startDate"><?php echo xlt("Created Date Start");?></label>
                                    <input type="date" class="form-control"  id="startDate" name="startDate"  value="<?php echo attr($startDate !== '' ? $startDate : date('Y-m-d')); ?>" placeholder="yyyy-mm-dd"/>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="endDate"><?php echo xlt("Created Date End");?></label>
                                    <input type="date" class="form-control"  id="endDate" name="endDate"  value="<?php echo attr($endDate !== '' ? $endDate : date('Y-m-d')); ?>" placeholder="yyyy-mm-dd"/>
                                </div>
                            </div>
                            <div class="col">

                            </div>
                            <div class="col">

                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <button type="submit" name="SubmitButton" class="btn btn-primary"><?php echo xlt("Submit"); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <?php
            if ($datas !== []) { ?>
                <table class="table table-striped mt-3">
                        <thead>
                            <tr>
                                <th scope="col"><?php echo xlt("Filename"); ?></th>
                                <th scope="col"><?php echo xlt("Status"); ?></th>
                                <th scope="col"><?php echo xlt("Messages"); ?></th>
                                <th scope="col"><?php echo xlt("Created"); ?></th>
                                <th scope="col"><?php echo xlt("Updated"); ?></th>
                                <th scope="col"><?php echo xlt("Action"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($datas as $data) {
                            $status = TypeCoerce::asString($data['status'] ?? '');
                            $rowId = TypeCoerce::asString($data['id'] ?? '');
                            $isError = str_contains($status, 'error');
                            $badgeClass = 'badge-secondary';
                            if ($status === 'success') {
                                $badgeClass = 'badge-success';
                            } elseif ($status === 'waiting') {
                                $badgeClass = 'badge-warning';
                            } elseif ($status === 'in-progress') {
                                $badgeClass = 'badge-info';
                            } elseif ($isError) {
                                $badgeClass = 'badge-danger';
                            }
                            ?>
                            <tr id="tracker-row-<?php echo attr($rowId); ?>">
                                <td><?php echo text(TypeCoerce::asString($data["x12_filename"] ?? '')); ?></td>
                                <td><span id="status-badge-<?php echo attr($rowId); ?>" class="badge <?php echo attr($badgeClass); ?>"><?php echo text($status); ?></span></td>
                                <td><?php echo text(TypeCoerce::asString($data["messages"] ?? '')); ?></td>
                                <td><?php echo text(TypeCoerce::asString($data["created_at"] ?? '')); ?></td>
                                <td><?php echo text(TypeCoerce::asString($data["updated_at"] ?? '')); ?></td>
                                <td>
                                    <?php if ($isError) { ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="retryFile(<?php echo attr_js($rowId); ?>)">
                                            <i class="fa fa-redo"></i> <?php echo xlt("Retry"); ?>
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
            <?php } ?>

            <script>
                function retryFile(id) {
                    if (!confirm(<?php echo xlj("Reset this file to waiting so it will be resent?"); ?>)) {
                        return;
                    }
                    $.ajax({
                        url: 'x12_retry.php',
                        type: 'POST',
                        data: {
                            id: id,
                            csrf_token: <?php echo js_escape(CsrfHelper::collectCsrfToken()); ?>
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                var badge = $('#status-badge-' + id);
                                badge.removeClass('badge-danger badge-secondary').addClass('badge-warning');
                                badge.text(<?php echo xlj("waiting"); ?>);
                                $('#tracker-row-' + id + ' td:last button').remove();
                            } else {
                                alert(response.message || <?php echo xlj("Failed to retry file"); ?>);
                            }
                        },
                        error: function() {
                            alert(<?php echo xlj("Error communicating with server"); ?>);
                        }
                    });
                }
            </script>
        </div>
    </body>
</html>
