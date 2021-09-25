<?php

/**
 * TwigContainer class.
 *
 * OpenEMR central container interface for twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Twig;

use OpenEMR\Core\Kernel;
use OpenEMR\Common\Twig\TwigExtension;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class TwigContainer
{
    private $paths = [];  // path in /templates

    /**
     * Instance of Kernel
     */
    private $kernel = null;

    /**
     * Create a new Twig superclass holding a twig environment
     *
     * @var $path string Additional path to add to $fileroot/templates string
     * @var $kernel Kernel An instance of Kernel to test if the environment is dev vs prod
     */
    public function __construct(string $path = null, Kernel $kernel = null)
    {
        $this->paths[] = $GLOBALS['fileroot'] . '/templates';
        if (!empty($path)) {
            $this->paths[] = $path;
        }

        if ($kernel) {
            $this->kernel = $kernel;
        }
    }

    /**
     * Get the Twig Environment.
     *
     * @return Twig\Environment The twig environment
     */
    public function getTwig()
    {
        $twigLoader = new FilesystemLoader($this->paths);
        $twigEnv = new Environment($twigLoader, ['autoescape' => false]);
        $twigEnv->addExtension(new TwigExtension());

        if ($this->kernel && $this->kernel->isDev()) {
            $twigEnv->addExtension(new DebugExtension());
            $twigEnv->enableDebug();
        }

        return $twigEnv;
    }
}
