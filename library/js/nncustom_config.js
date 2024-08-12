CKEDITOR.editorConfig = function (config) {
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';
    config.toolbarCanCollapse = true;
    config.toolbar = 'NNToolbar';

    config.shiftEnterMode = CKEDITOR.ENTER_P;
    config.enterMode = CKEDITOR.ENTER_BR;
    //config.height = 600;

    config.toolbar_NNToolbar =
        [
            ['Source', 'Templates'],
            ['Cut', 'Copy', 'Paste', 'SpellChecker', '-', 'Scayt'],
            ['Undo', 'Redo', '-', 'Find', 'Replace'],
            ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak'],
            ['Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript'],
            ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote', 'SelectAll', 'RemoveFormat'],
            ['Styles', 'Format', 'Font', 'FontSize'],
            ['TextColor', 'BGColor']
        ];

    //config.toolbarStartupExpanded = false;
};
