<?php

/**
 * How the phiMail ADD command labels an attachment.
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @author  Michael A. Smith <michael@opencoreemr.com>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
