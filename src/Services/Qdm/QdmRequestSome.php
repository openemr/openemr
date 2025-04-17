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
    protected $filter = null;

    /**
     * QdmQuery constructor.
     *
     * @param array $pids
     */
    public function __construct(array $pids)
    {
        $filterClause = str_repeat("?,", count($pids));
        $filterClause = rtrim($filterClause, ",");
        $this->filter = new BoundFilter();
        $this->filter->setFilterClause("pid IN ($filterClause)");
        $this->filter->setBoundValues($pids);
    }

    /**
     * @return BoundFilter|null
     *
     * Use the PIDs to create a BoundFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
