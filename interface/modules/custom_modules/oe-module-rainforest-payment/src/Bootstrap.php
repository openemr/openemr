<?php

/**
 * Rainforest Payment Module Bootstrap Class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Firehed
 * @copyright Copyright (c) 2026 TBD <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\RainforestPayment;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Services\Globals\GlobalSetting;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/oe-module-rainforest-payment";
    const MODULE_NAME = "oe-module-rainforest-payment";

    private readonly string $moduleDirectoryName;
    private $logger;
    private readonly string $modulePath;
    public string $installPath;
    private GlobalConfig $globalsConfig;

    public function __construct(
        /**
         * @var EventDispatcherInterface The object responsible for sending and subscribing to events
         */
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        $globalsBag = OEGlobalsBag::getInstance();
        $this->installPath = $globalsBag->get('web_root') . self::MODULE_INSTALLATION_PATH;
        $this->moduleDirectoryName = basename(dirname(__DIR__));
        $this->modulePath = dirname(__DIR__);
        $this->logger = new SystemLogger();
        $this->globalsConfig = new GlobalConfig();
    }

    /**
     * @return void
     */
    public function subscribeToEvents(): void
    {
        $this->addGlobalSettings();
    }

    /**
     * Register global settings for the module
     */
    public function addGlobalSettings(): void
    {
        $this->eventDispatcher->addListener(
            GlobalsInitializedEvent::EVENT_HANDLE,
            $this->addGlobalSettingsSection(...)
        );
    }

    /**
     * Add global settings section to OpenEMR administration
     */
    public function addGlobalSettingsSection(GlobalsInitializedEvent $event): void
    {
        $globalsBag = OEGlobalsBag::getInstance();
        $service = $event->getGlobalsService();
        $section = xlt("Rainforest Payment Gateway");
        $service->createSection($section, 'Payment');

        $settings = $this->globalsConfig->getGlobalSettingSectionConfiguration();

        foreach ($settings as $key => $config) {
            $value = $globalsBag->get($key, $config['default']);
            $service->appendToSection(
                $section,
                $key,
                new GlobalSetting(
                    xlt($config['title']),
                    $config['type'],
                    $value,
                    xlt($config['description']),
                    true
                )
            );
        }
    }

    /**
     * Get the globals config instance
     * 
     * @return GlobalConfig
     */
    public function getGlobalConfig(): GlobalConfig
    {
        return $this->globalsConfig;
    }

    /**
     * Get the globals bag instance for accessing global variables
     * 
     * @return OEGlobalsBag
     */
    public function getGlobalsBag(): OEGlobalsBag
    {
        return OEGlobalsBag::getInstance();
    }
}
