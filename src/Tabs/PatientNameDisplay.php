<?php

/**
 * How the patient bar shows the current patient's name: the patient_name_display setting.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tabs;

enum PatientNameDisplay: string
{
    case Button = 'btn';
    case Text = 'text';
    case LargeText = 'text-large';

    /**
     * An unknown setting shows the name as text, as before.
     */
    public static function fromSetting(string $setting): self
    {
        return self::tryFrom($setting) ?? self::Text;
    }

    /**
     * The element that holds the name and its buttons.
     */
    public function wrapperElement(): string
    {
        return match ($this) {
            self::LargeText => 'h3',
            self::Button, self::Text => 'div',
        };
    }

    public function wrapperClass(): string
    {
        return match ($this) {
            self::Button => 'btn-group btn-group-sm mb-2',
            self::Text, self::LargeText => 'd-inline',
        };
    }

    /**
     * Classes for the name; in the text styles the theme makes it bold.
     */
    public function nameClass(): string
    {
        return match ($this) {
            self::Button => 'btn btn-sm btn-secondary',
            self::Text, self::LargeText => 'patient-name-link',
        };
    }

    /**
     * Classes for the links next to the name (open elsewhere, close).
     */
    public function linkClass(): string
    {
        return match ($this) {
            self::Button => 'btn btn-sm btn-secondary',
            self::Text, self::LargeText => 'text-muted',
        };
    }

    /**
     * Classes for the icons next to the name.
     */
    public function iconClass(): string
    {
        return match ($this) {
            self::LargeText => 'text-muted fa-xs',
            self::Button, self::Text => 'text-muted',
        };
    }

    /**
     * The element around the links after the name (open elsewhere, close), which the theme
     * raises like a superscript; '' for none, as buttons stay in their button group.
     */
    public function actionsElement(): string
    {
        return match ($this) {
            self::Button => '',
            self::Text => 'span',
            self::LargeText => 'small',
        };
    }

    public function isButton(): bool
    {
        return $this === self::Button;
    }
}
