<?php

namespace Juggernaut\Module\Bamboo;

use Juggernaut\Module\Bamboo\Controllers\ResourcesConfig;
use OpenEMR\Events\PatientDemographics\RenderEvent;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Twig\TwigExtension;
use OpenEMR\Events\Core\TwigEnvironmentEvent;
use OpenEMR\Core\Kernel;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Utils\DateFormatterUtils;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";

    const MODULE_NAME = "oe-bamboo-pmp";
    /**
     * @var EventDispatcherInterface The object responsible for sending and subscribing to events through the OpenEMR system
     */
    private EventDispatcher $eventDispatcher;
    private Kernel $kernel;
    private Environment $twig;
    private ResourcesConfig $resourcesConfig;

    public function __construct(EventDispatcher $eventDispatcher, ?Kernel $kernel = null)
    {
        if ($kernel) {
            $this->kernel = $kernel;
        }
        $this->eventDispatcher = $eventDispatcher;
        $twig = new TwigContainer($this->getTemplatePath(), $kernel);
        $twigEnv = $twig->getTwig();
        $this->twig = $twigEnv;
        $this->resourcesConfig = new ResourcesConfig();

    }

    public function subscribeToEvents(): void
    {
        $registered = $this->resourcesConfig->getConnectionData();
        if ($registered['password']) {
            $this->eventDispatcher->addListener(RenderEvent::EVENT_RENDER_POST_PAGELOAD, [$this, 'renderButtonTextingPostLoad']);
        }
    }

    public function registerMenuItems(): void
    {
        $this->eventDispatcher->addListener(MenuEvent::class, [$this, 'addCustomModuleMenuItem']);
    }
    private function getTemplatePath(): string
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }

    /**
     * @return Environment
     */
    public function getTwig(): Environment
    {
        $twigLoader = new FilesystemLoader($this->paths);
        $twigEnv = new Environment($twigLoader, ['autoescape' => false]);
        $globalsService = new GlobalsService($GLOBALS, [], []);
        $twigEnv->addExtension(new TwigExtension($globalsService, $this->kernel));

        $coreExtension = $twigEnv->getExtension(CoreExtension::class);
        // set our default date() twig render function if no format is specified
        // we set our default date format to be the localized version of our dates and our time formats
        // by default Twig uses 'F j, Y H:i' for the format which doesn't match our OpenEMR dates as configured from the globals
        $dateFormat = DateFormatterUtils::getShortDateFormat() . " " . DateFormatterUtils::getTimeFormat();
        $coreExtension->setDateFormat($dateFormat);

        if ($this->kernel) {
            if ($this->kernel->isDev()) {
                $twigEnv->addExtension(new DebugExtension());
                $twigEnv->enableDebug();
            }
            $event = new TwigEnvironmentEvent($twigEnv);
            $this->kernel->getEventDispatcher()->dispatch($event, TwigEnvironmentEvent::EVENT_CREATED, 10);
        }

        return $twigEnv;
    }
    public function addCustomModuleMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'mod';
        $menuItem->menu_id = 'mod0';
        $menuItem->label = xlt("Bamboo PMP");
        $menuItem->url = "/interface/modules/custom_modules/oe-bamboo-pmp/public/crs_resource.php";
        $menuItem->children = [];

        /**
         * This defines the Access Control List properties that are required to use this module.
         * Several examples are provided
         */
        $menuItem->acl_req = [];

        /**
         * If you want your menu item to allows be shown then leave this property blank.
         */
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if ($item->menu_id == 'modimg') {
                $item->children[] = $menuItem;
                break;
            }
        }

        $event->setMenu($menu);

        return $event;
    }
    public function renderButtonTextingPostLoad()
    {
    ?>

    <script>
        let navbar33 = document.querySelector('#myNavbar');
        let ele33 = document.createElement("div");
        ele33.innerHTML = "<button class='btn btn-success  ml-2 mr-2' id='bambooPmp'><?php echo xlt("Bamboo PMP") ?></button>";
        navbar33.appendChild(ele33);

        document
            .getElementById('bambooPmp')
            .addEventListener('click', function (e){
                e.preventDefault();
                bambooDisplay();
            });

        function bambooDisplay(){
            window.location.href = "/interface/modules/custom_modules/oe-bamboo-pmp/public/history.php";
            return false;
        }
    </script>
    <?php
    }
}
