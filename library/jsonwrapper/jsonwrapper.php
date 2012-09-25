<?php
/**
 * jsonwrapper
 *
 * jsonwrapper implements the json_encode function if it is missing,
 * and leaves it alone if it is already present. So it is nicely
 * future-compatible.
 *
 * This script was downloaded on 9/27/2012 from
 * http://www.boutell.com/scripts/jsonwrapper.html and has
 * been released into the public domain. The following quote was
 * taken from the above page:
 * "jsonwrapper itself is hereby released into the public domain. However,
 * it is a simple wrapper around M. Migurski's PEAR JSON library, which has
 * its own free license."
 *
 * @package Services_JSON
 * @link    http://www.open-emr.org
 */

# In PHP 5.2 or higher we don't need to bring this in
if (!function_exists('json_encode')) {
	require_once 'jsonwrapper_inner.php';
} 
?>
