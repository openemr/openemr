<?php
/**
 * Backup Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2017 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

require_once("../globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("EOB Posting - Instructions");?></title>
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
                <center><h2><a name = 'entire_doc'><?php echo xlt("Backup Help");?></a></h2></center>
            </div>
            <div class= "row">
                <p><?php echo xlt("The flip side of instant access to all records is the possibility of instant loss of access to all records");?>.
                
                <p><?php echo xlt("A good backup strategy is essential to guard against the time when data WILL be lost");?>.
                
                <p><?php echo xlt("To be successful the backup should be periodic or continuous and be able to successfully restore data within an appopriate timeframe");?>.
                
                <p><?php echo xlt("In short a robust backup and disaster recovery policy and solution is essential to safeguard against catastrophic data loss and the attendant financial and legal ramifications of such loss");?>.
                
                <p><?php echo xlt("Backups can be of the following types");?>:
               
                <ul>
                    <li><?php echo xlt("Full backup - a complete backup of an application or entire system");?></li>
                    <li><?php echo xlt("Differential - backup all changes since the last FULL backup was performed");?></li>
                    <li><?php echo xlt("Incremental - backup only the changes since that LAST backup was performed");?></li>
                </ul>
                
                <p><?php echo xlt("These methods will backup data at predefined intervals");?>.
                
                <p><?php echo xlt("There are two other concepts that one must be familiar with");?>:
                
                <ul>
                    <li><?php echo xlt("Continuous Data Protection (CDP)");?></li>
                    <li><?php echo xlt("near-CDP");?></li>
                </ul>
                
                <p><?php echo xlt("CDP - copies data from a source to a target every time a change is made and automatically saves a copy of every change made to that data, thus having the ability to restore data to any point in time upto the point of failure");?>.
                 
                <p><?php echo xlt("near-CDP - copies data from a source to a target at pre-set time intervals as snapshots");?>.

                <p><?php echo xlt("Continuous data protection is different from traditional backup in that it is not necessary to specify the point in time to recover from until ready to restore");?>.

                <p><?php echo xlt("Traditional backups only restore data up to the time the backup was made");?>.
                
                <p><?php echo xlt("CDP offers protection against physical loss or corruption as well as logical corruption, i.e. when the data exists but has become corrupted");?>.
                
                <p><?php echo xlt("Traditional backup strategies, i.e. full, incremental or differental backups, that simply replicate data at the block level in whatever condition it exists DO NOT protect against logical corruption");?>.
                 
                <p><?php echo xlt("This means that if the primary data becomes logically corrupted then that corruption is replicated in the backup rendering it useless for recovery");?>.

                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("This backup module lets you create a full backup of the data and website of your OpenEMR installation");?>.</strong>
                 
                <p><?php echo xlt("Its main function is to be able to recover to a previous state if any upgrade fails and either the upgraded database or the website gets corrupted");?>.
                
                <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong><?php echo xlt("This should NOT be the only backup strategy in place");?>.</strong>

                <p><?php echo xlt("There are four actions that can be performed");?>:
                
                <ul id='action_list'>
                    <li><a href="#full_backup"><?php echo xlt("Create Full Backup"); ?></a></li>
                    <li><a href="#export_configuration"><?php echo xlt("Export Configuration"); ?></a></li>
                    <li><a href="#import_configuration"><?php echo xlt("Import Configuration"); ?></a></li>
                    <li><a href="#eventlog_backup"><?php echo xlt("Backup the eventlog"); ?></a></li>
                </ul>
                
                <p><?php echo xlt("With data generated an OpenEMR installation can be recovered");?>.
            </div>
            <div class= "row" id="full_backup">
                <h4 class="oe-help-heading"><?php echo xlt("Create Full Backup"); ?><a href="#action_list"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Click on the 'Create Full Backup' button to backup the entire openEMR database as well as the entire website");?>.

                <p><?php echo xlt("A compressed archive 'emr_backup.tar' will be created and will be automatically downloaded into the Downloads folder of the computer that you are running the command from");?>.
                
                <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong><?php echo xlt("If you are executing this from any computer other than the one which holds the OpenEMR installation, over a network, the time to download depends on the size of the database and website and the network speed - (sometimes may take many hours) especially if your database and website holds a lot of data");?>.</strong>
            </div>
            <div class= "row" id="export_configuration">
                <h4 class="oe-help-heading"><?php echo xlt("Export Configuration"); ?><a href="#action_list"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Lets you export select tables and entries within some tables containing configuration data");?>.
                
                <p><?php echo xlt("To activate this feature check the checkbox Administration > Globals > Features > Configuration Export/Import and click 'Save'");?>.

                <p><?php echo xlt("There are three sections");?>:
                
                <li><?php echo xlt("Tables  - contains data pertaining to Services, Products, Prices, Document Categories, Fee Sheet Options, Translations");?></li>
                <li><?php echo xlt("Lists - All the items in table 'list_options' that are used through out the program ");?></li>
                <li><?php echo xlt("Layouts - layout details of forms using the Layout Based Templates stored in table 'layout_options'");?></li>
                <br>
                <p><?php echo xlt("Select the needed and click 'Continue'");?>.
                
                <p><?php echo xlt("It will download a file called 'openemr_config.sql' that contains all the data from the selected tables as a sql file");?>.

                <p><?php echo xlt("This file can then be imported into a new install to avoid having to input this data");?>.
                
            </div>
            <div class= "row" id="import_configuration">
                <h4 class="oe-help-heading"><?php echo xlt("Import Configuration"); ?><a href="#action_list"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Lets you import configuration data into the OpenEMR database");?>.

                <p><?php echo xlt("Useful when you setup a new install and want to import the configuration values of a pervious install");?>.
                
                <p><?php echo xlt("Click 'Browse' and select a sql file to import. You are usually importing a previously created openemr_cofig_sql file ans then click 'Continue'");?>.

                <p><i class='fa fa-exclamation-triangle' style='color:red' aria-hidden='true'></i> <strong><?php echo xlt("WARNING: This will overwrite configuration information with data from the uploaded file") ?>.</strong>
                
                <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong><?php echo xlt("Use this feature only with newly installed sites, otherwise you will destroy references to/from existing data");?>.</strong></p>
               
                <p><?php echo xlt("Can use to recover the current install if you have modified any of the configuration data and want to revert to previous working version");?>.
            </div>
            <div class= "row" id="eventlog_backup">
                <h4 class="oe-help-heading"><?php echo xlt("Backup the eventlog"); ?><a href="#action_list"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("All events/actions that are done to the databse in OpenEMR are logged");?>.

                <p><?php echo xlt("The log data is stored in two tables 'log' and 'log_comment_encrypt'");?>.
                
                <p><?php echo xlt("These tables can get large over time");?>.

                <p><?php echo xlt("Click on 'Eventlog backup' to backup this data as a eventlog_yyyymmdd_hhmmss.sql file in the tmp directory under a directory called 'emr_eventlog_backup'");?>.
                
                <p><?php echo xlt("It then creates two new and empty tables for fresh log data");?>.

            </div>
            <div class= "row" id="restore">
                <h4 class="oe-help-heading"><?php echo xlt("Recovery"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("For OpenEMR installations on a Linux server use the following method to restore the OpenEMR application");?>.
                
                <p><?php echo xlt("Brief explanation of what will happen"); ?>: 
                
                <ul>
                    <li><?php echo xlt("A compressed archive is being used to recover an OpenEMR installation"); ?></li>
                    <li><?php echo xlt("The compressed archive (default name -  `emr_backup.tar`) has 2 compressed archives - one containing the website data and the other the database data"); ?></li>
                    <li><?php echo xlt("In addition there are 2 files - 'openemr-setup.txt' containing details regarding the website and database and a script called 'restore' that will be used for the actual restoration"); ?></li>
                    <li><?php echo xlt("You will first delete the existing OpenEMR website and then recreate a new one with data from the compressed archive by running the 'restore' script"); ?></li>
                </ul>
                
                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("You will need the  MySQL root user password to successfully complete this task");?>.</strong>

                <p><?php echo xlt("Copy the 'emr_backup.tar' that was created into the home directory on the server that you want to install OpenEMR");?>.
                
                <p><?php echo xlt("If you have downloaded the backup directory 'emr_backup.tar' on another machine you should move it to the home directory of a user on the server that you want to install OpenEMR");?>.
                
                <p><?php echo xlt("Open Terminal on the server that hosts the OpenEMR installation or ssh into it from another computer by using PuTTY for Windows or the terminal for a Mac or Linux");?>.
                
                <p><?php echo xlt("Extract the 'openemr-setup.txt' file from the emr_backup.tar compressed folder by issuing the following command after the dollar sign: sudo ./tar -xvf emr_backup.tar openemr-setup.txt");?>.
                
                <p><?php echo xlt("It contains details of the OpenEMR installation. To read its contents issue following command sudo ./cat openemr-setup.txt");?>.
                
                <p><?php echo xlt("Select the data by highlighting with a mouse, press Ctrl + Insert to copy and Shift + Insert to paste into a text file");?>.
                
                <p><?php echo xlt("Extract the 'restore' script from the emr_backup.tar compressed folder by issuing the following command sudo ./tar -xvf emr_backup.tar restore");?>.
                
                <p><?php echo xlt("Make the  'restore' script executable  by issuing the following command sudo ./chmod 755 restore");?>.
                
                <p><i class='fa fa-exclamation-triangle' style='color:red' aria-hidden='true'></i> <strong><?php echo xlt("Delete the openemr web directory");?>.</strong>
                
                <p><?php echo xlt("If you are on terminal or have logged in via ssh you can issue this command after the dollar sign: sudo rm -rf <path to website> i.e. ($ sudo rm -rf  /var/www/html/openemr)");?>.
                
                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong> <?php echo xlt("Be careful with this command , make sure that you have typed the correct path. It will delete the named directory and all its contents without any further warning");?>.</strong>
                    
                <p><?php echo xlt("Issue this command after the dollar sign: sudo ./restore ($ sudo ./restore), 1 space after $ with no space before restore");?>.
                
                <p><?php echo xlt("Enter the path to the backup file: /home/<your home directory username>/emr_backup.tar");?>.
                
                <p><?php echo xlt("Change values as needed by referring to the openemr-setup file that you had saved");?>.
                
                <p><?php echo xlt("Enter the MySQL root user password");?>.
                
                <p><?php echo xlt("Wait for the script to finish executing");?>.
                
                <p><?php echo xlt("If all goes well you should have a restored OpenEMR installation");?>.
                
                <p><?php echo xlt("Download the recovery instructions for those with OpenEMR installed on a Linux server as you will not be able to read this page when you are trying to restore the site"); ?>. &nbsp <a href="restore_instructions_linux.txt" class= "btn btn-default btn-download" download="Restore Instructions" target="_blank"> <?php echo xlt("Download"); ?></a>
               
                <p><?php echo xlt("For OpenEMR installations on a windows machine use the following method to restore the OpenEMR application by clicking on the following link");?>. <strong><a href="http://www.open-emr.org/wiki/index.php/Windows_Backup_And_Restore_Made_Easy" target="_blank"><?php echo xlt("Windows Backup and Recovery"); ?></a></strong> <strong><a href=""> </a>
                    
            </div>   
        </div><!--end of container div-->
    </body>
</html>
