<?php

namespace PubNub\Endpoints\Objects;

use PubNub\Endpoints\Endpoint;

abstract class ObjectsCollectionEndpoint extends Endpoint
{
    /** @var array */
    protected $page = [];

    /** @var string */
    protected $filter;

    /** @var string */
    protected $limit;

    /** @var array */
    protected $sort;

    /**
     * @param array $page
     * @return $this
     */
    public function page($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param string $filter
     * @return $this
     */
    public function filter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param string $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param array $sort
     * @return $this
     */
    public function sort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

}