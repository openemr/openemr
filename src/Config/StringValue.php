<?php

declare(strict_types=1);

namespace OpenEMR\Config;

/**
 * @implements Key<string>
 */
enum StringValue: string implements Key
{
    case OpenemrName = 'openemr_name';
    case MainMenuLogoLink = 'main_menu_logo_link';

    public static function cast(string $value): string
    {
        return $value;
    }

    public function uiControlType(): UiType
    {
        return UiType::String;
    }
}
