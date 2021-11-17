<?php


namespace OpenEMR\Services\Qdm;


class QdmRecord
{
    protected $data = [];
    protected $pid;

    /**
     * QdmRecord constructor.
     * @param array $data
     * @param $pid
     */
    public function __construct(array $data, int $pid)
    {
        $this->data = $data;
        $this->pid = $pid;
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


}
