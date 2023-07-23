<?php

/**
 * pid.inc.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\PatientSessionUtil;

// Function called to set the global session variable for patient id (pid) number.
function setpid($new_pid)
{
    PatientSessionUtil::setPid($new_pid);
}
