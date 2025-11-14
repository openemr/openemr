<?php
/*
 * IOEFormType.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms\Types;

interface IOptionFormType {
    public function buildPrintView($frow, $currvalue, $value_allowed = true);
    public function buildPlaintextView($frow, $currvalue);
    public function buildDisplayView($frow, $currvalue): string;
    public function buildFormView($frow, $currvalue): string;
}
