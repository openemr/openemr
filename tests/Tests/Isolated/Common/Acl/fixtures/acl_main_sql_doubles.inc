<?php

/**
 * Global database-function doubles for AclMainPostCalendarCategoryAcoTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

/*
 * sqlQuery() answers the row the test put in $GLOBALS['acl_main_test_row'],
 * or false, which is what the real one returns when no row matches.
 *
 * Deliberately not a .php file: PHPStan and Rector glob *.php, and a
 * global-namespace sqlQuery() in scope would become the signature the whole
 * codebase is analysed against. Load it only in a separate process.
 */

function sqlQuery($statement, $binds = false)
{
    return $GLOBALS['acl_main_test_row'] ?? false;
}
