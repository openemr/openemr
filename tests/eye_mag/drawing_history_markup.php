<?php
/** Render actual drawing templates with real core CSRF/session/BaseDrawing; no site bootstrap or DB. */
namespace OpenEMR\Common\Database {
    class QueryUtils
    {
        public static function querySingleRow($sql, $params = []) {
            if (!str_starts_with(ltrim($sql), 'SELECT') || substr_count($sql, '?') !== count($params)) {
                throw new \RuntimeException('Drawing preference fixture permits only correctly bound reads.');
            }
            return null;
        }
    }
}
namespace EyeDrawingHistoryTest {
    use OpenEMR\Common\Csrf\CsrfUtils;
    use OpenEMR\Core\OEGlobalsBag;
    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

    $coreRoot = $argv[1] ?? dirname(__DIR__, 2);
    $autoload = rtrim($coreRoot, '/') . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new \RuntimeException('Install OpenEMR dependencies or pass the OpenEMR root as the first argument.');
    }
    $loader = require $autoload;
    // Load Eye classes from the current checkout and dependencies from the installed core.
    $eyeRoot = getenv('EYE_TEST_CORE') ?: dirname(__DIR__, 2);
    $eyeClassDirectory = is_dir($eyeRoot . '/src/Forms/EyeMag') ? $eyeRoot . '/src/Forms/EyeMag' : rtrim($coreRoot, '/') . '/src/Forms/EyeMag';
    $eyeClasses = [];
    foreach (glob($eyeClassDirectory . '/*.php') as $classFile) {
        $eyeClasses['OpenEMR\\Forms\\EyeMag\\' . basename($classFile, '.php')] = $classFile;
    }
    $loader->addClassMap($eyeClasses);
    $webroot = '/fixture';
    $OE_SITE_DIR = sys_get_temp_dir() . '/eye-drawing-core-' . bin2hex(random_bytes(6));
    $OE_SITE_WEBROOT = '/fixture/sites/default';
    $oer_config = ['documents' => ['repository' => '/fixture/']];
    mkdir($OE_SITE_DIR . '/images/eye_mag', 0700, true);
    register_shutdown_function(static function () use ($OE_SITE_DIR) {
        foreach (glob($OE_SITE_DIR . '/images/eye_mag/*') ?: [] as $file) unlink($file);
        rmdir($OE_SITE_DIR . '/images/eye_mag');
        rmdir($OE_SITE_DIR . '/images');
        rmdir($OE_SITE_DIR);
    });
    $coreSession = new Session(new MockArraySessionStorage());
    $coreSession->set('authUserID', 1);
    CsrfUtils::setupCsrfKey($coreSession);
    class SessionWrapperFactory
    {
        public static function getInstance() { return new self(); }
        public function getActiveSession() { return $GLOBALS['coreSession']; }
    }
    function attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES); }
    function attr_url($value) { return rawurlencode((string) $value); }
    function text($value) { return attr($value); }
    function xla($value) { return attr($value); }
    function xlt($value) { return attr($value); }
    function sqlQuery($sql, $params) {
        global $saved;
        if (strpos($sql, 'SELECT * from documents') === 0) {
            if (!str_contains($sql, 'foreign_id = ?') || !str_contains($sql, 'deleted = 0') || end($params) !== 1) {
                throw new \RuntimeException('Drawing lookup must stay scoped to the active patient and exclude deleted files.');
            }
            return $saved ? ['id' => 123] : [];
        }
        return [];
    }
    require_once __DIR__ . '/../../interface/forms/eye_mag/php/drawing_backgrounds.php';
    $source = file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php');
    foreach (['canvas_select', 'display_draw_section'] as $function) {
        $start = strpos($source, 'function ' . $function . '(');
        $end = strpos($source, "\n/**", $start);
        eval('namespace EyeDrawingHistoryTest; use OpenEMR\\Core\\OEGlobalsBag; ' . substr($source, $start, $end - $start));
    }
    $form_folder = 'eye_mag';
    $NEURO_DRAWING_JSON = '[]';
    $id = 1;
    $checks = 0;
    foreach ([false, true] as $saved) {
        foreach (['HPI', 'PMH', 'EXT', 'ANTSEG', 'RETINA', 'NEURO', 'IMPPLAN', 'SDRETINA'] as $zone) {
            $documents = ['docs_in_name' => ['Drawings' => [
                ['name' => '1_10_OU_' . $zone . '_VIEW', 'encounter_id' => 10, 'encounter_date' => '2026-10-01', 'document_id' => 100],
                ['name' => '1_9_OU_' . $zone . '_VIEW', 'encounter_id' => 9, 'encounter_date' => '2026-09-01', 'document_id' => 99]
            ]]];
            ob_start();
            display_draw_section($zone, $saved ? 10 : 11, 1);
            $html = ob_get_clean();
            $doc = new \DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new \DOMXPath($doc);
            $selector = $doc->getElementById('SELECT_CANVAS_' . $zone);
            if (!$selector || !$doc->getElementById($zone . '_canvas')) {
                throw new \RuntimeException($zone . ': missing selector/canvas');
            }
            for ($parent = $selector->parentNode; $parent instanceof \DOMElement; $parent = $parent->parentNode) {
                if (in_array($parent->getAttribute('id'), [$zone . '_canvas', $zone . '_olddrawing'], true)) {
                    throw new \RuntimeException($zone . ': history navigation is inside a hidden image container');
                }
            }
            if ($xpath->query('//*[@id="SELECT_CANVAS_' . $zone . '"]/option[@value="current"]')->length !== 1
                || $xpath->query('//*[@id="' . $zone . '_canvas"]//canvas')->length !== 1
                || $xpath->query('//*[@id="Draw_' . $zone . '"]//div[@class="eye-drawing-command-row"]')->length !== 1
                || $xpath->query('//*[@id="Draw_' . $zone . '"]//*[@data-target="SELECT_CANVAS_' . $zone . '"]')->length !== 4) {
                throw new \RuntimeException($zone . ': navigation/editor structure changed');
            }
            $token = $doc->getElementById($zone . '_KONVA_TOOLS')->getAttribute('data-csrf');
            if (!CsrfUtils::verifyCsrfToken($token, session: $coreSession)) {
                throw new \RuntimeException($zone . ': toolbar token must belong to the active core session');
            }
            $checks++;
        }
    }
    // Upstream site overrides stay the default; saved encounter drawings still take priority.
    file_put_contents($OE_SITE_DIR . '/images/eye_mag/OU_RETINA_BASE.jpg', 'fixture');
    foreach ([false, true] as $saved) {
        ob_start();
        display_draw_section('RETINA', 10, 1);
        $html = ob_get_clean();
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        $siteUrl = $OE_SITE_WEBROOT . '/images/eye_mag/OU_RETINA_BASE.jpg';
        if ($doc->getElementById('base_url_RETINA')->getAttribute('value') !== $siteUrl) {
            throw new \RuntimeException('The provider library must retain the upstream site base drawing.');
        }
        $activeUrl = $doc->getElementById('url_RETINA')->getAttribute('value');
        if ($saved ? !str_contains($activeUrl, 'document_id=123') : $activeUrl !== $siteUrl) {
            throw new \RuntimeException('Saved encounter drawings must take priority over the upstream site default.');
        }
    }
    echo "PASS: $checks saved/new drawing templates retain history outside the editor; real core tokens, patient scoping and site defaults pass.\n";
}
