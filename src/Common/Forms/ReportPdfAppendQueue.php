<?php

/**
 * Deferred form-supplied pages for patient report PDFs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Forms;

use Mpdf\Mpdf;
use WeakMap;

/** Append form-specific pages after the containing report has rendered its HTML. */
final class ReportPdfAppendQueue
{
    /** @var WeakMap<Mpdf,array<string,callable(Mpdf):void>>|null */
    private static ?WeakMap $pending = null;

    /** Register one callback per form and output PDF. */
    public static function register(Mpdf $pdf, string $key, callable $append): void
    {
        self::$pending ??= new WeakMap();
        $callbacks = self::$pending[$pdf] ?? [];
        $callbacks[$key] = $append;
        self::$pending[$pdf] = $callbacks;
    }

    /** Consume the callbacks once, after the report body has been written. */
    public static function append(Mpdf $pdf): void
    {
        if (self::$pending === null || !isset(self::$pending[$pdf])) {
            return;
        }
        $callbacks = self::$pending[$pdf];
        unset(self::$pending[$pdf]);
        foreach ($callbacks as $callback) {
            $callback($pdf);
        }
    }
}
