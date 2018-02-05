<?php
/**
 * Patient data template.
 *
 * @package OpenEMR
 * @author  Robert Down <robertdown@live.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 */
?>
<script type="text/html" id="patient-data-template">
    <div>
        <span class="patientDataColumn">
            <span class="float-element"><a data-bind="click: viewPtFinder" href="#" class="btn btn-default btn-sm">
                <i class="fa fa-search" aria-hidden="true"></i>
            </a></span>
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
                    <i class="fa fa-envelope"></i>&nbsp;<span style="display:inline" data-bind="text: messages()"></span>
                </a>
            </span>
        <!-- /ko -->
        <!-- /ko -->
        <!-- /ko -->
    </div>
    <!-- /ko -->
</script>
