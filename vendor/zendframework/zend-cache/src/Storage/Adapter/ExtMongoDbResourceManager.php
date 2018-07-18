<?php
/**
 * @see       https://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-cache/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Exception\Exception as MongoDriverException;
use Zend\Cache\Exception;

/**
 * Resource manager for the ext-mongodb adapter.
 *
 * If you are using ext-mongo, use the MongoDbResourceManager instead.
 */
class ExtMongoDbResourceManager
{
    /**
     * Registered resources
     *
     * @var array[]
     */
    private $resources = [];

    /**
     * Check if a resource exists
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasResource($id)
    {
        return isset($this->resources[$id]);
    }

    /**
     * Set a resource
     *
     * @param string $id
     * @param array|Collection $resource
     * @return self Provides a fluent interface
     * @throws Exception\RuntimeException
     */
    public function setResource($id, $resource)
    {
        if ($resource instanceof Collection) {
            $this->resources[$id] = [
                'db'                  => (string) $resource->db,
                'db_instance'         => $resource->db,
                'collection'          => (string) $resource,
                'collection_instance' => $resource,
            ];
            return $this;
        }

        if (! is_array($resource)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or %s; received %s',
                __METHOD__,
                Collection::class,
                is_object($resource) ? get_class($resource) : gettype($resource)
            ));
        }

        $this->resources[$id] = $resource;
        return $this;
    }

    /**
     * Instantiate and return the Collection resource
     *
     * @param string $id
     * @return Collection
     * @throws Exception\RuntimeException
     */
    public function getResource($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = $this->resources[$id];
        if (! isset($resource['collection_instance'])) {
            try {
                if (! isset($resource['db_instance'])) {
                    if (! isset($resource['client_instance'])) {
                        $resource['client_instance'] = new Client(
                            isset($resource['server']) ? $resource['server'] : null,
                            isset($resource['connection_options']) ? $resource['connection_options'] : [],
                            isset($resource['driver_options']) ? $resource['driver_options'] : []
                        );
                    }
                }

                $collection = $resource['client_instance']->selectCollection(
                    isset($resouce['db']) ? $resource['db'] : 'zend',
                    isset($resource['collection']) ? $resource['collection'] : 'cache'
                );
                $collection->createIndex(['key' => 1]);

                $this->resources[$id]['collection_instance'] = $collection;
            } catch (MongoDriverException $e) {
                throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $this->resources[$id]['collection_instance'];
    }

    /**
     * @param string $id
     * @param string $server
     * @return void
     */
    public function setServer($id, $server)
    {
        $this->resources[$id]['server'] = (string) $server;

        unset($this->resources[$id]['client_instance']);
        unset($this->resources[$id]['db_instance']);
        unset($this->resources[$id]['collection_instance']);
    }

    /**
     * @param string $id
     * @return null|string
     * @throws Exception\RuntimeException if no matching resource discovered
     */
    public function getServer($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        return isset($this->resources[$id]['server']) ? $this->resources[$id]['server'] : null;
    }

    /**
     * @param string $id
     * @param array $connectionOptions
     * @return void
     */
    public function setConnectionOptions($id, array $connectionOptions)
    {
        $this->resources[$id]['connection_options'] = $connectionOptions;

        unset($this->resources[$id]['client_instance']);
        unset($this->resources[$id]['db_instance']);
        unset($this->resources[$id]['collection_instance']);
    }

    /**
     * @param string $id
     * @return array
     * @throws Exception\RuntimeException if no matching resource discovered
     */
    public function getConnectionOptions($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        return isset($this->resources[$id]['connection_options'])
            ? $this->resources[$id]['connection_options']
            : [];
    }

    /**
     * @param string $id
     * @param array $driverOptions
     * @return void
     */
    public function setDriverOptions($id, array $driverOptions)
    {
        $this->resources[$id]['driver_options'] = $driverOptions;

        unset($this->resources[$id]['client_instance']);
        unset($this->resources[$id]['db_instance']);
        unset($this->resources[$id]['collection_instance']);
    }

    /**
     * @param string $id
     * @return array
     * @throws Exception\RuntimeException if no matching resource discovered
     */
    public function getDriverOptions($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        return isset($this->resources[$id]['driver_options']) ? $this->resources[$id]['driver_options'] : [];
    }

    /**
     * @param string $id
     * @param string $database
     * @return void
     */
    public function setDatabase($id, $database)
    {
        $this->resources[$id]['db'] = (string) $database;

        unset($this->resources[$id]['db_instance']);
        unset($this->resources[$id]['collection_instance']);
    }

    /**
     * @param string $id
     * @return string
     * @throws Exception\RuntimeException if no matching resource discovered
     */
    public function getDatabase($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        return isset($this->resources[$id]['db']) ? $this->resources[$id]['db'] : '';
    }

    /**
     * @param string $id
     * @param string $collection
     * @return void
     */
    public function setCollection($id, $collection)
    {
        $this->resources[$id]['collection'] = (string) $collection;

        unset($this->resources[$id]['collection_instance']);
    }

    /**
     * @param string $id
     * @return string
     * @throws Exception\RuntimeException if no matching resource discovered
     */
    public function getCollection($id)
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        return isset($this->resources[$id]['collection']) ? $this->resources[$id]['collection'] : '';
    }
}
