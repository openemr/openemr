<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm;

use OpenEMR\Events\BoundFilter;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;

class QdmRequestSome implements QdmRequestInterface
{
    protected $pids = [];

    protected $pidString = "";

    protected $filter = null;

    /**
     * QdmQuery constructor.
     *
     * @param array $pids
     */
    public function __construct(array $pids)
    {
        $this->pids = $pids;

        if (is_array($pids)) {
            $this->pidString = implode(",", $pids);
        }

        $this->filter = new BoundFilter();
        $this->filter->setFilterClause("pid IN (?)");
        $this->filter->setBoundValues([$this->pidString]);
    }

    public function getPidString()
    {
        return $this->pidString;
    }

    /**
     * @return array
     */
    public function getPids(): array
    {
        return $this->pids;
    }

    /**
     * @param array $pids
     */
    public function setPids(array $pids): void
    {
        $this->pids = $pids;
    }


    public function getFilter()
    {
        return $this->filter;
    }
}
