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

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Events\Codes\CodeTypeInstalledEvent;
use OpenEMR\Services\Utils\Interfaces\ISQLUpgradeService;
use OpenEMR\Events\Core\SQLUpgradeEvent;
use OpenEMR\Services\CodeTypes\CodeTypeMappingUpdater;
use Psr\Log\{
    AbstractLogger,
    LoggerInterface,
};
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
        error_log(__METHOD__ . __LINE__);
        $upgradeService = $event->getSqlUpgradeService();
        $logger = new class ($upgradeService) extends AbstractLogger {
            public function __construct(private readonly ISQLUpgradeService $upgradeService)
            {
        error_log(__METHOD__ . __LINE__);
            }

            public function log($level, \Stringable|string $message, array $context = []): void
            {
        error_log(__METHOD__ . __LINE__);
                $this->upgradeService->flush_echo(text($message) . "<br />");
            }
        };

        error_log(__METHOD__ . __LINE__);
        $this->makeService($logger)->updateActivatedMappings();
    }

    public function onCodeTypeInstalledEvent(CodeTypeInstalledEvent $event)
    {
        $codeType = $event->getCodeType();
        $service = $this->makeService();
        if ($codeType === "SNOMED") {
            $service->updateSNOMEDMappings();
        } elseif ($codeType === "CPT4" && $service->shouldUpdateCPT4Mappings()) {
            $service->updateCPT4Mappings();
        }
    }

    private function makeService(?LoggerInterface $logger = null): CodeTypeMappingUpdater
    {
        error_log(__METHOD__ . __LINE__);
        $logger ??= ServiceContainer::getLogger();
        error_log(__METHOD__ . __LINE__);
        $em = ServiceContainer::getEntityManager();

        error_log(__METHOD__ . __LINE__);
        return new CodeTypeMappingUpdater($em, $logger);
    }
}
