<?php

/**
 * Compile all .twig templates to verify syntax and filter/function/test references.
 *
 * compileSource() tokenizes, parses, and compiles a template to PHP without
 * rendering it. The parse step validates that every referenced filter, function,
 * and test is registered with the Twig environment. {% extends %}, {% include %},
 * and {% import %} are recorded as nodes but not resolved until render time, so
 * each template compiles independently.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Twig;

use OpenEMR\Common\Twig\TwigContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Source;
use Twig\TwigFunction;

#[Group('isolated')]
#[Group('twig')]
class TwigTemplateCompilationTest extends TestCase
{
    private static ?Environment $twig = null;

    /**
     * Template directories outside templates/ that contain .twig files.
     * Each directory is added to the Twig FilesystemLoader so template
     * names resolve correctly during compilation.
     */
    private const EXTRA_TEMPLATE_DIRS = [
        'interface/forms/clinical_notes/templates',
        'interface/forms/newpatient/templates',
        'interface/forms/observation/templates',
        'interface/forms/soap/templates',
        'interface/forms/vitals/templates',
        'interface/modules/custom_modules/oe-module-comlink-telehealth/templates',
        'interface/modules/custom_modules/oe-module-ehi-exporter/templates',
        'interface/modules/custom_modules/oe-module-faxsms/templates',
    ];

    /**
     * Directories to scan for .twig files.
     */
    private const SEARCH_DIRS = [
        'templates',
        'interface/forms',
        'interface/modules/custom_modules',
    ];

    protected function setUp(): void
    {
        $GLOBALS['fileroot'] ??= self::fileroot();
        $GLOBALS['date_display_format'] ??= 0;
    }

    #[Test]
    #[DataProvider('twigTemplateProvider')]
    public function templateCompiles(string $templateName, string $filePath): void
    {
        $twig = self::twigEnvironment();
        $code = file_get_contents($filePath);
        self::assertIsString($code, "Failed to read $filePath");
        $source = new Source($code, $templateName, $filePath);

        try {
            $twig->compileSource($source);
        } catch (\Throwable $e) { // @codeCoverageIgnoreStart
            $relativePath = str_replace(self::fileroot() . '/', '', $filePath);
            self::fail("Template $relativePath failed to compile: " . $e->getMessage());
        } // @codeCoverageIgnoreEnd

        // compileSource() returns a string of PHP code on success
        $this->addToAssertionCount(1);
    }

    /**
     * Provide [templateName, absolutePath] for every .twig file in the repo.
     *
     * PHPUnit runs data providers before starting coverage instrumentation,
     * so this method and helpers it calls (resolveTemplateName, fileroot)
     * will never appear as covered.
     *
     * @codeCoverageIgnore
     * @return iterable<string, array{string, string}>
     */
    public static function twigTemplateProvider(): iterable
    {
        $fileroot = self::fileroot();

        foreach (self::SEARCH_DIRS as $searchDir) {
            $absDir = $fileroot . '/' . $searchDir;
            if (!is_dir($absDir)) {
                continue;
            }

            /** @var RecursiveIteratorIterator<RecursiveDirectoryIterator> $iterator */
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($absDir, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (!$file->isFile() || !str_ends_with($file->getFilename(), '.twig')) {
                    continue;
                }

                $absolutePath = $file->getPathname();
                $relativePath = substr($absolutePath, strlen($fileroot) + 1);
                $templateName = self::resolveTemplateName($relativePath);

                // Use the relative path as the dataset key for readable --testdox output
                yield $relativePath => [$templateName, $absolutePath];
            }
        }
    }

    /**
     * Build and cache the Twig environment with all loader paths and extensions.
     */
    private static function twigEnvironment(): Environment
    {
        if (self::$twig !== null) {
            return self::$twig;
        }

        $GLOBALS['fileroot'] ??= self::fileroot();
        $GLOBALS['date_display_format'] ??= 0;

        $twigContainer = new TwigContainer();
        $twig = $twigContainer->getTwig();

        // Add extra template directories so the loader can resolve template names
        // for forms and modules that store templates outside the main templates/ dir.
        $loader = $twig->getLoader();
        if (!$loader instanceof FilesystemLoader) {
            throw new \RuntimeException('Expected FilesystemLoader');
        }
        $fileroot = self::fileroot();
        foreach (self::EXTRA_TEMPLATE_DIRS as $dir) {
            $absDir = $fileroot . '/' . $dir;
            if (is_dir($absDir)) {
                $loader->addPath($absDir);
            }
        }

        // Register stub functions that production code adds at runtime.
        // C_EncounterVisitForm registers displayOptionClass dynamically;
        // the stub lets those templates compile without the full form controller.
        $twig->addFunction(new TwigFunction('displayOptionClass', fn () => ''));

        self::$twig = $twig;
        return $twig;
    }

    /**
     * Convert a repo-relative file path to a Twig template name that the
     * FilesystemLoader can resolve.
     *
     * Files under templates/ use the path relative to templates/ (the default
     * loader root). Files under extra template directories use the path
     * relative to the nearest registered templates/ directory.
     *
     * @codeCoverageIgnore Called only from the data provider.
     */
    private static function resolveTemplateName(string $relativePath): string
    {
        if (str_starts_with($relativePath, 'templates/')) {
            return substr($relativePath, strlen('templates/'));
        }

        foreach (self::EXTRA_TEMPLATE_DIRS as $dir) {
            $prefix = $dir . '/';
            if (str_starts_with($relativePath, $prefix)) {
                return substr($relativePath, strlen($prefix));
            }
        }

        // Fallback: use the full relative path (will likely fail to resolve,
        // producing a clear error message).
        return $relativePath;
    }

    /** @codeCoverageIgnore Called from the data provider. */
    private static function fileroot(): string
    {
        return dirname(__DIR__, 5);
    }
}
