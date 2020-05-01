<?php

/**
 * CQM NQF 0384
 *
 * @package OpenEMR
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/
class NQF_0384 extends AbstractCqmReport
{
    public function createPopulationCriteria()
    {
         return new NQF_0384_PopulationCriteria();
    }
}
