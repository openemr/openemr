<?php
 // Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
use OpenEMR\Core\Header;

include_once("../globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Lab Documents - Help");?></title>
    <style>
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
                <center><h2><a name = 'entire_doc'><?php echo xlt("Lab Documents");?></a></h2></center>
            </div>
            <div class= "row">
                <p><?php echo xlt("This page lists all lab documents.");?>
                
                <p><?php echo xlt("Page under construction.");?>
            </div>
             <div class= "row">
                <p><?php echo xlt("Page under construction.");?>
                
                <p><?php echo xlt("Page under construction."); ?> 
            </div>
            
           
        </div><!--end of container div-->
    </body>
</html>
