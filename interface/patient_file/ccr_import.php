<?php

/**
 * interface/patient_file/ccr_import.php Upload screen and parser for the CCR XML.
 *
 * Functions to upload the CCR XML and to parse and insert it into audit tables.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Ajil P M <ajilpm@zhservices.com>
 * @author    Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017 Jason Oettinger
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "../../globals.php");

use OpenEMR\Core\Header;

?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Import');?></title>
</head>
<body>
    <div class="container mt-3">
        <h2><?php echo xlt("Steps for uploading CCR XML");?></h2>
        <div class="jumbotron py-4">
            <ol class="list-group">
                <li class="list-group-item">1. <?php echo xlt('For an existing patient, go to Patient Summary->Documents. For a new patient, go to Miscellanous->New Documents') . '.'; ?></li>
                <li class="list-group-item">2. <?php echo xlt('Upload the xml file under the category CCR') . '.'; ?></li>
                <li class="list-group-item">3. <?php echo xlt('After Uploading, click "Import"') . '.'; ?></li>
                <li class="list-group-item">4. <?php echo xlt('Approve the patient from Patient/Client->Import->Pending Approval') . '.'; ?></li>
            </ol>
        </div>
    </div>
</body>
</html>
