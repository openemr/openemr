<?php

namespace PubNub\Models\Consumer\Objects\Membership;

class PNMembershipsResult
{
    /** @var integer */
    protected $totalCount;

    /** @var string */
    protected $prev;

    /** @var string */
    protected $next;

    /** @var array */
    protected $data;

    /**
     * PNMembershipsResult constructor.
   * @param integer $totalCount
     * @param string $prev
     * @param string $next
     * @param array $data
     */
    function __construct($totalCount, $prev, $next, $data)
    {
        $this->totalCount = $totalCount;
        $this->prev = $prev;
        $this->next = $next;
        $this->data = $data;
    }

    /**
     * @return integer
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return string
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * @return string
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function __toString()
    {
        if (!empty($data))
        {
          $data_string = json_encode($data);
        }

        return sprintf("totalCount: %s, prev: %s, next: %s, data: %s",
            $this->totalCount, $this->prev, $this->next, $data_string);
    }

    /**
     * @param array $payload
     * @return PNMembershipsResult
     */
    public static function fromPayload(array $payload)
    {
        $totalCount = null;
        $prev = null;
        $next = null;
        $data = null;

        if (array_key_exists("totalCount", $payload))
        {
            $totalCount = $payload["totalCount"];
        }

        if (array_key_exists("prev", $payload))
        {
            $prev = $payload["prev"];
        }

        if (array_key_exists("next", $payload))
        {
            $next = $payload["next"];
        }

        if (array_key_exists("data", $payload))
        {
            $data = [];

            foreach($payload["data"] as $value)
            {
                array_push($data, PNMembershipsResultItem::fromPayload($value));
            }
        }

        return new PNMembershipsResult($totalCount, $prev, $next, $data);
    }
}