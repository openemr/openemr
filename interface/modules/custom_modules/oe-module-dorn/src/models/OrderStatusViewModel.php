<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
<<<<<<< HEAD
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
=======
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
>>>>>>> d11e3347b (modules setup and UI changes)
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\Dorn\models;

use OpenEMR\Modules\Dorn\models\ApiResponseViewModel;

class OrderStatusViewModel extends ApiResponseViewModel
{
    public string $labName = '';
    public string $labGuid;
    public string $primaryId;
    public string|null $orderGuid;
    public string|null $orderNumber;
    public int|null $dornOrderStatusId;

    public string $orderStatusShortKeyCode = '';
    public string $orderStatusLong = '';
    public string $orderStatusDescrption = '';
    public bool $isPending = false;

    public DateTime|null $createdDateTimeUtc;
    public function __construct()
    {
    }
}
