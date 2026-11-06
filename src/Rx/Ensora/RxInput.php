<?php

/**
 * Text for the values in the RxInput XML that the eRx page posts to Ensora.
 *
 * eRxPage::getXML() used to run regexes over the serialized document to drop
 * carriage returns and tabs and to turn double quotes into single quotes.
 * Cleaning each value before DOM writes it sends the same text without
 * editing the markup.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

final class RxInput
{
    public static function text(string $value): string
    {
        return str_replace([chr(13), chr(9), '"'], ['', '', "'"], $value);
    }
}
