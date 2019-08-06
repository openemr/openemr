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
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>
<?php

   $search_any_type = $GLOBALS['search_any_patient'];
   //$search_any_type = 'comprehensive';
   //$search_any_type = 'dual';
   
if ($search_any_type == 'dual') {
    $any_search_class = "any-search-legacy";
    $search_globals_class = "btn-globals-legacy";
} elseif ($search_any_type == 'comprehensive') {
    $any_search_class = "any-search-modern";
    $search_globals_class = "btn-globals-modern";
}

?>
<script type="text/html" id="patient-data-template">
    <div>
        <span class="patientDataColumn">
            <div class="patientInfo float-element">
              <!-- ko if: patient -->
                  <div data-bind="with: patient" class="patientPicture">
                      <img data-bind="attr: {src: patient_picture}"
                           class="img-thumbnail"
                           onError="this.src = '<?php echo $GLOBALS['images_static_relative']; ?>/patient-picture-default.png'" />
                  </div>
              <!-- /ko -->
            </div>
            <div class="patientInfo">
                <?php echo xlt("Patient"); ?>:
                <!-- ko if: patient -->
                    <a class="ptName" data-bind="click:refreshPatient,with: patient" href="#">
                        <span data-bind="text: pname()"></span>
                        (<span data-bind="text: pubpid"></span>)
                    </a>
                <!-- /ko -->
                <!-- ko ifnot: patient -->
                    <?php echo xlt("None{{Patient}}");?>
                <!-- /ko -->
                <!-- ko if: patient -->
                    <a class="btn btn-xs btn-link" href="#" data-bind="click:clearPatient" title="<?php echo xla("Clear") ?>">
                        <i class="fa fa-times"></i>
                    </a>
                <!-- /ko -->
            </div>
            <div class="patientInfo">
            <!-- ko if: patient -->
                <span data-bind="text:patient().str_dob()"></span>
                <!-- /ko -->
                <div class="oe-expandable-search" id="div-search-globals">
                <?php //adapted from https://codepen.io/brandonkennedy/pen/yGjsi ?>
                    <form name="frm_search_globals">
                        <input type="text" id="anySearchBox"  class="<?php echo $any_search_class ?>" name="anySearchBox"  placeholder="<?php echo xla("Search by any demographics") ?>" autocomplete="off">
                        <button type="button" id="search_globals" class="btn btn-default btn-search btn-search1 <?php echo $search_globals_class ?>" title='<?php echo xla("Search for patient by entering whole or part of any demographics field information"); ?>' data-bind="event: {mousedown: viewPtFinder.bind( $data, '<?php echo xla("The search field cannot be empty. Please enter a search term") ?>', '<?php echo attr($search_any_type); ?>')}">
                        </button>
                    </form>
                </div>
            </div>
        </span>
        <span class="patientDataColumn">
        <!-- ko if: patient -->
        <!-- ko with: patient -->
            <a class="btn btn-xs btn-link" data-bind="click: clickEncounterList" href="#" title="<?php echo xla("Visit History");?>">
                <i class="fa fa-refresh"></i>
            </a>
            <a class="btn btn-xs btn-link" data-bind="click: clickNewEncounter" href="#" title="<?php echo xla("New Encounter");?>">
                <i class="fa fa-plus"></i>
            </a>
            <div class="patientCurrentEncounter">
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
            <!-- ko if: encounterArray().length > 0 -->
            <br>
            <div class="btn-group dropdown">
                <button class="btn btn-default btn-sm dropdown-toggle"
                        type="button" id="pastEncounters"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="true">
                    <?php echo xlt("View Past Encounters"); ?>&nbsp;
                    (<span data-bind="text:encounterArray().length"></span>)
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="pastEncounters">
                    <!-- ko foreach:encounterArray -->
                    <li style="display: inline-flex;">
                        <a href="#" data-bind="click:chooseEncounterEvent">
                            <span data-bind="text:date"></span>
                            <span data-bind="text:category"></span>
                        </a>
                        <a href="#" data-bind="click:reviewEncounterEvent">
                            <i class="fa fa-rotate-left"></i>&nbsp;<?php echo xlt("Review");?>
                        </a>
                    </li>
                    <!-- /ko -->
                </ul>
            </div>
            <!-- /ko -->
        <!-- /ko -->
        <!-- /ko -->
        </span>
        <!-- ko if: user -->
        <!-- ko with: user -->
        <!-- ko if:messages() -->
            <span class="messagesColumn">
                <a class="btn btn-default" href="#" data-bind="click: viewMessages" title="<?php echo xla("View Messages");?>">
                    <i class="fa fa-envelope"></i>&nbsp;<span class="label label-danger" style="display:inline" data-bind="text: messages()"></span>
                </a>
            </span>
        <!-- /ko -->
        <!-- ko if: portal() -->
            <div class="messagesColumn btn-group dropdown">
                <button class="btn btn-default btn-md dropdown-toggle"
                        type="button" id="portalMsgAlerts"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="true">
                    <?php echo xlt("Portal Alerts"); ?>&nbsp;
                    <span class="label label-danger" data-bind="text: portalAlerts()"></span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="portalMsgAlerts">
                    <li style="display: inline-flex;">
                        <a href="#" data-bind="click: viewPortalMail">
                            <i class="fa fa-envelope-o"></i>&nbsp;<?php echo xlt("Portal Mail"); ?>&nbsp;
                            <span class="label label-success" style="display:inline" data-bind="text: portalMail()"></span>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li style="display: inline-flex;">
                        <a href="#" data-bind="click: viewPortalAudits">
                            <i class="fa fa-align-justify"></i>&nbsp;<?php echo xlt("Portal Audits"); ?>&nbsp;
                            <span class="label label-success" style="display:inline"
                                  data-bind="text: portalAudits()"></span>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li style="display: inline-flex;">
                        <a href="#" data-bind="click: viewPortalChats">
                            <i class="fa fa-envelope"></i>&nbsp;<?php echo xlt("Portal Chats"); ?>&nbsp;
                            <span class="label label-success" style="display:inline" data-bind="text: portalChats()"></span>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li style="display: inline-flex;">
                        <a href="#" data-bind="click: viewPortalPayments">
                            <i class="fa fa-envelope"></i>&nbsp;<?php echo xlt("Portal Payments"); ?>&nbsp;
                            <span class="label label-success" style="display:inline" data-bind="text: portalPayments()"></span>
                        </a>
                    </li>
                </ul>
            </div>
        <!-- /ko -->
        <!-- /ko -->
        <!-- /ko -->
    </div>
    <!-- /ko -->
</script>
