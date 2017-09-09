<?php
/**
 * CQM NQF 0101
 *
 * @package OpenEMR
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class NFQ_0101 extends AbstractCqmReport
{
    public function createPopulationCriteria()
    {
         return new NFQ_0101_PopulationCriteria();
    }
}
