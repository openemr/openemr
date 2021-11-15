<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter;

trait AdapterAwareTrait
{
    /**
     * @var Adapter
     */
    protected $adapter = null;

    /**
     * Set db adapter
     *
     * @param Adapter $adapter
     * @return self Provides a fluent interface
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }
}
