<?php
    /**
 * CDR Alerts - Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2017 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
use OpenEMR\Core\Header;

include_once("../../../globals.php");
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Clinical Decision Rules Alert Manager - Help");?></title>
    <style>
        .oe-help-heading{
            color:#676666;
            background-color: #E4E2E0;
            border-color: #DADADA;
            padding: 10px 5px;
            border-radius: 5px;
        }
        .oe-help-redirect{
            color:#676666;
        }
        a {
            text-decoration: none !important;
            color:#676666 !important;
            font-weight:700;
        }
        h2 > a {
            font-weight:500;
        }
        @media only screen and (max-width: 768px) {
           [class*="col-"] {
           width: 100%;
           text-align:left!Important;
            }
        }
        @media only screen and (max-width: 1004px) and (min-width: 641px)  {
            .oe-large {
                display: none;
            }
            .oe-small {
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div>
            <center><h2><a name = 'entire_doc'><?php echo xlt("CDR Alert Manager - Help");?></a></h2></center>
        </div>
        <div class= "row">
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
                       
            <ul id='action_list'>
                <li><a href="#div-1"><?php echo xlt("Excepteur Sint Occaecat"); ?></a></li>
                <li><a href="#div-2"><?php echo xlt("Consectetur Adipiscing Elit"); ?></a></li>
                <li><a href="#div-3"><?php echo xlt("Sed Do Eiusmod Tempor Incididunt"); ?></a></li>
                <li><a href="#div-4"><?php echo xlt("Duis Aute Irure"); ?></a></li>
            </ul>
        </div>
        <div class= "row" id="div-1">
            <h4 class="oe-help-heading"><?php echo xlt("Excepteur Sint Occaecat"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong> <?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.</strong>
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
        </div>
        <div class= "row" id="div-2">
            <h4 class="oe-help-heading"><?php echo xlt("Consectetur Adipiscing Elit"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
            <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong> <?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.</strong>
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
        </div>
        <div class= "row" id="div-3">
            <h4 class="oe-help-heading"><?php echo xlt("Sed Do Eiusmod Tempor Incididunt"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
        </div>
        <div class= "row" id="div-4">
            <h4 class="oe-help-heading"><?php echo xlt("Duis Aute Irure"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
            
            <p><?php echo xlt("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua");?>.
        </div>
    </div><!-- end of container div-->
</body>
</html>