<?php

/**
 * SSO Module Bootstrap - Event subscriptions and initialization
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Modules\SSO\Services\ProviderRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    public const MODULE_NAME = 'oe-module-sso';
    public const MODULE_PATH = __DIR__ . '/..';

    private EventDispatcherInterface $eventDispatcher;
    private SystemLogger $logger;
    private $kernel;

    public function __construct(EventDispatcherInterface $eventDispatcher, $kernel = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->kernel = $kernel;
        $this->logger = new SystemLogger();
    }

    public function subscribeToEvents(): void
    {
        $this->eventDispatcher->addListener(
            TemplatePageEvent::RENDER_EVENT,
            [$this, 'renderLoginButtons']
        );
    }

    /**
     * Inject SSO login buttons into the login page
     */
    public function renderLoginButtons(TemplatePageEvent $event): void
    {
        // Only add buttons to the login page
        if ($event->getPageName() !== 'login/login.php') {
            return;
        }

        try {
            $registry = new ProviderRegistry();
            $enabledProviders = $registry->getEnabledProviders();

            if (empty($enabledProviders)) {
                return;
            }

            $baseUrl = $GLOBALS['webroot'] . '/interface/modules/custom_modules/' . self::MODULE_NAME;
            $buttons = [];

            foreach ($enabledProviders as $provider) {
                $buttons[] = [
                    'id' => $provider->getId(),
                    'name' => $provider->getName(),
                    'icon' => $provider->getIcon(),
                    'url' => $baseUrl . '/public/authorize.php?provider=' . urlencode($provider->getId()),
                ];
            }

            $html = $this->renderButtonsHtml($buttons);
            $event->setTwigVariables(array_merge(
                $event->getTwigVariables() ?? [],
                ['sso_buttons_html' => $html]
            ));
        } catch (\Exception $e) {
            $this->logger->errorLogCaller('SSO: Error rendering login buttons: ' . $e->getMessage());
        }
    }

    private function renderButtonsHtml(array $buttons): string
    {
        if (empty($buttons)) {
            return '';
        }

        $html = '<div class="sso-login-buttons mt-3">';
        $html .= '<div class="text-center text-muted mb-2"><small>' . xlt('Or sign in with') . '</small></div>';

        foreach ($buttons as $button) {
            $html .= sprintf(
                '<a href="%s" class="btn btn-outline-secondary btn-block mb-2">' .
                '<span class="sso-icon">%s</span> %s</a>',
                attr($button['url']),
                $button['icon'],
                text($button['name'])
            );
        }

        $html .= '</div>';
        return $html;
    }

    public static function getModulePath(): string
    {
        return dirname(__DIR__);
    }
}
