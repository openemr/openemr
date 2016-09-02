<?php
/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
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
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */
?>

<script type="text/html" id="patient-data-template">
        <divs>
            <span class="patientDataColumn">
                <div class="patientInfo">
                    <span>
                            <?php echo xlt("Patient"); ?>:
                    </span>
                    <b>
                    <!-- ko if: patient -->
                        <a data-bind="click:refreshPatient,with: patient" href="#">
                            <span data-bind="text: pname()"></span>(<span data-bind="text: pubpid"></span>)
                        </a>
                    <!-- /ko -->
                    <!-- ko ifnot: patient -->
                        <span><?php echo xlt("None");?></span>
                    <!-- /ko -->

                    <!-- ko if: patient -->
                    <span class="clear" data-bind="click:clearPatient"><?php echo xlt("Clear") ?></span>
                        <!-- /ko -->
                    </b>
                </div>
                <div class="patientInfo">
                    <b>
                    <!-- ko if: patient -->
                        <span data-bind="text:patient().str_dob()"></span>
                    <!-- /ko -->
                    </b>
                </div>
            </span>
            <span class="patientDataColumn">
                &nbsp;
            </span>
            <span class="patientDataColumn patientEncountersColumn">
                <!-- ko if: patient -->                
                <!-- ko with: patient -->                
                <div>
                    <span>Selected Encounter:</span>
                    <!-- ko if:selectedEncounter() -->
                        <span data-bind="text:selectedEncounter().date()"></span>
                        (<span data-bind="text:selectedEncounter().id()"></span>)
                    <!-- /ko -->
                    <!-- ko if:!selectedEncounter() -->
                        <?php echo xlt("None") ?>
                    <!-- /ko -->                
                </div>
                <span class="patientEncounterList" >
                    <div data-bind="click: clickNewEncounter"><?php echo xlt("New Encounter");?></div>
                    <div data-bind="click: clickEncounterList"><?php echo xlt("Past Encounter List");?>
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
                                <td class="review" data-bind="click: reviewEncounterEvent">Review
                                </td>
                            </tr>
                        <!-- /ko -->
                        </tbody>
                    </table>
                </span>
                <!-- /ko -->                
                <!-- /ko -->                

            </span>
        </div>
    <!-- /ko -->
</script>
