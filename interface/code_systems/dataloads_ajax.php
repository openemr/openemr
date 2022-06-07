<?php

/**
 * This file implements the main jquery interface for loading external
 * database files into openEMR
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
 * @author    Rohit Kumar <pandit.rohit@netsity.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2011 Phyaura, LLC <info@phyaura.com>
 * @copyright Copyright (c) 2012 Patient Healthcare Analytics, Inc.
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// Ensure script doesn't time out
set_time_limit(0);

// Control access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("External Data Loads")]);
    exit;
}

$activeAccordionSection = isset($_GET['aas']) ? $_GET['aas'] : '0';

?>
<html>
<head>
<title><?php echo xlt('External Data Loads'); ?></title>
<?php Header::setupHeader(); ?>

<style>
    .hdr {
        font-size: 1.1rem;
        font-weight: bold;
    }
    .overview {
        font-size: 1.1rem;
        font-weight: normal;
        width: 700px;
        color: var(--primary);
    }
    .atr {
        font-size: 0.8rem;
        font-weight: normal;
        clear: both;
        width: 300px;
    }
    .left_wrpr {
        padding: 20px;
        background-color: var(--gray200);
    }
    .wrpr {
        padding: 20px;
        background-color: var(--gray200);
    }
    <!-- Keeping empty classes for jquery hooks -->
    .inst_dets {
    }
    .stg_dets {
    }
    .stg {
        font-size: 0.8rem;
        font-weight: normal;
        font-style: italic;
        margin: 10px;
    }
    .status {
        font-size: 0.8rem;
        font-weight: normal;
        width: 350px;
    }

    span.msg {
        cursor: pointer;
        display: inline-block;
        margin-left: 10px;
        width: 16px;
        height: 16px;
        background-color: #89A4CC;
        line-height: 16px;
        color: var(--white);
        font-size: 0.8125rem;
        font-weight: bold;
        border-radius: 8px;
        text-align: center;
        position: relative;
    }
    span.msg:hover {
        background-color: #3D6199;
    }
</style>
</head>
<body class="body_top">
<h4><?php echo xlt("External Database Import Utility"); ?></h4>

<div class="accordion" id="externalDatabaseAccordion">
    <!-- Overview collapse -->
    <div class="card">
        <div class="card-header" id="overview">
        <h2 class="mb-0">
            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOverview" aria-expanded="true" aria-controls="collapseOne">
                <?php echo xlt("Overview"); ?>
            </button>
        </h2>
        </div>

        <div id="collapseOverview" class="collapse show" aria-labelledby="overview" data-parent="#externalDatabaseAccordion">
        <div class="card-body stg">
            <div class="overview"><?php echo xlt("This page allows you to review each of the supported external dataloads that you can install and upgrade. Each section below can be expanded by clicking on the section header to review the status of the particular database of interest."); ?>
                <div class="text-danger"><?php echo xlt("NOTE: Importing external data can take more than an hour depending on your hardware configuration. For example, one of the RxNorm data tables contain in excess of 6 million rows."); ?></div>
            </div>
        </div>
        </div>
    </div>

    <!-- List Database collapse -->
    <?php
    //
    // setup the divs for each supported external dataload
    //
    // placemaker for when support DSMIV
    //$db_list = array("DSMIV", "ICD9", "ICD10", "RXNORM", "SNOMED");
    $db_list = array("ICD9", "ICD10", "RXNORM", "SNOMED","CQM_VALUESET");
    foreach ($db_list as $db) {
        ?>
        <div class="card">
            <div class="card-header" id="<?php echo attr($db); ?>">
                <h2 class="mb-0">
                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse<?php echo attr($db); ?>" aria-expanded="false" aria-controls="collapseTwo">
                        <?php echo attr($db); ?>
                    </button>
                </h2>
            </div>
            <div id="collapse<?php echo attr($db); ?>" class="collapse" aria-labelledby="<?php echo attr($db); ?>" data-parent="#externalDatabaseAccordion">
                <div class="card-body">
                    <div class="status" id="<?php echo attr($db); ?>_status">
                    </div>
                    <div class="row px-5">
                    <div class="left_wrpr col-md-2 col-sm-4">
                        <div class="inst_dets">
                            <div class="card-text"><?php echo xlt("Installed Release"); ?>
                            </div>
                            <hr>
                            <div id="<?php echo attr($db); ?>_install_details">
                                <div id='<?php echo attr($db); ?>_inst_loading' class='m-2'>
                                    <img src='../pic/ajax-loader.gif'/>
                                </div>
                            </div>
                        </div>
                        <div >
                        </div>
                    </div>
                    <div class="wrpr col-md-auto col-sm-7 offset-sm-1">
                        <div class="stg_dets">
                            <div class="card-text" id="<?php echo attr($db); ?>_stg_hdr"><?php echo xlt("Staged Releases"); ?>
                            </div>
                            <hr>
                            <div id="<?php echo attr($db); ?>_stage_details">
                            </div>
                            <div id='<?php echo attr($db); ?>_stg_loading' class='m-2'>
                                <img src='../pic/ajax-loader.gif'/>
                            </div>
                        </div>
                    </div>
    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<script>
    const dbList = ['ICD9', 'ICD10', 'RXNORM', 'SNOMED', 'CQM_VALUESET'];

    dbList.forEach((dbName) => {
        $(`#collapse${dbName}`).on('show.bs.collapse', function () {
            const parm = `db=${dbName}`;
            const inst_dets_id = `#${dbName}_install_details`;
            const stg_dets_id = `#${dbName}_stage_details`;
            const inst_load_id = `#${dbName}_inst_loading`;
            const stg_load_id = `#${dbName}_stg_loading`;

            top.restoreSession()
            $(inst_load_id).show();
            $(stg_load_id).show();

            $.ajax({
                url: 'list_installed.php',
                data: parm,
                cache: false,
                success: function(data) {
                    $(inst_dets_id).html(data);
                }
            });

            $.ajax({
                url: 'list_staged.php',
                data: parm,
                cache: false,
                success: function(data) {
                    $(stg_load_id).hide();
                    $(stg_dets_id).html(data);
                    $(`#${dbName}_instrmsg`).click( function() {
                        dlgopen(`${dbName.toLowerCase()}_howto.php`, '', 800, 250, false, `${dbName} <?php echo xla("Installation Details"); ?>`, {
                            buttons: [{
                                text: '<?php echo xlt("Close"); ?>',
                                close: true,
                                style: 'btn btn-sm btn-danger'}],
                            allowDrag: false,
                        });
                    });
                    // Initial tooltip
                    $(`#${dbName}_unsupportedmsg`).attr({"title": "<?php echo xla("OpenEMR does not recognize the incoming file in the contrib directory. This is most likely because you need to configure the release in the supported_external_dataloads table in the MySQL database."); ?>", "data-toggle":"tooltip", "data-placement":"bottom"}).tooltip();

                    $(`#${dbName}_dirmsg`).attr({"title": "<?php echo xla("Please create the following directory before proceeding"); ?>: contrib/" + (dbName).toLowerCase(), "data-toggle":"tooltip", "data-placement":"bottom"}).tooltip();

                    $(`#${dbName}_msg`).attr({"title": "<?php echo xla("Please place your install files in following directory"); ?>: contrib/" + (dbName).toLowerCase(), "data-toggle":"tooltip", "data-placement":"bottom"}).tooltip();

                    // Upgrade Database button event
                    $(`#${dbName}_install_button`).click(function(e) {
                        $(this).prop("disabled", "disabled");
                        const stg_load_id = `#${dbName}_stg_loading`;
                        $(stg_load_id).show();
                        let thisInterval;
                        const parm = `db=${dbName}&newInstall=` + (($(this).val() === 'INSTALL') ? 1 : 0) + '&file_checksum=' + $(this).attr('file_checksum') + '&file_revision_date=' + $(this).attr('file_revision_date') + '&version=' + $(this).attr('version') + '&rf=' + $(this).attr('rf');
                        const stg_dets_id = `#${dbName}_stage_details`;

                        $.ajax({
                            url: 'standard_tables_manage.php',
                            data: parm,
                            cache: false,
                            success: function(data) {
                                const stg_load_id = `#${dbName}_stg_loading`;
                                $(stg_load_id).hide();

                                dlgopen('', '', 800, 250, '', `<div class='text-success'><?php echo xla("Successfully upgraded"); ?> ${dbName}</div>`, {
                                    buttons: [{
                                        text: '<?php echo xlt("Close"); ?>',
                                        close: true,
                                        style: 'default btn-sm btn-secondary'}],
                                    type: 'Alert',
                                    html: data,
                                });
                                $("#response_dialog").html(data);
                            }
                        });
                    });
                    return false;
                }
            });
        });
    });
</script>
</body>
</html>
