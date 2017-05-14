<?php
/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Robert Down <robertdown@live.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */
?>
<script type="text/html" id="patient-data-template">
    <div>
        <span class="patientDataColumn">
            <span style="float:left;"><a data-bind="click: viewPtFinder" href="#" class="btn btn-default btn-sm">
                <i class="fa fa-search" aria-hidden="true"></i>
            </a></span>
            <div class="patientInfo">
                <?php echo xlt("Patient"); ?>:
                <!-- ko if: patient -->
                    <a class="ptName" data-bind="click:refreshPatient,with: patient" href="#">
                        <span data-bind="text: pname()"></span>
                        (<span data-bind="text: pubpid"></span>)
                    </a>
                <!-- /ko -->
                <!-- ko ifnot: patient -->
                    <?php echo xlt("None");?>
                <!-- /ko -->
                <!-- ko if: patient -->
                    <a class="btn btn-xs btn-link" href="#" class="clear" data-bind="click:clearPatient" title="<?php echo xlt("Clear") ?>">
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
            <a class="btn btn-xs btn-link" data-bind="click: clickNewEncounter" href="#" title="<?php echo xlt("New Encounter");?>">
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
                    <?php echo xlt("None") ?>
                <!-- /ko -->
            </div>
            <!-- ko if: encounterArray().length > 0 -->
            <div class="dropdown dropdown-toggle">
                <button class="btn btn-default btn-sm dropdown-toggle"
                        type="button" id="pastEncounters"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="true">
                    <?php echo xlt("View Past Encounters"); ?>&nbsp;
                    (<span data-bind="text:encounterArray().length"></span>)
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu"
                    aria-labelledby="pastEncounters">
                    <!-- ko foreach:encounterArray -->
                    <li>
                        <a href="#" data-bind="click:chooseEncounterEvent" style="display: inline;">
                            <span data-bind="text:date"></span>
                            <span data-bind="text:category"></span>
                        </a>
                        <a href="#" class="btn btn-xs btn-link"
                           data-bind="click:reviewEncounterEvent" style="display: inline;">
                            <i class="fa fa-folder-o"></i>&nbsp;<?php echo xlt("Review");?>
                        </a>
                    </li>
                    <!-- /ko -->
                </ul>
            </div>
                <div class="patientInfo">
                    <span class="patientDataColumn patientEncountersColumn">
                        <span class="patientEncounterList" >
                        </span>
                    </span>
                </div>
            <!-- /ko -->
        <!-- /ko -->
        <!-- /ko -->
        </span>
        <!-- ko if: user -->
        <!-- ko with: user -->
        <!-- ko if:messages() -->
            <span class="messagesColumn">
                <a class="btn btn-default btn-sm" href="#" data-bind="click: viewMessages" title="<?php echo xlt("View Messages");?>">
                    <i class="fa fa-envelope"></i>&nbsp;<span style="display:inline" data-bind="text: messages()"></span>
                </a>
            </span>
        <!-- /ko -->
        <!-- /ko -->
        <!-- /ko -->
    </div>
    <!-- /ko -->
</script>
