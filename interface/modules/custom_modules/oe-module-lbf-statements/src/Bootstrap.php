<?php

/**
 * Menu registration and extra Twig template path for this module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Encounter\EncounterFormsListRenderEvent;
use OpenEMR\Menu\MenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class Bootstrap
{
    public const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";
    public const MODULE_NAME = "oe-module-lbf-statements";

    private static self $instance;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public static function instantiate(EventDispatcherInterface $eventDispatcher, Kernel $kernel): self
    {
        unset($kernel);
        if (!isset(self::$instance)) {
            self::$instance = new Bootstrap($eventDispatcher);
            self::$instance->subscribeToEvents();
        }
        return self::$instance;
    }

    public function subscribeToEvents(): void
    {
        $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, $this->addCustomModuleMenuItem(...));
        $this->eventDispatcher->addListener(
            EncounterFormsListRenderEvent::EVENT_SECTION_RENDER_POST,
            (new EncounterToolbar())->onFormsListRender(...)
        );
    }

    public function addCustomModuleMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'mod';
        $menuItem->menu_id = 'lbfstmt0';
        $menuItem->label = xl('Form statements');
        $menuItem->url = "/interface/modules/custom_modules/" . self::MODULE_NAME . "/public/index.php";
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

    public function getTwig(): Environment
    {
        $kernel = OEGlobalsBag::getInstance()->getKernel();
        $container = new TwigContainer($this->getTemplatePath(), $kernel);
        return $container->getTwig();
    }

    public function getTemplatePath(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }

    public function getPublicUrl(): string
    {
        return OEGlobalsBag::getInstance()->getWebRoot()
            . self::MODULE_INSTALLATION_PATH
            . self::MODULE_NAME
            . "/public/";
    }
}
