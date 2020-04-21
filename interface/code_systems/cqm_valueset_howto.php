<?php

/**
 * Instructions for loading VALUESET Database
 *
 * Copyright (C) 2016 Visolve <services@visolve.com>
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
 * @author  ViSolve Inc <services@visolve.com>
 * @link    http://www.open-emr.org
 */

require_once("../../interface/globals.php");

use OpenEMR\Core\Header;


?>
<?php Header::setupHeader(); ?>
<div class='container'>
    <p>
        <?php echo xlt("Steps to install the VALUSET database"); ?>:
        <ol>
            <li><?php echo xlt("The first step is to download the VALUESET release. Access to VALUESET is provided by NLM. Only valueset for Eligible Professionals need to be downloaded and it should be downloaded in XML format from Sorted By CMS ID column. For more details see the below link") .
            " <a href='https://vsac.nlm.nih.gov/#download-tab' target='_blank' rel='noopener'>https://vsac.nlm.nih.gov/#download-tab</a>."; ?>
            </li>
            <li><?php echo xlt("Place the downloaded VALUESET database zip file into the following directory"); ?>: contrib/cqm_valueset
            </li>
            <li><?php echo xlt("Return to this page and you will be able to complete the Valueset installation process by clicking on the VALUESET section header"); ?>
            </li>
        </ol>
        <h5 class="text-danger"><?php echo xlt("NOTE: Only the XML formats and Eligible Professionals valuesets supported"); ?></h5>
    </p>
</dvi>
