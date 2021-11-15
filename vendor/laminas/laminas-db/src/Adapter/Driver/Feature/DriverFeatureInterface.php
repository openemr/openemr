<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Driver\Feature;

interface DriverFeatureInterface
{
    /**
     * Setup the default features for Pdo
     *
     * @return DriverFeatureInterface
     */
    public function setupDefaultFeatures();

    /**
     * Add feature
     *
     * @param string $name
     * @param mixed $feature
     * @return DriverFeatureInterface
     */
    public function addFeature($name, $feature);

    /**
     * Get feature
     *
     * @param $name
     * @return mixed|false
     */
    public function getFeature($name);
}
