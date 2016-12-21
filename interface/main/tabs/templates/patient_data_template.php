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
            <span style="float:left;" class="fa-stack"><a data-bind="click: viewPtFinder" href="#">
                <i class="fa fa-list fa-stack-1x" aria-hidden="true"></i>
                <strong><i style="margin: 10px 0 0 10px;" class="fa fa-search fa-stack-x" aria-hidden="true"></i></strong>
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
                    <a class="css_button_small" href="#" class="clear" data-bind="click:clearPatient" title="<?php echo xlt("Clear") ?>">
                        <i style="font-size:150%;" class="fa fa-times"></i>
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
            <a class="css_button_small" data-bind="click: clickNewEncounter" href="#" title="<?php echo xlt("New Encounter");?>">
                <i style="font-size:150%;" class="fa fa-plus"></i>
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
                <div class="patientInfo">
                    <span class="patientDataColumn patientEncountersColumn">
                        <span class="patientEncounterList" >
                            <div data-bind="click: clickEncounterList">
                            <!-- ko if: encounterArray().length == 1 -->
                                <?php echo xlt("View Past Encounter");?>
                            <!-- /ko -->
                            <!-- ko if: encounterArray().length > 1 -->
                                <?php echo xlt("View Past Encounters");?>
                            <!-- /ko -->
                                (<span data-bind="text:encounterArray().length"></span>)
                            </div>
                            <table class="encounters">
                                <tbody>
                                <!-- ko  foreach:encounterArray -->
                                    <tr >
                                        <td data-bind="click: chooseEncounterEvent">
                                            <span data-bind="text:date"></span>
                                            <span data-bind="text:category"></span>
                                        </td>
                                        <td class="review" data-bind="click: reviewEncounterEvent">
                                            <?php echo xlt("Review"); ?>
                                        </td>
                                    </tr>
                                <!-- /ko -->
                                </tbody>
                            </table>
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
                <a style="font-size:150%;" class="css_button_small" href="#" data-bind="click: viewMessages" title="<?php echo xlt("View Messages");?>">
                    <i class="fa fa-envelope"></i>&nbsp;<span style="display:inline" data-bind="text: messages()"></span>
                </a>
            </span>
        <!-- /ko -->
        <!-- /ko -->
        <!-- /ko -->
    </div>
    <!-- /ko -->
</script>
