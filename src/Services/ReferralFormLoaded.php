<?php

/**
 * What the referral screen's form showed when it was loaded (#14710): its Reply Date and its Refer
 * To, as stored, posted back in two hidden fields. A save from that form that leaves no Reply Date
 * cleared the reply it showed, which was the reply of the recipient it showed, so
 * ReferralTaskService undoes the Task that recipient's reply completed, and no other.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use Symfony\Component\HttpFoundation\Request;

final readonly class ReferralFormLoaded
{
    /** The hidden field the form posts the Reply Date it loaded in. */
    public const REPLY_DATE_FIELD = 'referral_reply_date_loaded';

    /** The hidden field the form posts the Refer To it loaded in (the stored users.id). */
    public const REFER_TO_FIELD = 'referral_refer_to_loaded';

    /** The Reply Date's own field as the layout form posts it, with get_layout_form_value()'s form_ prefix. */
    private const POSTED_REPLY_DATE_FIELD = 'form_reply_date';

    /**
     * @param bool $showedReply whether the form showed a Reply Date
     * @param ?int $referTo     the Refer To the form showed (a users.id), or null when it showed none
     */
    public function __construct(public bool $showedReply, public ?int $referTo)
    {
    }

    /**
     * What the screen's form showed, from its hidden fields, when the form also posted the Reply
     * Date's own field, empty or not; else null. A layout that makes the Reply Date read-only
     * renders it disabled, so it isn't posted: the screen then deletes the stored date as a blank
     * though nobody cleared it, and null keeps that save from undoing the reply. A Refer To that
     * isn't a whole number (a blank, or anything else) is none.
     */
    public static function fromRequest(Request $request): ?self
    {
        $posted = $request->request;
        if (!$posted->has(self::POSTED_REPLY_DATE_FIELD)) {
            return null;
        }
        $referTo = trim($posted->getString(self::REFER_TO_FIELD));
        return new self(trim($posted->getString(self::REPLY_DATE_FIELD)) !== '', ctype_digit($referTo) ? (int)$referTo : null);
    }
}
