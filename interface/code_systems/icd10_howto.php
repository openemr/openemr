<?php

/**
 * Instructions for loading ICD10 Database
 *
 * Copyright (C) 2012 Patient Healthcare Analytics, Inc.
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
 * @author  (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

require_once("../../interface/globals.php");

use OpenEMR\Core\Header;

?>
<?php Header::setupHeader(); ?>

<div class="container">
    <p>
        <?php echo xlt("Steps to install the ICD 10 database"); ?>:
        <ol>
            <li><?php echo xlt("The raw data feed release can be obtained from"); ?> <a class='font-weight-bold' href="https://www.cms.gov/Medicare/Coding/ICD10/index" target="_blank" rel="noopener"><?php echo xlt("this location"); ?></a>
            </li>
            <li><?php echo xlt("Place the downloaded ICD 10 database zip files into the following directory"); ?>: contrib/icd10
            </li>
            <li><?php echo xlt("Return to this page and you will be able to complete the ICD10 installation process by clicking on the ICD10 section header"); ?>
            </li>
        </ol>
    </p>
</div>
