<?php
/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
 * Copyright (C) 2017 Amiel Elboim <amielel@matrix.co.il>
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
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */
?>

<script type="text/html" id="therapy-group-template">
    <div>
        <span class="patientDataColumn">
            <span style="float:left;" class="fa-stack"><a data-bind="click: viewTgFinder" href="#">
                <i class="fa fa-list fa-stack-1x" aria-hidden="true"></i>
                <strong><i style="margin: 10px 0 0 10px;" class="fa fa-search fa-stack-x" aria-hidden="true"></i></strong>
            </a></span>
            <div class="patientInfo">
                <?php echo xlt("Group"); ?>:
                <!-- ko if: therapy_group -->
                    <a class="ptName" data-bind="click:refreshGroup,with: therapy_group" href="#">
                        <span data-bind="text: gname()"></span>
                        (<span data-bind="text: gid"></span>)
                    </a>
                <!-- /ko -->
                <!-- ko ifnot: therapy_group -->
                    <?php echo xlt("None");?>
                <!-- /ko -->
                <!-- ko if: therapy_group -->
                    <a class="css_button_small" href="#" class="clear" data-bind="click:clearTherapyGroup" title="<?php echo xlt("Clear") ?>">
                        <i style="font-size:150%;" class="fa fa-times"></i>
                    </a>
                <!-- /ko -->
            </div>
        </span>
        <span class="patientDataColumn">
        <!-- ko if: therapy_group -->
        <!-- ko with: therapy_group -->
            <a class="css_button_small" data-bind="click: clickNewGroupEncounter" href="#" title="<?php echo xlt("New Encounter");?>">
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
