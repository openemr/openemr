<?php
/**
 * interface/patient_file/ccr_import.php Upload screen and parser for the CCR XML.
 *
 * Functions to upload the CCR XML and to parse and insert it into audit tables.
 *
 * Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
 * @link    http://www.open-emr.org
 * @author  Eldho Chacko <eldho@zhservices.com>
 * @author  Ajil P M <ajilpm@zhservices.com>
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017 Jason Oettinger
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
require_once(dirname(__FILE__) . "../../globals.php");

use OpenEMR\Core\Header;

?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Import');?></title>
<style>
.list-group-item {
    display: list-item;
}
</style>
</head>
<body class="body_top" >
  <main class="container">
    <div class="row">
      <div class="col-md-3"></div>
      <div class="col-md-6">
        <h3><?php echo xlt("Steps for uploading CCR XML");?></h3>
        <ol class="list-group">
          <li class="list-group-item"><?php echo xlt('For an existing patient, go to Patient Summary->Documents. For a new patient, go to Miscellanous->New Documents').'.'; ?></li>
          <li class="list-group-item"><?php echo xlt('Upload the xml file under the category CCR').'.'; ?></li>
          <li class="list-group-item"><?php echo xlt('After Uploading, click "Import"').'.'; ?></li>
          <li class="list-group-item"><?php echo xlt('Approve the patient from Patient/Client->Import->Pending Approval').'.'; ?></li>
        </ol>
      </div>
    </div>
  </main>
</body>
</html>
