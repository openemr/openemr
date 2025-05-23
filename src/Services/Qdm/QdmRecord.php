<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm;

class QdmRecord
{
    protected $data = [];
    protected $pid;
    protected $entityCount ;

    /**
     * QdmRecord constructor.
     *
     * @param array $data
     * @param $pid
     */
    public function __construct(array $data, int $pid, int $entityCount)
    {
        $this->data = $data;
        $this->pid = $pid;
        $this->entityCount = $entityCount;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param mixed $pid
     */
    public function setPid($pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @return int
     */
    public function getEntityCount(): int
    {
        return $this->entityCount;
    }

    /**
     * @param int $entityCount
     */
    public function setEntityCount(int $entityCount): void
    {
        $this->entityCount = $entityCount;
    }
}
