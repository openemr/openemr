<?php

/**
 * Keeps a statement run from happening twice when the biller refreshes the page
 * or presses Back and resubmits: each listing's form carries a fresh run id,
 * and a run id is honored only once per session.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use OpenEMR\Common\Session\SessionUtil;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class StatementRunOnce
{
    public const FORM_FIELD = 'statement_run_id';
    private const SESSION_KEY = 'statement_runs_done';
    /** Run ids remembered; older ones drop off. */
    private const KEEP = 50;

    /**
     * @param \Closure(list<string>): void $remember saves the ids done (SessionUtil::setSession by default:
     *     the page's session may be closed for writing by the time a run ends)
     */
    public function __construct(private SessionInterface $session, private ?\Closure $remember = null)
    {
    }

    public static function newId(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * True the first time a run id is claimed; false when that run was already done.
     */
    public function claim(string $runId): bool
    {
        $done = $this->session->get(self::SESSION_KEY);
        $done = is_array($done) ? array_values(array_filter($done, is_string(...))) : [];
        if (in_array($runId, $done, true)) {
            return false;
        }
        $done = array_slice([...$done, $runId], -self::KEEP);
        ($this->remember ?? static function (array $ids): void {
            SessionUtil::setSession(self::SESSION_KEY, $ids);
        })($done);
        return true;
    }
}
