<?php

/**
 * OpenEMR <https://open-emr.org>.
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Core OpenEMR kernel.
 *
 * Holds the service container, event dispatcher, and infrastructure paths.
 * Path accessors follow Symfony's convention (getProjectDir(), etc.).
 *
 * @package   OpenEMR
 * @subpackage Core
 * @author    Robert Down <robertdown@live.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017-2022 Robert Down
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */
class Kernel
{
    /** @var ContainerBuilder */
    private $container;

    /**
     * @param ?string $projectDir Absolute filesystem root (e.g. "/var/www/openemr").
     *                            Optional for backward compat with modules that construct
     *                            a Kernel without paths; path getters throw if null.
     * @param ?string $webRoot    URL path prefix (e.g. "/openemr" or "").
     *                            Optional for the same reason as $projectDir.
     * @param ?EventDispatcherInterface $dispatcher Pre-built event dispatcher to inject.
     */
    public function __construct(
        private readonly ?string $projectDir = null,
        private readonly ?string $webRoot = null,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
        $this->prepareContainer();
    }

    /**
     * Setup the initial container
     */
    private function prepareContainer()
    {
        if (!$this->container) {
            $builder = new ContainerBuilder(new ParameterBag());
            $builder->addCompilerPass(new RegisterListenersPass());
            $definition = new Definition(EventDispatcher::class, [new Reference('service_container')]);
            if (!empty($this->dispatcher)) {
                $definition->setSynthetic(true);
            }
            $definition->setPublic(true);
            $builder->setDefinition('event_dispatcher', $definition);
            $builder->compile();
            $this->container = $builder;
            if (!empty($this->dispatcher)) {
                $this->container->set('event_dispatcher', $this->dispatcher);
            }
        }
    }

    // ---- Path accessors (web-relative) ------------------------------------

    /**
     * URL path prefix, e.g. "/openemr" or ""
     */
    public function getWebRoot(): string
    {
        return $this->requireWebRoot();
    }

    /**
     * Web root + "/interface"
     */
    public function getRootDir(): string
    {
        return $this->requireWebRoot() . '/interface';
    }

    /**
     * Web root + "/public/assets"
     */
    public function getAssetsRelative(): string
    {
        return $this->requireWebRoot() . '/public/assets';
    }

    /**
     * Web root + "/public/themes"
     */
    public function getThemesRelative(): string
    {
        return $this->requireWebRoot() . '/public/themes';
    }

    /**
     * Web root + "/public/images"
     */
    public function getImagesRelative(): string
    {
        return $this->requireWebRoot() . '/public/images';
    }

    // ---- Path accessors (filesystem-absolute) -----------------------------

    /**
     * Absolute filesystem root, e.g. "/var/www/openemr"
     */
    public function getProjectDir(): string
    {
        return $this->requireProjectDir();
    }

    /**
     * Project dir + "/library"
     */
    public function getSrcDir(): string
    {
        return $this->requireProjectDir() . '/library';
    }

    /**
     * Project dir + "/interface"
     */
    public function getIncludeRoot(): string
    {
        return $this->requireProjectDir() . '/interface';
    }

    /**
     * Project dir + "/vendor"
     */
    public function getVendorDir(): string
    {
        return $this->requireProjectDir() . '/vendor';
    }

    /**
     * Project dir + "/templates/"
     */
    public function getTemplateDir(): string
    {
        return $this->requireProjectDir() . '/templates/';
    }

    /**
     * Project dir + "/public/images"
     */
    public function getImagesAbsolute(): string
    {
        return $this->requireProjectDir() . '/public/images';
    }

    /**
     * Project dir + "/sites"
     */
    public function getSitesBase(): string
    {
        return $this->requireProjectDir() . '/sites';
    }

    // ---- Site-specific path accessors -------------------------------------

    /**
     * Absolute path to a site directory: sitesBase + "/$siteId"
     */
    public function getSiteDir(string $siteId): string
    {
        return $this->getSitesBase() . '/' . $siteId;
    }

    /**
     * Web path to a site directory: webRoot + "/sites/$siteId"
     */
    public function getSiteWebRoot(string $siteId): string
    {
        return $this->requireWebRoot() . '/sites/' . $siteId;
    }

    // ---- Existing API -----------------------------------------------------

    /**
     * Return true if the environment variable OPENEMR__ENVIRONMENT is set to dev.
     *
     * @return bool
     */
    public function isDev()
    {
        return (($_ENV['OPENEMR__ENVIRONMENT'] ?? '') === 'dev') ? true : false;
    }

    /**
     * Get the Service Container
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if (!$this->container) {
            $this->prepareContainer();
        }

        return $this->container;
    }

    /**
     * Get the Event Dispatcher
     *
     * @return EventDispatcherInterface
     * @throws \Exception
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        if ($this->container) {
            /** @var EventDispatcherInterface $dispatcher */
            return $this->container->get('event_dispatcher');
        } else {
            throw new \Exception('Container does not exist');
        }
    }

    // ---- Internal helpers -------------------------------------------------

    private function requireProjectDir(): string
    {
        if ($this->projectDir === null) {
            throw new \RuntimeException('Kernel was constructed without a projectDir. Pass $projectDir to the Kernel constructor.');
        }
        return $this->projectDir;
    }

    private function requireWebRoot(): string
    {
        if ($this->webRoot === null) {
            throw new \RuntimeException('Kernel was constructed without a webRoot. Pass $webRoot to the Kernel constructor.');
        }
        return $this->webRoot;
    }
}
