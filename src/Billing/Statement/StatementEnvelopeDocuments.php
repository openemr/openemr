<?php

/**
 * Envelope windows for a statement PDF, email, or saved invoice.
 *
 * The site template opts in by defining StatementSiteTemplate. An older
 * copy of statement.inc.php does not, and the stock PDF margins stay.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use OpenEMR\Billing\StatementEnvelope;
use OpenEMR\Pdf\Config_Mpdf;

final class StatementEnvelopeDocuments
{
    /**
     * This site's statement template draws envelope windows.
     */
    public static function siteSupportsWindows(): bool
    {
        return class_exists(\StatementSiteTemplate::class, false)
            && \StatementSiteTemplate::supportsEnvelopeWindows();
    }

    /**
     * mPDF settings for a statement. Windowed profiles use a zero margin
     * so the template's own positions land in the envelope windows.
     *
     * @return array<string, mixed>
     */
    public static function pdfConfig(): array
    {
        if (!self::siteSupportsWindows()) {
            return Config_Mpdf::getConfigMpdf();
        }

        return StatementEnvelope::fromGlobals()->mpdfConfig();
    }

    /**
     * One patient's own file. A batch keeps the window CSS on the first
     * sheet and a page break before each later one, so a saved or emailed
     * copy needs that CSS and must not open on a blank page.
     */
    public static function forOnePatient(string $html): string
    {
        if (!self::siteSupportsWindows()) {
            return $html;
        }

        return StatementEnvelope::fromGlobals()->individualDocument($html);
    }

    /**
     * A windowed text statement is positioned from the corner of the sheet.
     * The stock text margins push that text out of the windows.
     */
    public static function textMarginsAreZero(): bool
    {
        return self::siteSupportsWindows() && StatementEnvelope::fromGlobals()->isWindowed();
    }

    /**
     * Why a run must not print, or null when the profile can be used.
     * None is a valid choice and is not refused here.
     */
    public static function refusalMessage(): ?string
    {
        if (!self::siteSupportsWindows() || !StatementEnvelope::fromGlobals()->isMisconfigured()) {
            return null;
        }

        return xl('The custom envelope measurements are blank or do not fit the page. No statements were printed.');
    }
}
