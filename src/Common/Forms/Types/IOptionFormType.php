<?php

/*
 * IOEFormType.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms\Types;

/**
 * $frow is a layout_options row; $currvalue is the field's stored value.
 */
interface IOptionFormType {
    /**
     * @param array<mixed> $frow
     */
    public function buildPrintView(array $frow, ?string $currvalue, bool $value_allowed = true);

    /**
     * @param array<mixed> $frow
     */
    public function buildPlaintextView(array $frow, ?string $currvalue);

    /**
     * @param array<mixed> $frow
     */
    public function buildDisplayView(array $frow, ?string $currvalue): string;

    /**
     * @param array<mixed> $frow
     */
    public function buildFormView(array $frow, ?string $currvalue): string;
}
