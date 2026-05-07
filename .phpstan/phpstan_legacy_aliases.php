<?php

/**
 * Class aliases for legacy procedural symbols.
 *
 * Some legacy classes have been lifted into PSR-4 namespaces under `src/`
 * but keep their old global name available for procedural callers via
 * `class_alias()` in their original `library/` files. PHPStan does not
 * follow runtime `class_alias()` calls, so the same aliases are mirrored
 * here at bootstrap so the legacy names resolve during static analysis.
 *
 * Each alias must be paired with a runtime `class_alias()` in the legacy
 * file at the original path. Removing the legacy file (or changing its
 * alias target) requires updating this file in lockstep.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

class_alias(\OpenEMR\Billing\EdiHistory\X12File::class, 'edih_x12_file');
