<?php

/**
 * Globals UI for custom statement envelope measurements.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;

class StatementEnvelopeGlobalConfig
{
    /**
     * HTML display section for Administration > Billing > Statement envelope.
     *
     * @param mixed $fldid
     * @param mixed $fldarray
     */
    public static function renderCustomFields($fldid, $fldarray): string
    {
        $twig = ServiceContainer::getTwig();
        $g = OEGlobalsBag::getInstance();
        return $twig->render('billing/statement_envelope_custom.html.twig', [
            'fldid' => $fldid,
            'fldarray' => $fldarray,
            'webroot' => $g->getWebRoot(),
            'assetVersion' => $g->getString('v_js_includes'),
        ]);
    }
}
