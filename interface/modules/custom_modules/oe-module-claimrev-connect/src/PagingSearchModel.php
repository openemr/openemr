<?php

/**
 * Paging search model for ClaimRev API requests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

class PagingSearchModel
{
    public int $pageIndex = 0;
    public int $pageSize = 50;
    public string $sortField = "";
    public string $sortDirection = "";
}
