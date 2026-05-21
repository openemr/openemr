<?php

/**
 * Legacy class-alias shim for edih_x12_file.
 *
 * The class body was lifted to OpenEMR\Billing\EdiHistory\X12File. This
 * file remains so existing procedural callers in library/edihistory/*
 * keep resolving the unqualified `edih_x12_file` symbol.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin McCormick
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2014 Kevin McCormick Longview, Texas
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

class_alias(\OpenEMR\Billing\EdiHistory\X12File::class, 'edih_x12_file');
