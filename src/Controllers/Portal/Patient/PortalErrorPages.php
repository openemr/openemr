<?php

/**
 * The portal/patient error pages: not found, and the page for a failed request.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

use Symfony\Component\HttpFoundation\Response;

final readonly class PortalErrorPages
{
    public function __construct(private PortalPageRenderer $pages)
    {
    }

    /**
     * No route serves the path. Answered with 200, as it always was.
     */
    public function notFound(): Response
    {
        return new Response($this->pages->render('DefaultError404'));
    }

    /**
     * A request failed. The page shows a generic message; the failure is logged by the caller.
     */
    public function failed(string $message): Response
    {
        return new Response($this->pages->render('DefaultErrorFatal', [
            'message' => $message,
            'stacktrace' => '',
            'code' => 0,
        ]));
    }
}
