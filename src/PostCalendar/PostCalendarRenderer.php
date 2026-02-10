<?php

namespace OpenEMR\PostCalendar;

use Twig\Environment;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;

/**
 * Pure Twig renderer for PostCalendar templates
 * Replaces pcSmarty with no Smarty dependencies
 * Uses OEGlobalsBag instead of direct $GLOBALS access
 */
class PostCalendarRenderer
{
    private Environment $twig;
    private OEGlobalsBag $globalsBag;
    private array $templateVars = [];
    private string $templateName = 'default';
    private string $templateView = 'default';

    public function __construct(?OEGlobalsBag $globalsBag = null)
    {
        // Use provided OEGlobalsBag or get singleton instance with compatibility mode
        $this->globalsBag = $globalsBag ?? OEGlobalsBag::getInstance(true);

        // Initialize Twig
        $kernel = $this->globalsBag->get('kernel');
        $container = new TwigContainer(null, $kernel);
        $this->twig = $container->getTwig();

        // Add calendar-specific Twig extensions
        $this->twig->addExtension(new PostCalendarTwigExtensions());

        // Setup default template variables
        $this->setupDefaults();
    }

    /**
     * Assign a variable to the template context
     */
    public function assign(string $key, mixed $value): void
    {
        $this->templateVars[$key] = $value;
    }

    /**
     * Assign by reference (for backwards compatibility)
     * Note: Twig doesn't use references, so this just assigns the value
     */
    public function assign_by_ref(string $key, mixed &$value): void
    {
        $this->templateVars[$key] = $value;
    }

    /**
     * Get a template variable
     */
    public function getVar(string $key): mixed
    {
        return $this->templateVars[$key] ?? null;
    }

    /**
     * Render a template
     *
     * Converts legacy template paths to Twig paths:
     * - "month/ajax_template.html" → "calendar/default/views/month/ajax_template.html.twig"
     * - "user/ajax_search.html" → "calendar/default/views/user/ajax_search.html.twig"
     */
    public function render(string $templatePath): string
    {
        $twigPath = $this->resolveTemplatePath($templatePath);

        if (!$this->twig->getLoader()->exists($twigPath)) {
            throw new \RuntimeException("Template not found: $twigPath");
        }

        return $this->twig->render($twigPath, $this->templateVars);
    }

    /**
     * Display (echo) a rendered template
     */
    public function display(string $templatePath): void
    {
        echo $this->render($templatePath);
    }

    /**
     * Resolve legacy template paths to Twig template paths
     */
    private function resolveTemplatePath(string $path): string
    {
        // Already a Twig path
        if (str_ends_with($path, '.html.twig')) {
            return $path;
        }

        // Pattern: "default/views/month/ajax_template.html"
        if (preg_match('|([^/]+)/views/([^/]+)/([^\\.]+)\\.html|', $path, $matches)) {
            $viewtype = $matches[2];
            $template = $matches[3];
            return "calendar/default/views/$viewtype/$template.html.twig";
        }

        // Pattern: "month/ajax_template.html"
        if (preg_match('|^([^/]+)/([^\\.]+)\\.html$|', $path, $matches)) {
            $viewtype = $matches[1];
            $template = $matches[2];
            return "calendar/default/views/$viewtype/$template.html.twig";
        }

        // Pattern: "user/ajax_search.html"
        if (preg_match('|^user/([^\\.]+)\\.html$|', $path, $matches)) {
            $template = $matches[1];
            return "calendar/default/views/user/$template.html.twig";
        }

        // Default fallback
        return "calendar/default/views/$path.html.twig";
    }

    /**
     * Setup default template variables
     * Uses OEGlobalsBag instead of direct $GLOBALS access
     */
    private function setupDefaults(): void
    {
        // Get theme colors from OEGlobalsBag instead of global variables
        // Note: These are typically set in themes, might need to be in OEGlobalsBag
        $bgcolor1 = $this->globalsBag->get('style')['BGCOLOR1'] ?? '';
        $bgcolor2 = $this->globalsBag->get('style')['BGCOLOR2'] ?? '';
        $bgcolor3 = $this->globalsBag->get('style')['BGCOLOR3'] ?? '';
        $bgcolor4 = $this->globalsBag->get('style')['BGCOLOR4'] ?? '';
        $bgcolor5 = $this->globalsBag->get('style')['BGCOLOR5'] ?? '';
        $bgcolor6 = $this->globalsBag->get('style')['BGCOLOR6'] ?? '';
        $textcolor1 = $this->globalsBag->get('style')['TEXTCOLOR1'] ?? '';
        $textcolor2 = $this->globalsBag->get('style')['TEXTCOLOR2'] ?? '';

        // Session-based defaults
        if (isset($_SESSION['authUserID'])) {
            $this->assign('authUserID', $_SESSION['authUserID']);
        }

        // Module information (these helper functions still exist)
        $pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
        $pcDir = pnVarPrepForOS($pcModInfo['directory']);
        $pcDisplayName = $pcModInfo['displayname'];

        // Theme colors
        $this->assign('BGCOLOR1', $bgcolor1);
        $this->assign('BGCOLOR2', $bgcolor2);
        $this->assign('BGCOLOR3', $bgcolor3);
        $this->assign('BGCOLOR4', $bgcolor4);
        $this->assign('BGCOLOR5', $bgcolor5);
        $this->assign('BGCOLOR6', $bgcolor6);
        $this->assign('TEXTCOLOR1', $textcolor1);
        $this->assign('TEXTCOLOR2', $textcolor2);

        // Language and settings
        $this->assign('USER_LANG', 'eng');
        $this->assign('FUNCTION', pnVarCleanFromInput('func'));
        $this->assign('PRINT_VIEW', pnVarCleanFromInput('print'));

        // Calendar settings (from constants - these are defined in PostCalendar config)
        $this->assign('USE_POPUPS', _SETTING_USE_POPUPS);
        $this->assign('USE_TOPICS', _SETTING_DISPLAY_TOPICS);
        $this->assign('USE_INT_DATES', _SETTING_USE_INT_DATES);
        $this->assign('OPEN_NEW_WINDOW', _SETTING_OPEN_NEW_WINDOW);
        $this->assign('EVENT_DATE_FORMAT', _SETTING_DATE_FORMAT);
        $this->assign('HIGHLIGHT_COLOR', _SETTING_DAY_HICOLOR);
        $this->assign('24HOUR_TIME', _SETTING_TIME_24HOUR);

        // Module info
        $this->assign('MODULE_NAME', $pcDisplayName);
        $this->assign('MODULE_DIR', $pcDir);

        // Settings from OEGlobalsBag instead of $GLOBALS
        $this->assign('translate_appt_categories', $this->globalsBag->get('translate_appt_categories'));
        $this->assign('session_language_choice', $_SESSION['language_choice'] ?? 1);

        // Template name/view
        $this->templateName = _SETTING_TEMPLATE ?? 'default';
        $this->templateView = pnVarCleanFromInput('tplview') ?? 'default';

        $this->assign('TPL_NAME', $this->templateName);
        $this->assign('TPL_VIEW', $this->templateView);

        // Paths from OEGlobalsBag
        $rootdir = $this->globalsBag->get('rootdir');
        $this->assign('TPL_IMAGE_PATH', "$rootdir/main/calendar/modules/$pcDir/pntemplates/{$this->templateName}/images");
        $this->assign('TPL_ROOTDIR', $rootdir);
        $this->assign('TPL_STYLE_PATH', "modules/$pcDir/pntemplates/{$this->templateName}/style");

        // Language direction and chevrons
        $langDir = $_SESSION['language_direction'] ?? 'ltr';
        $this->assign('language_direction', $langDir);
        $this->assign('chevron_icon_left', $langDir === 'ltr' ? 'fa-chevron-circle-left' : 'fa-chevron-circle-right');
        $this->assign('chevron_icon_right', $langDir === 'ltr' ? 'fa-chevron-circle-right' : 'fa-chevron-circle-left');

        // Date
        $this->assign('Date', postcalendar_getDate());
        $this->assign('DATE_STR_CURRENT', date('Ymd'));
    }

    /**
     * Get the OEGlobalsBag instance being used
     */
    public function getGlobalsBag(): OEGlobalsBag
    {
        return $this->globalsBag;
    }
}
