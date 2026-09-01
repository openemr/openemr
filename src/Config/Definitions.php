<?php

declare(strict_types=1);

namespace OpenEMR\Config;

class Definitions
{

    /**
     * @return array<string, Definition<mixed>[]>
     */
    public static function getSystemSettingsByTabName(): array
    {
        // huge def'n here
        return [
            'Appearance' => [
                new Definition(
                    BoolValue::SimplifiedCopay,
                    false,
                    xld('Simplified Co-Pay'),
                    xld('Omit method of payment from the co-pay panel'),
                ),

            ],
            // ...
        ];
    }

    /**
     * @return Key<mixed>[]
     */
    public static function getKeysRequiringReauth(): array
    {
        return [
            // theme_tabs_layout
            // css_header
            // prevent_browser_refresh
            // login_page_layout
        ];
    }
}
