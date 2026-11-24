<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * The session store could not be reached. The request cannot proceed, but the
 * client's session cookie remains valid and the request can be retried.
 */
class SessionStoreUnavailableException extends ServiceUnavailableHttpException
{
    private const RETRY_AFTER_SECONDS = 5;

    public function __construct()
    {
        parent::__construct(self::RETRY_AFTER_SECONDS, 'Session store unavailable.');
    }
}
