<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.xla.php
 * Type:     modifier
 * Name:     xla
 * Purpose:  Translate via xl() and then escape via attr().
 * -------------------------------------------------------------
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
function smarty_modifier_xla($translate)
{
    return \xla($translate);
}
