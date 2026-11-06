<?php

/**
 * How the phiMail ADD command labels an attachment.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

enum PhiMailAttachmentType: string
{
    case Text = 'TEXT';
    case Raw = 'RAW';
    case Cda = 'CDA';
    case Ccr = 'CCR';

    /**
     * The type for a clinical document in this format (html, pdf, or xml),
     * or null when phiMail cannot send the format.
     *
     * @param string $documentType CCR, or the CDA document type, such as CCD
     */
    public static function forDocument(string $format, string $documentType): ?self
    {
        return match ($format) {
            'html' => self::Text,
            'pdf' => self::Raw,
            'xml' => $documentType === 'CCR' ? self::Ccr : self::Cda,
            default => null,
        };
    }
}
