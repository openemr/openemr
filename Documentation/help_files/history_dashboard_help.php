<?php
/**
 * History Dashboard Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

require_once("../../interface/globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("History and Lifestyle Help");?></title>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <center><h2><a name='entire_doc'><?php echo xlt("History and Lifestyle Help");?></a></h2></center>
            </div>
            <div class= "row">
                <div class="col-sm-12">
                    <p><?php echo xlt("A place to document and display the patient's past medical history, family history, personal history");?>.

                    <p><?php echo xlt("It has four tabs");?>:
                        <ul>
                            <li><?php echo xlt("General"); ?></li>
                            <li><?php echo xlt("Family History"); ?></li>
                            <li><?php echo xlt("Relatives"); ?></li>
                            <li><?php echo xlt("Lifestyle"); ?></li>
                            <li><?php echo xlt("Other"); ?></li>
                        </ul>
                    <p><?php echo xlt("General Tab - lists the risk factors / past medical conditions on the left and the Results of various clinical exams, procedures and tests on the right");?>.

                    <p><?php echo xlt("Family history - Documents the patient's family history, an ICD10 diagnosis can be linked to the medical conditions");?>.

                    <p><?php echo xlt("Relatives - lists the patient relatives having various medical conditions like Cancer, Diabetes, Hypertension etc");?>.

                    <p><?php echo xlt("Lifestyle - lists the patient's use of Tobacco, Coffee, Alcohol, Recreational Drugs etc");?>.

                    <p><?php echo xlt("Other - lists items not covered in the above sections");?>.

                    <p><?php echo xlt("Users with appropriate privileges can edit these items by clicking on the Edit button");?>.
                    <button type="button" class="btn btn-default btn-edit btn-sm oe-no-float"><?php echo xlt("Edit"); ?></button>

                    <p><?php echo xlt("The default form can be customized by editing it in Administration > Layouts");?>.

                    <p><i class="fa fa-exclamation-triangle oe-text-red"  aria-hidden="true"></i> <strong><?php echo xlt("You will need Administrator privileges to edit this form");?>.</strong>

                    <p><?php echo xlt("There are 3 forms in the Core category - Demographics, Facility Specific User Information and History and all 5 forms in Transactions that can be edited ");?>.

                    <p><?php echo xlt("More information on how to edit this form and other such forms can be found here");?>. &nbsp; <a href="https://www.open-emr.org/wiki/index.php/LBV_Forms" rel="noopener" target="_blank"><i class="fa fa-external-link text-primary" aria-hidden="true" data-original-title="" title=""></i></a>&nbsp;
                    <a href="https://www.open-emr.org/wiki/index.php/Sample_Layout_Based_Visit_Form" rel="noopener" target="_blank"><i class="fa fa-external-link text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                </div>
            </div>

        </div>

        </div><!--end of container div-->
        <script>
           $('#show_hide').click(function() {
                var elementTitle = $('#show_hide').prop('title');
                var hideTitle = '<?php echo xla('Click to Hide'); ?>';
                var showTitle = '<?php echo xla('Click to Show'); ?>';
                $('.hideaway').toggle('1000');
                $(this).toggleClass('fa-eye-slash fa-eye');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                }
                $('#show_hide').prop('title', elementTitle);
            });
        </script>
    </body>
</html>
