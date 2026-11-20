<?php

/**
 * Isolated tests for choosing the phiMail ADD type of a clinical document.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\DirectMessaging\PhiMailAttachmentType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PhiMailAttachmentTypeTest extends TestCase
{
    /**
     * @return array<string, array{string, string, ?PhiMailAttachmentType}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function documentProvider(): array
    {
        return [
            'html' => ['html', 'CCD', PhiMailAttachmentType::Text],
            'pdf' => ['pdf', 'CCD', PhiMailAttachmentType::Raw],
            'CDA xml' => ['xml', 'CCD', PhiMailAttachmentType::Cda],
            'CCR xml' => ['xml', 'CCR', PhiMailAttachmentType::Ccr],
            'unsupported format' => ['docx', 'CCD', null],
        ];
    }

    #[DataProvider('documentProvider')]
    public function testChoosesTheTypeForADocument(string $format, string $documentType, ?PhiMailAttachmentType $expected): void
    {
        self::assertSame($expected, PhiMailAttachmentType::forDocument($format, $documentType));
    }
}
