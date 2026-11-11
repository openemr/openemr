<?php

/**
 * Envelope documents stay on the stock PDF when the site template has
 * not opted in.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Statement;

use OpenEMR\Billing\Statement\StatementEnvelopeDocuments;
use OpenEMR\Pdf\Config_Mpdf;
use PHPUnit\Framework\TestCase;

final class StatementEnvelopeDocumentsTest extends TestCase
{
    public function testStockPdfWhenTheSiteTemplateIsAbsent(): void
    {
        $this->assertFalse(StatementEnvelopeDocuments::siteSupportsWindows());
        $this->assertFalse(StatementEnvelopeDocuments::textMarginsAreZero());
        $this->assertNull(StatementEnvelopeDocuments::refusalMessage());
        $this->assertSame('<p>Later sheet</p>', StatementEnvelopeDocuments::forOnePatient('<p>Later sheet</p>'));
        $this->assertSame(Config_Mpdf::getConfigMpdf(), StatementEnvelopeDocuments::pdfConfig());
    }
}
