<?php

/**
 * Patient data template.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Robert Down <robertdown@live.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2023 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<?php

$search_any_type = $GLOBALS['search_any_patient'];

//Modes for search box :comprehensive, dual, fixed and the default is none
switch ($search_any_type) {
    case 'dual':
        $any_search_class = "any-search-legacy";
        $search_globals_class = "btn-globals-legacy";
        break;
    case 'comprehensive':
        $any_search_class = "any-search-modern";
        $search_globals_class = "btn-globals-modern";
        break;
    case 'fixed':
        $any_search_class = "any-search-fixed";
        $search_globals_class = "btn-globals-fixed";
        break;
    default:
        $any_search_class = "any-search-none";
        $search_globals_class = "btn-globals-none";
}

?>
<script type="text/html" id="patient-data-template">
    <div class="d-lg-inline-flex w-100">
        <div class="flex-fill">
            <div class="float-left mx-2">
                <!-- ko if: patient -->
                <div data-bind="with: patient" class="patientPicture">
                    <img data-bind="attr: {src: patient_picture}"
                        class="img-thumbnail"
                        width="75"
                        height="75"
                        onError="this.src = '<?php echo $GLOBALS['images_static_relative']; ?>/patient-picture-default.png'" />
                </div>
                <!-- /ko -->
            </div>
            <div class="form-group">
                <!-- ko if: patient -->
                <?php
                $classes = "";
                $closeAnchorClasses = '';
                switch ($GLOBALS['patient_name_display']) :
                    case 'btn':
                        $classes = "btn btn-sm btn-secondary";
                        $wrapperElement = 'div';
                        $wrapperElementClass = 'btn-group btn-group-sm mb-2';
                        $closeElement = '';
                        $closeElementClass = '';
                        $closeIconClass = 'text-muted';
                        $pubpidElement = 'span';
                        break;
                    case 'text-large':
                        $closeAnchorClasses = 'text-muted';
                        $wrapperElement = 'h3';
                        $wrapperElementClass = 'd-inline';
                        $closeElement = 'small';
                        $closeElementClass = '';
                        $closeIconClass = 'text-muted fa-xs';
                        $pubpidElement = 'small';
                        break;
                    default:
                        $closeAnchorClasses = 'text-muted';
                        $wrapperElement = 'div';
                        $wrapperElementClass = 'd-inline';
                        $pubpidElement = 'span';
                        $closeElement = 'span';
                        $closeElementClass = '';
                        $closeIconClass = 'text-muted';
                        break;
                endswitch;
                echo "<$wrapperElement class=\"$wrapperElementClass\">";
                ?>
                    <a class="ptName <?php echo $classes ?? ''; ?> " data-bind="click:refreshPatient,with: patient" href="#" title="<?php echo xla("To Dashboard") ?>">
                        <span data-bind="text: pname()"></span>
                        <<?php echo $pubpidElement;?> class="text-muted">(<span data-bind="text: pubpid"></span>)</<?php echo $pubpidElement;?>>
                    </a>
                    <?php echo ($closeElement !== '') ? "<$closeElement class=\"$closeElementClass\">" : ''; ?>
                    <a href="#" class="pt-1<?php echo (($classes ?? '') !== "") ? " " . $classes : "";?> <?php echo ($closeAnchorClasses !== "") ? " " . $closeAnchorClasses : ""; ?>" data-bind="click:clearPatient" title="<?php echo xla("Close Patient Chart") ?>">
                        <i class="fa fa-times<?php echo ($closeIconClass !== "") ? " " . $closeIconClass : ""; ?>"></i>
                    </a>
                    <?php echo ($closeElement !== '') ? "</$closeElement>" : ''; ?>
                <?php echo "</$wrapperElement>"; ?>

                <div class="mt-2">
                    <span data-bind="text:patient().str_dob()"></span>
                </div>
                <!-- /ko -->
            </div>
        </div>

        <div class="flex-fill ml-2">
            <!-- ko if: patient -->
            <!-- ko with: patient -->
            <div class="btn-group btn-group-sm">
                <a class="btn btn-sm btn-secondary" data-bind="click: clickEncounterList" href="#"
                    title="<?php echo xla("Visit History"); ?>">
                    <i class="fas fa-history"></i>
                </a>
                <div class="btn-group dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle"
                    type="button" id="pastEncounters"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="true">
                    <?php echo xlt("Select Encounter"); ?>&nbsp;
                    (<span data-bind="text:encounterArray().length"></span>)<span class="caret"></span></button>
                <ul class="dropdown-menu" aria-labelledby="pastEncounters">
                    <!-- ko foreach:encounterArray -->
                    <li class="d-inline-flex">
                        <a class="dropdown-item" href="#" data-bind="click:chooseEncounterEvent">
                            <span data-bind="text:date"></span>
                            <span data-bind="text:category"></span>
                        </a>
                        <a href="#" class="dropdown-item" data-bind="click:reviewEncounterEvent">
                            <i class="fa fa-rotate-left"></i>&nbsp;<?php echo xlt("Review"); ?>
                        </a>
                    </li>
                    <!-- /ko -->
                </ul>
            </div>
                <a class="btn btn-sm btn-secondary" data-bind="click: clickNewEncounter" href="#"
                    title="<?php echo xla("New Encounter"); ?>">
                    <i class="fa fa-plus"></i>
                </a>
            </div>

            <!-- ko if: encounterArray().length > 0 -->
            <div class="patientCurrentEncounter mt-2 d-block">
                    <span><?php echo xlt("Open Encounter"); ?>:</span>
                    <!-- ko if:selectedEncounter() -->
                    <a data-bind="click: refreshEncounter" href="#">
                        <span data-bind="text:selectedEncounter().date()"></span>
                        (<span data-bind="text:selectedEncounter().id()"></span>)
                    </a>
                    <!-- /ko -->
                    <!-- ko if:!selectedEncounter() -->
                    <?php echo xlt("None{{Encounter}}") ?>
                    <!-- /ko -->
                </div>

            <!-- /ko --><!-- encounter -->
            <!-- /ko --><!-- with patient -->
            <!-- /ko --><!-- patient -->
        </div>

        <div class="flex-column mx-2">
            <!-- ko if: user -->
            <!-- ko with: user -->
            <!-- ko if:messages() -->
            <span class="mr-auto">
                <a class="btn btn-secondary btn-sm" href="#" data-bind="click: viewMessages"
                    title="<?php echo xla("View Messages"); ?>">
                    <i class="fa fa-envelope"></i>&nbsp;<span class="badge badge-primary" style="display:inline" data-bind="text: messages()"></span>
                </a>
            </span>
            <!-- /ko --><!-- messages -->
            <!-- ko if: portal() -->
            <nav class="btn-group dropdown mr-auto">
                <button class="btn btn-secondary btn-sm dropdown-toggle"
                    type="button" id="portalMsgAlerts"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="true">
                    <?php echo xlt("Portal"); ?>&nbsp;
                    <span class="badge badge-danger" data-bind="text: portalAlerts()"></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="portalMsgAlerts">
                    <li>
                        <a class="dropdown-item" href="#" data-bind="click: viewPortalMail">
                            <i class="fa fa-envelope"></i>&nbsp;<?php echo xlt("Portal Mail"); ?>&nbsp;
                            <span class="badge badge-success" style="display:inline" data-bind="text: portalMail()"></span>
                        </a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" data-bind="click: viewPortalAudits">
                            <i class="fa fa-align-justify"></i>&nbsp;<?php echo xlt("Portal Audits"); ?>&nbsp;
                            <span class="badge badge-success" style="display:inline" data-bind="text: portalAudits()"></span>
                        </a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" data-bind="click: viewPortalPayments">
                            <i class="fa fa-credit-card"></i>&nbsp;<?php echo xlt("Portal Payments"); ?>&nbsp;<span class="badge badge-success" style="display:inline" data-bind="text: portalPayments()"></span>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /ko --><!-- portal alert -->
            <!-- ko if: servicesOther() -->
            <nav class="btn-group dropdown mr-auto">
                <button class="btn btn-secondary btn-sm dropdown-toggle"
                    type="button" id="servicesMsgAlerts"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="true">
                    <?php echo xlt("Services"); ?>&nbsp;
                    <span class="badge badge-danger" data-bind="text: serviceAlerts()"></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="servicesMsgAlerts">
                    <li>
                        <a class="dropdown-item" href="#" data-bind="click: viewFaxCount">
                            <i class="fa fa-solid fa-fax"></i>&nbsp;<?php echo xlt("Pending Faxes"); ?>&nbsp;
                            <span class="badge badge-success" style="display:inline" data-bind="text: faxAlerts()"></span>
                        </a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" data-bind="click: viewSmsCount">
                            <i class="fa fa-sms"></i>&nbsp;<?php echo xlt("Pending SMS"); ?>&nbsp;
                            <span class="badge badge-success" style="display:inline" data-bind="text: smsAlerts()"></span>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /ko --><!-- servicesOther alert -->
            <!-- /ko --><!-- with user -->
            <!-- /ko --><!-- user -->
        </div>
    </div>
</script>
