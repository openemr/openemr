<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Twig;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Core\TwigEnvironmentEvent;
use OpenEMR\Services\Utils\DateFormatterUtils;
use Twig\Environment;
use Twig\Extension\CoreExtension;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

/**
 * Usage:
 *   TwigFactory::createInstance()->render('template.html.twig', ['var' => 'value]); // Default path
 *   TwigFactory::createInstance(__DIR__)->render('template.html.twig', ['var' => 'value]); // __DIR__ as additional path
 *   TwigFactory::createInstance([$path1, $path2])->render('template.html.twig', ['var' => 'value]); // Multiple additional paths
 */
class TwigFactory
{
    public static function createInstance(string|array $paths = []): Environment
    {
        $globals = OEGlobalsBag::getInstance();
        $kernel  = $globals->get('kernel');
        $paths = array_values(array_unique(array_merge(is_string($paths) ? [$paths] : $paths, [
            sprintf('%s/templates', $globals->get('fileroot')),
        ])));

        $twigLoader = new FilesystemLoader($paths);
        $twigEnv = new Environment($twigLoader, ['autoescape' => false]);

        $twigEnv->addExtension(new TwigExtension(
            OEGlobalsBag::getInstance(),
            $kernel,
        ));

        $coreExtension = $twigEnv->getExtension(CoreExtension::class);
        // set our default date() twig render function if no format is specified
        // we set our default date format to be the localized version of our dates and our time formats
        // by default Twig uses 'F j, Y H:i' for the format which doesn't match our OpenEMR dates as configured from the globals
        $dateFormat = DateFormatterUtils::getShortDateFormat() . " " . DateFormatterUtils::getTimeFormat();
        $coreExtension->setDateFormat($dateFormat);

        if ($kernel) {
            if ($kernel->isDev()) {
                $twigEnv->addExtension(new DebugExtension());
                $twigEnv->enableDebug();
            }
            $event = new TwigEnvironmentEvent($twigEnv);
            $kernel->getEventDispatcher()->dispatch($event, TwigEnvironmentEvent::EVENT_CREATED);
        }

        return $twigEnv;
    }
}
