<?php

/**
 * Identifies one of the four vendor-specific HL7 order generators.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\ProcedureOrder;

use OpenEMR\Common\Orders\Hl7OrderResult;

/**
 * Every implementation of the gen_hl7_order contract is a global function
 * declared in an include file rather than a service, so a test can only name
 * one by calling it. This enum lets a data provider name a generator without
 * resolving it: PHPUnit builds data sets before setUpBeforeClass() has run the
 * require_once calls, so anything that binds a function at creation time — a
 * first class callable, a callable-string, a Closure::fromCallable() — fails
 * with "Call to undefined function". A case is inert until {@see generate()}
 * runs it from inside the test body, by which point the includes are loaded.
 */
enum Hl7OrderGenerator
{
    /** interface/orders/gen_hl7_order.inc.php — the generator every lab gets unless it ships its own. */
    case DefaultLab;

    /** interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php */
    case Universal;

    /** interface/procedure_tools/labcorp/gen_hl7_order.inc.php */
    case LabCorp;

    /** interface/procedure_tools/quest/gen_hl7_order.inc.php */
    case Quest;

    public function generate(int $orderId): Hl7OrderResult
    {
        return match ($this) {
            self::DefaultLab => default_gen_hl7_order($orderId),
            self::Universal => universal_gen_hl7_order($orderId),
            self::LabCorp => labcorp_gen_hl7_order($orderId),
            self::Quest => quest_gen_hl7_order($orderId),
        };
    }
}
