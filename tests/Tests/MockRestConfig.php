<?php

/**
 * MockRestConfig is intended to imitate the \RestConfig class that we don't have fully namespaced due to its reliance
 * on globals and a bunch of other stuff.  Methods that are in \RestConfig can be imitated here so we can have some
 * form of testing.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests;

class MockRestConfig
{
    public static $FHIR_ROUTE_MAP;
    public static $systemScopesEnabled = false;

    public static function reset()
    {
        self::$FHIR_ROUTE_MAP = [];
    }

    public static function areSystemScopesEnabled()
    {
        return self::$systemScopesEnabled;
    }
}
