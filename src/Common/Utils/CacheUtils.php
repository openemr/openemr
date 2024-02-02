<?php

/**
 * CacheUtils handles utility functions to facilitate javascript and other caching utilities in OpenEMR
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

class CacheUtils
{
    public static function getAssetCacheParamRaw()
    {
        return $GLOBALS['v_js_includes'];
    }
    /**
     * Returns the asset cache param that is used to bust the cache when the javascript versions change on the frontend.
     * @return string
     */
    public static function getAssetCacheParam()
    {
        $v = $GLOBALS['v_js_includes'];
        return "v={$v}";
    }

    /**
     * Adds a URL parameter for the cache busting parameter to the passed in path
     * @param $path
     * @return string
     */
    public static function addAssetCacheParamToPath($path)
    {
        return $path . "?" . self::getAssetCacheParam();
    }
}
