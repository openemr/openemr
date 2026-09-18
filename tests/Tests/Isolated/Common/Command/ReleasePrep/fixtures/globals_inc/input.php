<?php

return [
    'language_menu_other' => [
        xl('Allowed Languages'),
        'm_lang',
        '',
        xl('Select languages.'),
    ],

    'allow_debug_language' => [
        xl('Allow Debugging Language'),
        'bool',                           // data type
        '1',                              // default = true during development and false for production releases
        xl('This will allow selection of the debugging (\'dummy\') language.'),
    ],

    'translate_no_safe_apostrophe' => [
        xl('Do Not Use Safe Apostrophe'),
        'bool',
        '0',
        xl('Note.'),
    ],
];
