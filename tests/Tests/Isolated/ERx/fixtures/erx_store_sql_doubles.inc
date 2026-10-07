<?php

/**
 * Global database-function doubles for ERxStoreMissingRowTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

/*
 * sqlQuery() answers the row the test put in $GLOBALS['erx_store_test_row'] to
 * every query, or false, which is what the real one returns when no row
 * matches.
 *
 * Deliberately not a .php file: PHPStan and Rector glob *.php, and
 * global-namespace doubles in scope would become the signatures the whole
 * codebase is analysed against. Load it only in a separate process.
 */

function sqlQuery($statement, $binds = false)
{
    return $GLOBALS['erx_store_test_row'] ?? false;
}
