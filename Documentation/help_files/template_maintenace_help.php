<?php

/**
 * import_template.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>

<div id='help-panel' class='container-fluid border-0 my-2 collapse'>
    <div class='card'>
        <div class='card-block bg-light px-2'>
            <div class='card-title'><h5><?php echo xlt('In General'); ?></h5></div>
            <div class='card-text px-1'>
                <ul>
                    <li><?php echo xlt('The Scope toolbar is global to the entire form. It consists of Location, Category and action buttons. "Refresh" to clear Location selections, "Send" to send templates to patient(s), "Files" for uploading templates and a Submit to submit Location and Category current selections. By selecting location and or category, all the tables in the form will reflect those selection.'); ?></li>
                    <li><?php echo xlt('Additionally, to the right is a toggle to select if you want to use the pop out window or dialog editor. The window allows having multiple editors open at the same time where the dialog is more appropriate for making quick edits'); ?></li>
                    <li><?php echo xlt('Best practice is to select Location(s) then a Category for the location then click the appropriate action button.'); ?></li>
                    <li><?php echo xlt('There are three main template views. The first is the Repository where you can store templates for future edits and disposal. '); ?><?php echo xlt('Second is the All Patients. This View shows all current templates that are default to all patients.'); ?><?php echo xlt('Lastly is the view to show templates assigned to various patients.'); ?></li>
                    <li><?php echo xlt('Concerning the General category. After being assigned to an active view, the General template will always appear as a base template in portal Pending documents i.e no category and as Default in the dashboard view.'); ?></li>
                    <li><?php echo xlt('Once a template is uploaded, you may change the category at anytime from the repository view.'); ?></li>
                </ul>
            </div>
        </div>
        <div class='card-block bg-light px-2'>
            <div class='card-title'><h5><?php echo xlt('Template Uploads'); ?></h5></div>
            <div class='card-text px-1'>
                <ul>
                    <li><?php echo xlt('To upload local text or html templates, select the destination Location(s), a Category then click Files to show the file browser. You may select as many files to upload with in reason'); ?>&nbsp;
                        <?php echo xlt('Eventually file name becomes a Pending document selection in Portal Documents.'); ?><br />
                        <em><?php echo xlt('For example: Privacy_Agreement.txt becomes "Privacy Agreement" button in Patient Documents.'); ?></em></li>
                    <li><?php echo xlt('While you may choose to upload directly to patient locations, it is best practice to upload to the repository then send to patients from there.'); ?></li>
                    <!--<li><?php /*echo xlt(''); */ ?></li>
                    <li><?php /*echo xlt(''); */ ?></li>
                    <li><?php /*echo xlt(''); */ ?></li>-->
                </ul>
            </div>
        </div>
        <div class='card-block bg-light px-2'>
            <div class='card-title'><h5><?php echo xlt('Sending Templates'); ?></h5></div>
            <div class='card-text px-1'>
                <ul>
                    <li><?php echo xlt('To send template(s) to patient(s), simply select be patient(s) from the Location select then select the templates to send from the repository send check box.'); ?></li>
                    <li><?php echo xlt('Once a template has been selected, the Send button will show in the Scope toolbar. Click it to perform the action. A confirmation popup will then show on success or an error message otherwise.'); ?></li>
                </ul>
            </div>
        </div>
        <button type='button' data-toggle='collapse' data-target='#help-panel' class='btn btn-sm btn-secondary'><?php echo xlt('Dismiss'); ?></button>
    </div>
    <br />
</div>
