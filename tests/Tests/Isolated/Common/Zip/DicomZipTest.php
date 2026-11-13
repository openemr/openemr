<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Zip;

use OpenEMR\Common\Zip\DicomZip;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ZipArchive;

#[Group('isolated')]
class DicomZipTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = tempnam(sys_get_temp_dir(), 'dcmzip');
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    private static function dicom(): string
    {
        return str_repeat("\0", 128) . 'DICM' . str_repeat("\0", 16);
    }

    /** @param array<string, string> $entries name => content; a name ending in / is a folder */
    private function zip(array $entries): ZipArchive
    {
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($this->path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        foreach ($entries as $name => $content) {
            if (str_ends_with($name, '/')) {
                $zip->addEmptyDir(rtrim($name, '/'));
            } else {
                $zip->addFromString($name, $content);
            }
        }
        $zip->close();
        $reopened = new ZipArchive();
        $this->assertTrue($reopened->open($this->path));
        return $reopened;
    }

    /** @return array<string, array{string, bool}> */
    public static function ignoredEntries(): array
    {
        return [
            'folder' => ['study/', true],
            'resource fork folder' => ['__MACOSX/study/._1.dcm', true],
            'dot file' => ['study/.DS_Store', true],
            'dot file at the top' => ['._1.dcm', true],
            'image' => ['study/1.dcm', false],
            'image without extension' => ['study/IM0001', false],
        ];
    }

    #[DataProvider('ignoredEntries')]
    public function testIsIgnoredEntry(string $name, bool $expected): void
    {
        $this->assertSame($expected, DicomZip::isIgnoredEntry($name));
    }

    public function testPlainDicomZipIsAccepted(): void
    {
        $zip = $this->zip(['1.dcm' => self::dicom(), '2.dcm' => self::dicom()]);
        $this->assertTrue(DicomZip::normalize($zip));
    }

    public function testFolderAndMacOsMetadataDoNotMakeTheZipInvalid(): void
    {
        $zip = $this->zip([
            'study/' => '',
            'study/1.dcm' => self::dicom(),
            '__MACOSX/study/._1.dcm' => 'AppleDouble',
            'study/.DS_Store' => 'x',
        ]);
        $this->assertTrue(DicomZip::normalize($zip));
    }

    public function testOneNonDicomFileMakesTheZipInvalid(): void
    {
        $zip = $this->zip(['1.dcm' => self::dicom(), 'readme.txt' => 'not dicom']);
        $this->assertFalse(DicomZip::normalize($zip));
    }

    public function testZipWithoutImagesIsNotDicom(): void
    {
        $zip = $this->zip(['study/' => '', '.DS_Store' => 'x']);
        $this->assertFalse(DicomZip::normalize($zip));
    }

    public function testDicomFilesWithoutExtensionAreRenamedKeepingTheirFolder(): void
    {
        $zip = $this->zip(['a/IM0001' => self::dicom(), 'b/IM0001' => self::dicom()]);
        $this->assertTrue(DicomZip::normalize($zip));
        $zip->close();
        $check = new ZipArchive();
        $check->open($this->path);
        $this->assertSame(['a/IM0001.dcm', 'b/IM0001.dcm'], [$check->getNameIndex(0), $check->getNameIndex(1)]);
    }

    public function testDicomFileWithADotNameDoesNotCrash(): void
    {
        $zip = $this->zip(['1.dcm' => self::dicom(), '.hidden' => self::dicom()]);
        $this->assertTrue(DicomZip::normalize($zip));
    }
}
