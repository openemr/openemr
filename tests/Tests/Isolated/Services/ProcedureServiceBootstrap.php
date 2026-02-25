<?php

/**
 * Bootstrap for ProcedureServiceTest
 *
 * Defines OPENEMR_STATIC_ANALYSIS to prevent code_types.inc.php from
 * executing SQL queries when BaseService is autoloaded. This is required
 * because BaseService has a file-scope require_once that loads
 * code_types.inc.php which calls sqlStatement().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if (!defined('OPENEMR_STATIC_ANALYSIS')) {
    define('OPENEMR_STATIC_ANALYSIS', true);
}
