<?php

/**
 * PatientPortalPDFDocumentCreator is used for generating pdf documents from html documents that have been submitted
 * via a patient either from the patient portal or in a patient smart app.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Pdf;

use HTMLPurifier_Config;
use HTMLPurifier_URIDefinition;
use Mpdf\Mpdf;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Pdf\Config_Mpdf;
use OpenEMR\Pdf\SameOriginUriFilter;

class PatientPortalPDFDocumentCreator
{
    public function createPdfObject($htmlIn): Mpdf
    {
        $html = is_string($htmlIn) ? $htmlIn : '';
        $config_mpdf = Config_Mpdf::getConfigMpdf();
        $pdf = new Mpdf($config_mpdf);
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if ($session->get('language_direction') === 'rtl') {
            $pdf->SetDirectionality('rtl');
        }

        $allowedHostSpec = self::allowedHostSpec();
        if ($allowedHostSpec !== '') {
            // Allow http(s) fetches for same-host resources (e.g. /barcode.php); block file:// and php://.
            // Pin mPDF's base path to the trusted host so relative references resolve there,
            // not against the request's HTTP_HOST.
            $pdf->whitelistStreamWrappers = ['http', 'https'];
            $pdf->SetBasePath('http://' . $allowedHostSpec . '/');
        } else {
            // No trusted host configured — refuse every stream wrapper so hostless relative
            // references cannot be resolved against the request's HTTP_HOST.
            $pdf->whitelistStreamWrappers = [];
        }

        // Keep every inline <style> block but drop any url() / @import values that leave the current host.
        $styleBlock = '';
        if (preg_match_all('#<\s*style\b[^>]*>(.*?)</\s*style\s*>#s', $html, $styleMatches) > 0) {
            foreach ($styleMatches[1] as $styleContent) {
                $styleBlock .= '<style>' . self::sanitizeCss($styleContent, $allowedHostSpec) . '</style>';
            }
            $html = preg_replace('#<\s*style\b[^>]*>.*?</\s*style\s*>#s', '', $html) ?? $html;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('URI.AllowedSchemes', ['data' => true, 'http' => true, 'https' => true]);
        $uriDefinition = $config->getDefinition('URI', true);
        assert($uriDefinition instanceof HTMLPurifier_URIDefinition);
        $uriDefinition->addFilter(new SameOriginUriFilter($allowedHostSpec), $config);
        $purify = new \HTMLPurifier($config);
        $html = $purify->purify($html);

        $stylesheet = "<style>.signature {vertical-align: middle;max-height:65px; height:65px !important;width:auto !important;}</style>";
        $html = "<!DOCTYPE html><html><head>" . $styleBlock . $stylesheet . "</head><body>$html</body></html>";
        $pdf->writeHtml($html);
        return $pdf;
    }

    public function createPdfDocument($cpid, $formFilename, $documentCategory, $htmlIn): \Document
    {

        $pdf = $this->createPdfObject($htmlIn);
        if (!$cpid) {
            throw new \InvalidArgumentException("Missing Patient ID");
            echo js_escape("ERROR " . xla("Missing Patient ID"));
            exit();
        }
        $data = $pdf->Output($formFilename, 'S');
        $d = new \Document();
        $rc = $d->createDocument($cpid, $documentCategory, $formFilename, 'application/pdf', $data);
        if (empty($rc)) {
            return $d;
        } else {
            throw new \RuntimeException("Failed to create document: " . $rc);
        }
    }

    /**
     * Resolve the host[:port] spec the PDF renderer trusts for http(s) resource references.
     * Only the `pdf_allowed_host` global is trusted; when blank, remote fetches are rejected
     * across the board (the request's SERVER_NAME is attacker-controllable in default Apache
     * UseCanonicalName Off configurations).
     */
    private static function allowedHostSpec(): string
    {
        return OEGlobalsBag::getInstance()->getString('pdf_allowed_host', '');
    }

    /**
     * Strip url() and @import references that leave the current host:port.
     */
    private static function sanitizeCss(string $css, string $allowedHostSpec): string
    {
        // url(...) wrapper form. Match either a quoted value (which may contain `)` characters)
        // or an unquoted value up to the first `)`.
        $css = (string) preg_replace_callback(
            '#url\s*\(\s*((?:"[^"]*"|\'[^\']*\'|[^)]*))\s*\)#i',
            static function (array $m) use ($allowedHostSpec): string {
                $raw = trim($m[1]);
                $len = strlen($raw);
                if (
                    $len >= 2
                    && (($raw[0] === '"' && $raw[$len - 1] === '"') || ($raw[0] === "'" && $raw[$len - 1] === "'"))
                ) {
                    $raw = substr($raw, 1, -1);
                } elseif (str_contains($raw, '"') || str_contains($raw, "'")) {
                    return 'url("")';
                }
                return self::isAllowedResourceUrl($raw, $allowedHostSpec) ? $m[0] : 'url("")';
            },
            $css
        );
        // @import "..." / '...'  (quoted-string).
        $css = (string) preg_replace_callback(
            '#@import\s+(["\'])([^"\']*)\1\s*;?#i',
            static fn(array $m): string => self::isAllowedResourceUrl(trim($m[2]), $allowedHostSpec) ? $m[0] : '',
            $css
        );
        // @import <url>;  (bare, no url() wrapper, no quotes).
        $css = (string) preg_replace_callback(
            '#@import\s+(?!url\()(?!["\'])([^\s;]+)\s*;?#i',
            static fn(array $m): string => self::isAllowedResourceUrl(trim($m[1]), $allowedHostSpec) ? $m[0] : '',
            $css
        );
        return $css;
    }

    private static function isAllowedResourceUrl(string $url, string $allowedHostSpec): bool
    {
        if ($url === '' || str_starts_with($url, 'data:')) {
            return true;
        }
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }
        // Reject embedded credentials outright (user:pass@host authority spoofing).
        if (isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }
        $hasScheme = isset($parts['scheme']);
        $hasHost = isset($parts['host']);
        if (!$hasScheme) {
            // Pure relative URL (no scheme, no authority) resolves against mPDF's base path,
            // which createPdfObject() pins to the trusted host.
            if (!$hasHost) {
                return true;
            }
            // Protocol-relative "//host[:port]/path" — require same-host:port match.
            return self::hostPortMatches($parts['host'], $parts['port'] ?? null, 'http', $allowedHostSpec);
        }
        if (!$hasHost) {
            return false;
        }
        if (strcasecmp($parts['scheme'], 'http') !== 0 && strcasecmp($parts['scheme'], 'https') !== 0) {
            return false;
        }
        return self::hostPortMatches($parts['host'], $parts['port'] ?? null, $parts['scheme'], $allowedHostSpec);
    }

    private static function hostPortMatches(string $host, mixed $port, string $scheme, string $allowedSpec): bool
    {
        if ($allowedSpec === '' || $host === '') {
            return false;
        }
        [$allowedHost, $allowedPort] = SameOriginUriFilter::splitHostPort($allowedSpec);
        if (strcasecmp($host, $allowedHost) !== 0) {
            return false;
        }
        $default = strcasecmp($scheme, 'https') === 0 ? 443 : 80;
        $uriPort = is_numeric($port) ? (int) $port : $default;
        $expected = $allowedPort ?? $default;
        return $uriPort === $expected;
    }
}
