<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qdm;

use OpenEMR\Events\BoundFilter;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;

class QdmRequestOne implements QdmRequestInterface
{
    protected BoundFilter $filter;

    public function __construct(protected string $pid)
    {
        $this->filter = new BoundFilter();
        $this->filter->setFilterClause("pid = ?");
        $this->filter->setBoundValues([$this->pid]);
    }


    public function getFilter(): BoundFilter
    {
        return $this->filter;
    }
}
