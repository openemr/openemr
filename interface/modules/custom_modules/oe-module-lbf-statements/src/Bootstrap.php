<?php

/**
 * Menu item and Twig path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Encounter\EncounterFormsListRenderEvent;
use OpenEMR\Menu\MenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Bootstrap
{
    public const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";
    public const MODULE_NAME = "oe-module-lbf-statements";

    private static self $instance;

    /**
     * @param EventDispatcherInterface $eventDispatcher OpenEMR kernel dispatcher.
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * Subscribe once and return the shared bootstrap.
     */
    public static function instantiate(EventDispatcherInterface $eventDispatcher, Kernel $kernel): self
    {
        unset($kernel);
        if (!isset(self::$instance)) {
            self::$instance = new Bootstrap($eventDispatcher);
            self::$instance->subscribeToEvents();
        }
        return self::$instance;
    }

    /**
     * Register the Modules menu item and the encounter toolbar button.
     */
    public function subscribeToEvents(): void
    {
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, $this->addCustomModuleMenuItem(...));
        $this->eventDispatcher->addListener(
            EncounterFormsListRenderEvent::EVENT_SECTION_RENDER_POST,
            (new EncounterToolbar())->onFormsListRender(...)
        );
    }

    /**
     * Add Form statements under the Modules menu.
     */
    public function addCustomModuleMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'mod';
        $menuItem->menu_id = 'lbfstmt0';
        $menuItem->label = xl('Form statements');
        $menuItem->url = $this->getPublicUrl() . 'index.php';
        $menuItem->children = [];
        $menuItem->acl_req = ["encounters", "notes"];
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if (!$item instanceof \stdClass) {
                continue;
            }
            $menuId = $item->menu_id ?? null;
            if (!is_string($menuId) || $menuId !== 'modimg') {
                continue;
            }
            $children = $item->children ?? [];
            if (!is_array($children)) {
                $children = [];
            }
            $children[] = $menuItem;
            $item->children = $children;
            break;
        }

        $event->setMenu($menu);
        return $event;
    }

    /**
     * Twig environment with this module's templates on the loader.
     */
    public function getTwig(): Environment
    {
        $twig = ServiceContainer::getTwig();
        $loader = $twig->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $path = rtrim($this->getTemplatePath(), '/\\');
            $already = false;
            foreach ($loader->getPaths() as $existing) {
                if (rtrim((string) $existing, '/\\') === $path) {
                    $already = true;
                    break;
                }
            }
            if (!$already) {
                $loader->prependPath($path);
            }
        }
        return $twig;
    }

    /**
     * Absolute path to this module's Twig templates.
     */
    public function getTemplatePath(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }

    /**
     * Web-root URL of this module's public/ directory, with trailing slash.
     */
    public function getPublicUrl(): string
    {
        return OEGlobalsBag::getInstance()->getWebRoot()
            . self::MODULE_INSTALLATION_PATH
            . self::MODULE_NAME
            . "/public/";
    }
}
