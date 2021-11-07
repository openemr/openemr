<?php

/**
 * TwigEnvironmentEvent is fired when the twig environment has been created in the system and allows module writers
 * to modify the environment (such as adding their own template folder locations)
 *
 * An example of this can be seen below:
 *
 * function addTemplateOverrideLoader(TwigEnvironmentEvent $event)
 * {
 *     $twig = $event->getTwigEnvironment();
 *
 *     // we make sure we can override our file system directory here.
 *     $loader = $twig->getLoader();
 *     if ($loader instanceof FilesystemLoader) {
 *          $loader->prependPath(\dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR);
 *     }
 * }
 * $GLOBALS['kernel']->getEventDispatcher()->addListener(TwigEnvironmentEvent::EVENT_CREATED, ['addTemplateOverrideLoader']);
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Core;

use Twig\Environment;

class TwigEnvironmentEvent
{
    /**
     * This event is triggered after the twig environment has been created in the TwigContainer
     */
    const EVENT_CREATED = 'core.twig.environment.create';

    /**
     * @var Environment
     */
    private $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getTwigEnvironment(): Environment
    {
        return $this->environment;
    }
}
