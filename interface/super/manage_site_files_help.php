<?php
 /**
/**
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 * 
 * @package OpenEMR
 * @author Ranganath Pathak <pathak01@hotmail.com>
 * @copyright Copyright (c) 2017 Ranganath Pathak
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link http://www.open-emr.org 
 */

use OpenEMR\Core\Header;

require_once ("../globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Files Help");?></title>
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
                <center><h2><a name = 'entire_doc'><?php echo xlt("File Management Help");?></a></h2></center>
            </div>
            <div class= "row">
                <p><?php echo xlt("This page allows you to perform  five functions on the OpenEMR installation");?>.
                
                <ul id='action_list'>
                    <li><a href='#edit_files'><?php echo xlt("Edit Files");?></a></li>
                    <li><a href='#upload_images'><?php echo xlt("Upload Images");?></a></li>
                    <li><a href='#upload_patient_ed'><?php echo xlt("Upload Patient Education material");?></a></li>
                    <li><a href='#generate_thumbnails'><?php echo xlt("Generate Thumbnails");?></a></li>
                    <li><a href='#mime_white_list'><?php echo xlt("Create a MIME White List");?></a></li>
                </ul>    
                
                <p><?php echo xlt(" Start by selecting an action from the 'Select an Action' dropwown box");?>.
                
                <p><?php echo xlt("It will reveal the relevant portions needed to perform the selected action");?>:
            </div>
            <div class= "row" id="edit_files">
                <h4 class="oe-help-heading"><?php echo xlt("Edit Files"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Lets you edit six files located in openemr/sites/default directory");?>.
                
                <ul>
                    <li>config.php - <?php echo xlt("contains configuration information");?></li>
                    <li>faxcover.txt - <?php echo xlt("customize the fax cover sheet");?></li>
                    <li>faxtile.eps - <?php echo xlt("");?></li>
                    <li>referral_template.html - <?php echo xlt("edit the referral template file");?></li>
                    <li>statement.inc.php - <?php echo xlt("customize the statements that are sent out to patients");?></li>
                    <li>custom_pdf.php <?php echo xlt("in the letter_templates sub-directory");?></li>
                </ul>
                
                <p><?php echo xlt("You are dealing with the files that are a part of the OpenEMR installation");?>.
                
                <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong><?php echo xlt("Changes made to these files may affect the running of OpenEMR");?>.</strong>
                
                <p><?php echo xlt("You should have a good understanding of the structure of OpenEMR  and what you are trying to achieve");?>.
                
                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("These files must allow the web-server to write to them, i.e user 'www-data' in Ubuntu or Debian running apache2");?>.</strong>
                
                <p><?php echo xlt("Upon selecting the 'Edit Files' option the file editor window is revealed");?>.
                
                <p><?php echo xlt("Select one of the above six files to edit");?>.
                
                <p><?php echo xlt("Edit the file and click 'Save'");?>.
            </div>
            <div class= "row" id="upload_images">
                <h4 class="oe-help-heading"><?php echo xlt("Upload Images"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Lets you upload images to the sites/default/images directory");?>.
                                
                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("The web server needs to have permission to write to this directory");?>.</strong>
                
                <p><?php echo xlt("Select 'Browse' to open a dialog box that lets you choose the image file that you want to upload");?>.
                
                <p><?php echo xlt("You can type a different name by typing in the 'Destination File' box or use the original file name");?>.
                
                <p><?php echo xlt("Click 'Save'");?>.

                <p><?php echo xlt("Click 'Reset' if you do not want to upload the file that you have selected");?>.
                
            </div>
            <div class= "row" id="upload_patient_ed">
                <h4 class="oe-help-heading"><?php echo xlt("Upload Patient Education material"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Lets you upload customized patient education material as pdfs to the sites/default/documents/education directory");?>.

                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("The web server needs to have permission to write to this directory");?>.</strong>
                
                <p><?php echo xlt("They have to be a pdf file and should be named as codetype_code_language.pdf, i.e  icd10_K60.1_en.pdf");?>.

                <p><?php echo xlt("These files will be available in the issues page under 'Coding', click on the selction to open the 'Education materials' selection page");?>.
                
                <p><?php echo xlt("The default lets you search in MedlinePlus ");?>.
               
                <p><?php echo xlt("Click on the 'Select source' arrowhead and select 'Local Source' and click 'Submit' to view the relevant uploaded file matched according to ICD code");?>.
            </div>
            <div class= "row" id="generate_thumbnails">
                <h4 class="oe-help-heading"><?php echo xlt("Generate Thumbnails"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("To activate this feature go to Administration > Globals > Documents > Generate thumbnail, check the checkbox and click 'Save'");?>.
                
                <p><?php echo xlt("You can also set the size of the generated thumbnail here");?>.
                
                <p><?php echo xlt("Click on 'Generate' to generate thumbnails of images in the images directory");?>.
           </div>
            <div class= "row" id="mime_white_list">
                <h4 class="oe-help-heading"><?php echo xlt("MIME White List"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("The Multipurpose Internet Mail Extensions (MIME) type is a standardized way to indicate the nature and format of a document");?>.
                
                <p><?php echo xlt("Browsers use the MIME type (and not the file extension) to determine how it will process a document");?>.
                
                <p><?php echo xlt("It consists of a type and a subtype, two strings, separated by a '/' e.g text/plain, image/jpeg");?>.
                
                <p><?php echo xlt("By limiting the MIME types that can be uploaded into the sites/default/documents directory you can prevent extraneous file types from being uploaded");?>.
                
                <p><?php echo xlt("To activate this feature go to Administration > Globals > Security > Secure Upload Files with White List, check the checkbox and click 'Save'");?>.
                
                <p><?php echo xlt("Go to Adminstration > Files > Select action, and select the 'MIME White List' option");?>.
                
                <p><?php echo xlt("It will display the 'Create custom white list' section");?>.
                
                <p><?php echo xlt("It consists of a 'Black List' and a 'White List'");?>.
                
                <p><?php echo xlt("By default all MIME types are in the black list, i.e  cannot upload any file to 'Documents' ");?>.
                
                <p><?php echo xlt("You can move the MIME types from the black List to the white list or vice-versa by using the appopriate buttons and click 'Save'");?>.
                
                <p><?php echo xlt("Alternatively a MIME type can be manually added to the white list");?>.
            </div>   
        </div><!--end of container div-->
    </body>
</html>
