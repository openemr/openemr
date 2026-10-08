<?php

/**
 * CcdaServiceConnectionException is thrown when a C-CDA document cannot be generated: generation is
 * disabled in Globals, or the in-process converter failed. The name predates the removal of the node
 * ccda service and is kept so existing catch blocks continue to work.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

class CcdaServiceConnectionException extends \Exception
{
}
