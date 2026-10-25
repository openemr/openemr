<?php

namespace GeneralDermatology;

require_once __DIR__ . "/../vendor/autoload.php";


use GlobalConfig;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Menu\MenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
class Bootstrap
{
    private \Twig\Environment $twig;

    /**
     * @deprecated Not implemented — reserved for future global config integration.
     */
    private mixed $globalsConfig = null;

    /**
     * @deprecated Not implemented — reserved for future event dispatcher integration.
     */
    private mixed $eventDispatcher = null;

    public function __construct(?Kernel $kernel = null)
    {
        if (empty($kernel)) {
            $kernel = new Kernel();
        }

        $twig          = new TwigContainer($this->getTemplatePath(), $kernel);
        $this->twig    = $twig->getTwig();
    }

    public function twigEnv(): \Twig\Environment
    {
        return $this->twig;
    }

    /**
     * @deprecated $globalsConfig is not yet assigned. Do not call until implemented.
     * @return mixed
     */
    public function getGlobalConfig(): mixed
    {
        return $this->globalsConfig;
    }

    /**
     * @deprecated $eventDispatcher is not yet assigned. Do not call until implemented.
     */
    public function addGlobalSettings(): void
    {
        if ($this->eventDispatcher === null) {
            return;
        }
        $this->eventDispatcher->addListener(GlobalsInitializedEvent::EVENT_HANDLE, [$this, 'addGlobalSettingsSection']);
    }

    /**
     * @deprecated $eventDispatcher is not yet assigned. Do not call until implemented.
     */
    public function registerMenuItems(): void
    {
        if ($this->eventDispatcher === null || $this->globalsConfig === null) {
            return;
        }
        if ($this->getGlobalConfig()->getGlobalSetting(GlobalConfig::CONFIG_ENABLE_MENU)) {
            /**
             * @var EventDispatcherInterface $eventDispatcher
             * @var array $module
             * @global $eventDispatcher @see ModulesApplication::loadCustomModule
             * @global $module @see ModulesApplication::loadCustomModule
             */
            $this->eventDispatcher->addListener(MenuEvent::MENU_UPDATE, [$this, 'addCustomModuleMenuItem']);
        }
    }
    public function getTemplatePath(): string
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR;
    }
}
