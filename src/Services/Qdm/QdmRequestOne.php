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

class QdmRequestOne implements QdmRequestInterface
{
    protected $pid = '';

    protected $filter = null;

    /**
     * QdmQuery constructor.
     *
     * @param $pid
     */
    public function __construct($pid)
    {
        $this->pid = $pid;
        $this->filter = new BoundFilter();
        $this->filter->setFilterClause("pid = ?");
        $this->filter->setBoundValues([$this->pid]);
    }


    public function getFilter()
    {
        return $this->filter;
    }
}
