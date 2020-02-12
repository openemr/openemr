<?php
/**
 * tabs_template.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
?>

<script type="text/html" id="tabs-controls">
    <div class="tabControls" data-bind="with: tabs">
        <div class="tabNotchosen" style="width:2%">
            <i class="fa fa-caret-up menu_arrow" id="patient_caret" title="<?php echo xla('Toggle the Patient Panel'); ?>" aria-hidden="true"></i>
        </div>
        <!-- ko  foreach: tabsList -->
            <div class="tabSpan bgcolor2" data-bind="click: tabClicked, css: {tabNotchosen: !visible()}">
                <span class="tabTitle" data-bind="text: title, click: tabClicked, css: {tabHidden: !visible()}"></span>
                <span class="fa fa-fw fa-refresh" data-bind="click: tabRefresh"></span>
                <!--ko if:!locked() -->
                    <span class="fa fa-fw fa-unlock"  data-bind="click: tabLockToggle"></span>
                <!-- /ko -->
                <!--ko if:locked() -->
                    <span class="fa fa-fw fa-lock"  data-bind="click: tabLockToggle"></span>
                <!-- /ko -->

                <!-- ko if:closable-->
                    <span class="fa fa-fw fa-times" data-bind="click: tabClose"></span>
                <!-- /ko -->
            </div>
        <!-- /ko -->
        <div class="tabsNoHover" style="width:100%;"></div>
    </div>
</script>
<script type="text/html" id="tabs-frames">

        <!-- ko  foreach: tabs.tabsList -->
        <div class="frameDisplay" data-bind="visible:visible">
            <iframe data-bind="location: $data, iframeName: $data.name, ">

            </iframe>
        </div>
        <!-- /ko -->
</script>
