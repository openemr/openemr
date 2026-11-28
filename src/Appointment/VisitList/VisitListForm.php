<?php

/**
 * VisitListForm
 *
 * What the appointment editor shows for a Visit List patient: whether it is one, and its
 * Order # and Stop # as the form fields hold them ('' when not set).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

final readonly class VisitListForm
{
    /**
     * @param array{categoryId: int, messages: array<string, string>, deleteText: string}|array{} $blockWarning
     *        for an existing Visit List block with patients: what to ask before it is deleted or
     *        given another category, by repeat scope (current, future, all; a scope with no
     *        patients from today on is left out), for the editor's script; else empty
     */
    public function __construct(
        public bool $active,
        public string $sequence = '',
        public string $stop = '',
        public array $blockWarning = [],
    ) {
    }

    public static function inactive(): self
    {
        return new self(false);
    }

    /**
     * The class that hides the editor's time, duration and repeat fields, which a Visit List
     * patient does not have.
     */
    public function hiddenClass(): string
    {
        return $this->active ? ' d-none' : '';
    }
}
