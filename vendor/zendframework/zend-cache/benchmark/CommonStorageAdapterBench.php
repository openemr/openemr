<?php

namespace ZendBench\Cache;

use Zend\Cache\Storage\StorageAdapterInterface;

/**
 * @BeforeMethods({"setUp"})
 * @AfterMethods({"tearDown"})
 * @BeforeClassMethods({"setUpClass"})
 * @AfterClassMethods({"tearDownClass"})
 */
abstract class CommonStorageAdapterBench
{
    /**
     * @var StorageAdapterInterface
     */
    protected $storage;

    /**
     * Key-Value-Pairs of existing items
     */
    protected $warmItems = [];

    /**
     * Key-Value-Pairs of missing items
     */
    protected $coldItems = [];

    public function __construct()
    {
        // generate warm items
        for ($i = 0; $i < 10; $i++) {
            $this->warmItems['warm' . $i] = $i;
        }

        // generate cold items
        for ($i = 0; $i < 10; $i++) {
            $this->coldItems['cold' . $i] = $i;
        }
    }

    public function setUp()
    {
        $this->storage->setItems($this->warmItems);
    }

    public function tearDown()
    {
        $this->storage->removeItems(array_keys($this->coldItems));
    }

    public static function setUpClass()
    {
    }

    public static function tearDownClass()
    {
    }

    /**
     * Has missing items with single operations
     */
    public function benchHasMissingItemsSingle()
    {
        foreach ($this->coldItems as $k => $v) {
            $this->storage->hasItem($k);
        }
    }

    /**
     * Has missing items at once
     */
    public function benchHasMissingItemsBulk()
    {
        $this->storage->hasItems(array_keys($this->coldItems));
    }

    /**
     * Has existing items with single operations
     */
    public function benchHasExistingItemsSingle()
    {
        foreach ($this->warmItems as $k => $v) {
            $this->storage->hasItem($k);
        }
    }

    /**
     * Has existing items at once
     */
    public function benchHasExistingItemsBulk()
    {
        $this->storage->hasItems(array_keys($this->warmItems));
    }

    /**
     * Set existing items with single operations
     */
    public function benchSetExistingItemsSingle()
    {
        foreach ($this->warmItems as $k => $v) {
            $this->storage->setItem($k, $v);
        }
    }

    /**
     * Set existingn items at once
     */
    public function benchSetExistingItemsBulk()
    {
        $this->storage->setItems($this->warmItems);
    }

    /**
     * Set missing items with single operations
     */
    public function benchSetMissingItemsSingle()
    {
        foreach ($this->coldItems as $k => $v) {
            $this->storage->setItem($k, $k . $v);
        }
    }

    /**
     * Set missing items at once
     */
    public function benchSetMissingItemsBulk()
    {
        $this->storage->setItems($this->coldItems);
    }

    /**
     * Add items with single operations
     */
    public function benchAddItemsSingle()
    {
        foreach ($this->coldItems as $k => $v) {
            $this->storage->addItem($k, $k . $v);
        }
    }

    /**
     * Add items at once
     */
    public function benchAddItemsBulk()
    {
        $this->storage->addItems($this->coldItems);
    }

    /**
     * Replace items with single operations
     */
    public function benchReplaceItemsSingle()
    {
        foreach ($this->warmItems as $k => $v) {
            $this->storage->replaceItem($k, $k . $v);
        }
    }

    /**
     * Replace items at once
     */
    public function benchReplaceItemsBulk()
    {
        $this->storage->replaceItems($this->coldItems);
    }

    /**
     * Get, check and set items with single operations
     */
    public function benchGetCheckAndSetItemsSingle()
    {
        foreach ($this->warmItems as $k => $v) {
            $this->storage->getItem($k, $success, $token);
            $this->storage->checkAndSetItem($token, $k, $k . $v);
        }
    }

    /**
     * Touch missing items with single operations
     */
    public function benchTouchMissingItemsSingle()
    {
        foreach ($this->coldItems as $k => $v) {
            $this->storage->touchItem($k);
        }
    }

    /**
     * Touch missing items at once
     */
    public function benchTouchMissingItemsBulk()
    {
        $this->storage->touchItems(array_keys($this->coldItems));
    }

    /**
     * Touch existing items with single operations
     */
    public function benchTouchExistingItemsSingle()
    {
        foreach ($this->warmItems as $k => $v) {
            $this->storage->touchItem($k);
        }
    }

    /**
     * Touch existing items at once
     */
    public function benchTouchExistingItemsBulk()
    {
        $this->storage->touchItems(array_keys($this->warmItems));
    }

    /**
     * Get missing items with single operations
     */
    public function benchGetMissingItemsSingle()
    {
        foreach ($this->coldItems as $k => $v) {
            $this->storage->getItem($k);
        }
    }

    /**
     * Get missing items at once
     */
    public function benchGetMissingItemsBulk()
    {
        $this->storage->getItems(array_keys($this->coldItems));
    }

    /**
     * Get existing items with single operations
     */
    public function benchGetExistingItemsSingle()
    {
        foreach ($this->warmItems as $k => $v) {
            $this->storage->getItem($k);
        }
    }

    /**
     * Get existing items at once
     */
    public function benchGetExistingItemsBulk()
    {
        $this->storage->getItems(array_keys($this->warmItems));
    }

    /**
     * Remove missing items with single operations
     */
    public function benchRemoveMissingItemsSingle()
    {
        foreach ($this->coldItems as $k => $v) {
            $this->storage->removeItem($k);
        }
    }

    /**
     * Remove missing items at once
     */
    public function benchRemoveMissingItemsBulk()
    {
        $this->storage->removeItems(array_keys($this->coldItems));
    }

    /**
     * Remove exisint items with single operations
     */
    public function benchRemoveExistingItemsSingle()
    {
        foreach ($this->warmItems as $k => $v) {
            $this->storage->removeItem($k);
        }
    }

    /**
     * Remove existing items at once
     */
    public function benchRemoveExistingItemsBulk()
    {
        $this->storage->removeItems(array_keys($this->warmItems));
    }

    /**
     * Increment missing items with single operations
     */
    public function benchIncrementMissingItemsSingle()
    {
        foreach ($this->coldItems as $k => $v) {
            $this->storage->incrementItem($k, $v);
        }
    }

    /**
     * Increment missing items at once
     */
    public function benchIncrementMissingItemsBulk()
    {
        $this->storage->incrementItems($this->coldItems);
    }

    /**
     * Increment exisint items with single operations
     */
    public function benchIncrementExistingItemsSingle()
    {
        foreach ($this->warmItems as $k => $v) {
            $this->storage->incrementItem($k, $v);
        }
    }

    /**
     * Increment existing items at once
     */
    public function benchIncrementExistingItemsBulk()
    {
        $this->storage->incrementItems($this->warmItems);
    }

    /**
     * Decrement missing items with single operations
     */
    public function benchDecrementMissingItemsSingle()
    {
        foreach ($this->coldItems as $k => $v) {
            $this->storage->decrementItem($k, $v);
        }
    }

    /**
     * Decrement missing items at once
     */
    public function benchDecrementMissingItemsBulk()
    {
        $this->storage->decrementItems($this->coldItems);
    }

    /**
     * Decrement exisint items with single operations
     */
    public function benchDecrementExistingItemsSingle()
    {
        foreach ($this->warmItems as $k => $v) {
            $this->storage->decrementItem($k, $v);
        }
    }

    /**
     * Decrement existing items at once
     */
    public function benchDecrementExistingItemsBulk()
    {
        $this->storage->decrementItems($this->warmItems);
    }
}
