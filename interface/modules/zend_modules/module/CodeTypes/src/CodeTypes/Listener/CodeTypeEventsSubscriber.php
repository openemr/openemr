<?php

/**
 * CodeTypeEventsSubscriber  Handles the mapping of code systems to our list options.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ZendModules\CodeTypes\Listener;

use Psr\Log\{
    AbstractLogger,
    LoggerInterface,
};
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Events\Codes\CodeTypeInstalledEvent;
use OpenEMR\Events\Core\SQLUpgradeEvent;
use OpenEMR\Services\CodeTypes\CodeTypeMappingUpdater;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @deprecated - use OpenEMR\Services\CodeTypes\CodeTypeMappingUpdater
 */
class CodeTypeEventsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            SQLUpgradeEvent::EVENT_UPGRADE_POST => 'onSqlUpgradeEvent'
            ,CodeTypeInstalledEvent::EVENT_INSTALLED_POST => 'onCodeTypeInstalledEvent'
        ];
    }

    public function onSqlUpgradeEvent(SQLUpgradeEvent $event)
    {
        // we want to push out to the system that we are making changes...
        // $logger = function ($message) use ($event): void {
        //     // make sure we escape this here.
        //     $event->getSqlUpgradeService()->flush_echo(text($message) . "<br />");
        // };
        $logger = new class extends AbstractLogger {
            public function log(): void
            {
            }
        };

        $this->makeService($logger)->updateActivatedMappings();
    }

    public function onCodeTypeInstalledEvent(CodeTypeInstalledEvent $event)
    {
        $codeType = $event->getCodeType();
        if ($codeType === "SNOMED") {
            // check if we have SNOMED codes installed and update our list options
            $this->makeService()->updateSNOMEDMappings($codeType);
        } elseif ($codeType === "CPT4") {
            // check if we have CPT4 codes installed and update our list options
            $this->makeService()->updateCPT4Mappings();
        }
    }

    private function makeService(?LoggerInterface $logger = null): CodeTypeMappingUpdater
    {
        $logger ??= ServiceContainer::getLogger();

        return new CodeTypeMappingUpdater(
        );
    }
}
