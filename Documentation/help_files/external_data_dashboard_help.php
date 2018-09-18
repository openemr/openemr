<?php
/**
 * External Data Dashboard Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @version 1.0.0
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
 
use OpenEMR\Core\Header;

require_once("../../interface/globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("External Data Help");?></title>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <center><h2><a name='entire_doc'><?php echo xlt("External Data Help");?></a></h2></center>
            </div>
            <div class= "row">
                <div class="col-sm-12">
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <ul>
                        <li><a href="#section1"><?php echo xlt("Lorum Ipsum Section 1");?></a></li>
                        <li><a href="#section2"><?php echo xlt("Lorum Ipsum Section 2");?></a></li>
                        <li><a href="#section3"><?php echo xlt("Lorum Ipsum Section 3");?></a></li>
                        <li><a href="#section4"><?php echo xlt("Lorum Ipsum Section 4");?></a></li>
                    </ul>
                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Lorum Ipsum Section 1"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Lorum Ipsum Section 2"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Lorum Ipsum Section 3"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.</strong>
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    <button type="button" class="btn btn-default btn-sm oe-no-float"><i class="fa fa-envelope"></i></button>
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <strong><?php echo xlt("You need administrator privileges to enable the MedEx Communication Service"); ?>.</strong>
                    
                    <p><strong><?php echo xlt("LORUM IPSUM SUBSECTION"); ?> :</strong>
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    <button type="button" class="btn btn-default btn-add btn-sm oe-no-float"><?php echo xlt("Add New"); ?></button>
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum list"); ?>.
                        <ul>
                            <li><?php echo xlt("Lorem ipsum list - 1"); ?></li>
                            <li><?php echo xlt("Lorem ipsum list - 2"); ?></li>
                            <li><?php echo xlt("Lorem ipsum list - 3"); ?></li>
                            <li><?php echo xlt("Lorem ipsum list - 4"); ?></li>
                        </ul>
                <div>
            </div>
            <div class= "row" id="section4">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Lorum Ipsum Section 4"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                    
                    <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor");?>.
                        
                    <p><strong><?php echo xlt("LORUM IPSUM SUBSECTION"); ?> :</strong>
                    
                    <p><?php echo xlt("Clicking on the  Create A Dated Reminder   button will bring up the  Send a Reminder  popup"); ?>.
                    <button type="button" class="btn btn-default btn-add btn-sm oe-no-float"><?php echo xlt("Create A Dated Reminder"); ?></button>
                    
                    <p><?php echo xlt("Lorem ipsum dolor link");?>.
                    <a href="https://www.lipsum.com/" target="_blank"><i class="fa fa-external-link text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    
                    <p><?php echo xlt("To see to all Lorum ipsum lists that are available click on the eye icon."); ?>&nbsp <i id="show_hide" class="fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i>
                    
                    <div id="aco_list" class='hideaway' style='display: none;'>
                        <ul>
                            <li><strong><?php echo xlt('Lorum Ipsum List Level 1 - section 1');?></strong></li>
                                <ul>
                                    <li><?php echo xlt('Lorum Ipsum List Level 2');?></li>
                                    <li><?php echo xlt('Lorum Ipsum List Level 2');?></li>
                                    <li><?php echo xlt('Lorum Ipsum List Level 2');?></li>
                                    <li><?php echo xlt('Lorum Ipsum List Level 2');?></li>
                                    <li><?php echo xlt('Lorum Ipsum List Level 2');?></li>
                                </ul>
                            <li><strong><?php echo xlt('Lorum Ipsum List Level 1 - section 2');?></strong></li>
                                <ul>
                                    <li><?php echo xlt('Lorum Ipsum List Level 2');?></li>
                                    <li><?php echo xlt('Lorum Ipsum List Level 2');?></li>
                                    <li><?php echo xlt('Lorum Ipsum List Level 2');?></li>
                                    <li><?php echo xlt('Lorum Ipsum List Level 2');?> <i class="fa fa-exclamation-circle" style="color:blue" aria-hidden="true"></i>&nbsp;<strong><?php echo xlt("New in  Ver 7"); ?></strong></li>
                                    <li><?php echo xlt('Lorum Ipsum List Level 2');?> <i class="fa fa-exclamation-circle" style="color:magenta" aria-hidden="true"></i>&nbsp;<strong><?php echo xlt("New in Ver 6"); ?></strong></li>
                                </ul>
                        </ul>
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
