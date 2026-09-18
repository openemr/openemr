<?php

/**
 * Guard that every inline <script src>/<link href> carries a cache-buster.
 *
 * version.php sets the rule: whenever a .js or .css file changes, every URL
 * that references it must end with `?v=$v_js_includes`, or a browser that
 * has cached the old copy keeps using it. Header::setupHeader() appends the
 * parameter for assets declared in config/config.yaml; tags written directly
 * into a view bypass that and have to append it themselves. How long a stale
 * asset survives depends on how aggressively a deployment caches static
 * files, so the rule has to hold everywhere.
 *
 * The mirror rule matters too: a stylesheet handed to mPDF is read off the
 * filesystem, and mPDF's AssetFetcher fopen()s the href verbatim. A query
 * string on a filesystem path makes that open fail and the stylesheet is
 * silently dropped from the PDF.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[Group('isolated')]
#[Group('core')]
class AssetCacheBusterTest extends TestCase
{
    /**
     * Path prefixes outside the served application: vendored libraries,
     * generated documentation, and test fixtures.
     */
    private const SKIP_PREFIXES = [
        'ccdaservice/',
        'contrib/',
        'docs/',
        'Documentation/',
        'gacl/',
        'public/assets/',
        'sites/',
        'swagger/',
        'tests/',
    ];

    /**
     * Files whose asset references legitimately carry no cache-buster, with
     * the reason. Keep this list short and justified -- the default answer to
     * a new entry is to append the parameter instead.
     */
    private const EXEMPT_FILES = [
        'acknowledge_license_cert.html' =>
            'Static HTML served with no template engine, so there is nothing to interpolate.',
        'admin.php' =>
            'The multi-site admin page runs without the Composer autoloader, so neither '
            . 'attr_url() nor OEGlobalsBag is available.',
        'interface/forms/CAMOS/help.html' =>
            'Static HTML served with no template engine, so there is nothing to interpolate.',
        'setup.php' =>
            'The installer renders before version.php or the application globals are loaded.',
    ];

    /**
     * Expressions that resolve to a filesystem path rather than a URL, in
     * lower case. A reference built from one of these is read by mPDF off
     * disk and must not carry a query string.
     */
    private const FILESYSTEM_ROOT_TOKENS = [
        '__dir__',
        'fileroot',
        'includeroot',
        'oe_site_dir',
        'projectdir',
        'webserver_root',
    ];

    private const VIEW_EXTENSIONS = ['php', 'twig', 'html', 'htm', 'tpl', 'inc'];

    /**
     * Stand-in for the `->` of a template expression such as
     * `{$form->template_dir}`. Without it the tag scan below would read that
     * `>` as the end of the tag and miss the href entirely.
     */
    private const ARROW = "\x01";

    private const TAG_PATTERN = '/<(?:script|link)\b([^>]*)>/i';

    private const ATTR_PATTERN = '/\b(?:src|href)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i';

    #[Test]
    #[DataProvider('webReferenceProvider')]
    public function webReferenceCarriesACacheBuster(string $where, string $url): void
    {
        self::assertMatchesRegularExpression(
            '/[?&]v=/',
            $url,
            "$where: asset URL has no ?v= cache-buster, so a browser that cached the"
            . ' previous copy keeps using it after the file changes (see version.php). Append'
            . " ?v=<?php echo attr_url(OEGlobalsBag::getInstance()->getString('v_js_includes')); ?>"
            . ' (Twig: ?v={{ assetVersion|attr_url }}, Smarty: ?v={assetVersionNumber}).'
        );
    }

    #[Test]
    #[DataProvider('filesystemReferenceProvider')]
    public function filesystemReferenceCarriesNoQueryString(string $where, string $url): void
    {
        self::assertStringNotContainsString(
            '?',
            $url,
            "$where: this href resolves to a filesystem path, which mPDF fopen()s verbatim."
            . ' A query string makes the open fail and the stylesheet is dropped from the PDF.'
        );
    }

    /** @return iterable<string, array{string, string}> */
    public static function webReferenceProvider(): iterable
    {
        foreach (self::references() as $reference) {
            [$where, $url, $isFilesystem] = $reference;
            if ($isFilesystem || self::isExternal($url)) {
                continue;
            }
            yield $where => [$where, $url];
        }
    }

    /** @return iterable<string, array{string, string}> */
    public static function filesystemReferenceProvider(): iterable
    {
        foreach (self::references() as $reference) {
            [$where, $url, $isFilesystem] = $reference;
            if (!$isFilesystem) {
                continue;
            }
            yield $where => [$where, $url];
        }
    }

    /**
     * Every .js/.css reference in a served view, as "path:line" plus the raw
     * attribute value and whether it resolves to a filesystem path.
     *
     * @return iterable<array{string, string, bool}>
     */
    private static function references(): iterable
    {
        foreach (self::viewFiles() as $path => $contents) {
            if (isset(self::EXEMPT_FILES[$path])) {
                continue;
            }
            $stream = self::markupStream($path, $contents);
            if (!preg_match_all(self::TAG_PATTERN, $stream, $tags, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                continue;
            }
            foreach ($tags as $tag) {
                if (!preg_match(self::ATTR_PATTERN, $tag[1][0], $attr)) {
                    continue;
                }
                $url = self::firstNonEmpty($attr);
                if (!preg_match('/\.(?:js|css)$/i', rtrim(explode('?', $url, 2)[0]))) {
                    continue;
                }
                $url = str_replace(self::ARROW, '->', $url);
                $line = substr_count(substr($stream, 0, $tag[0][1]), "\n") + 1;
                yield ["$path:$line", $url, self::isFilesystemPath($url)];
            }
        }
    }

    /**
     * Tracked view files, keyed by repo-relative path.
     *
     * Only tracked files are in scope. Composer installs modules such as
     * oe-module-claimrev-connect into interface/modules/custom_modules/, and
     * those live in their own repositories -- a fix has to land there, and any
     * edit made here is wiped by the next composer install.
     *
     * @return iterable<string, string>
     */
    private static function viewFiles(): iterable
    {
        $root = self::projectRoot();
        foreach (self::trackedPaths() as $path) {
            if (!in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::VIEW_EXTENSIONS, true)) {
                continue;
            }
            foreach (self::SKIP_PREFIXES as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    continue 2;
                }
            }
            $contents = file_get_contents($root . '/' . $path);
            if ($contents === false || !preg_match('/<(?:script|link)\b/i', $contents)) {
                continue;
            }
            yield $path => $contents;
        }
    }

    /** @return list<string> */
    private static function trackedPaths(): array
    {
        $git = new Process(['git', 'ls-files'], self::projectRoot());
        $git->mustRun();

        return array_values(array_filter(explode("\n", $git->getOutput())));
    }

    /**
     * Flatten a view into a single markup stream.
     *
     * Inline HTML and string-literal bodies pass through verbatim, so a tag
     * echoed from a heredoc reads the same as one written as inline HTML.
     * Every other PHP token keeps its source text with quotes, angle brackets
     * and newlines neutralised, so an interpolated expression stays visible
     * inside an attribute value without terminating the tag. Newline counts
     * are preserved throughout, which keeps reported line numbers exact.
     *
     * HTML comments are dropped: a commented-out tag is never served.
     */
    private static function markupStream(string $path, string $contents): string
    {
        $isPhp = str_ends_with($path, '.php') || str_ends_with($path, '.inc');
        $stream = $isPhp ? self::flattenPhp($contents) : $contents;

        $stream = (string) preg_replace_callback(
            '/<!--.*?-->/s',
            static fn(array $m): string => str_repeat("\n", substr_count($m[0], "\n")),
            $stream
        );

        return str_replace('->', self::ARROW, $stream);
    }

    private static function flattenPhp(string $code): string
    {
        $out = '';
        foreach (token_get_all($code) as $token) {
            if (!is_array($token)) {
                $out .= str_replace(['"', "'", '<', '>'], ' ', $token);
                continue;
            }
            [$id, $text] = $token;
            if ($id === T_INLINE_HTML || $id === T_ENCAPSED_AND_WHITESPACE) {
                $out .= $text;
            } elseif ($id === T_CONSTANT_ENCAPSED_STRING) {
                $out .= substr($text, 1, -1);
            } elseif (in_array($id, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG, T_START_HEREDOC, T_END_HEREDOC], true)) {
                $out .= str_repeat("\n", substr_count($text, "\n"));
            } else {
                $out .= str_replace(["\n", '"', "'", '<', '>'], ' ', $text)
                    . str_repeat("\n", substr_count($text, "\n"));
            }
        }
        return $out;
    }

    /** @param array<int, string> $attr */
    private static function firstNonEmpty(array $attr): string
    {
        foreach ([1, 2, 3] as $group) {
            if (($attr[$group] ?? '') !== '') {
                return $attr[$group];
            }
        }
        return '';
    }

    private static function isExternal(string $url): bool
    {
        return (bool) preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $url);
    }

    private static function isFilesystemPath(string $url): bool
    {
        $needle = strtolower($url);
        foreach (self::FILESYSTEM_ROOT_TOKENS as $token) {
            if (str_contains($needle, $token)) {
                return true;
            }
        }
        return false;
    }

    private static function projectRoot(): string
    {
        return dirname(__DIR__, 4);
    }
}
