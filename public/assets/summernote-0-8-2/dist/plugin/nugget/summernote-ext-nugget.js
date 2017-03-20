/*
 summernote-nugget
 
 Allow users to insert custom nuggets into the WYSIWYG.
 
 Installation
 
 1) Copy the plugin
 
 You must copy the plugin/nugget folder into your local summernote plugin folder.
 
 2) Configure the plugin
 
 After that, to initialize the template plugin, you have to set these options :
 
 $('#summernote').summernote({
 toolbar: [
 ['insert', ['nugget']]
 ],
 nugget: {
 list: [ 
 '[[Condo.name]]',
 '[[Condo.title]]'
 ]
 },
 });
 
 *
 **/
(function (factory) {
    /* global define */
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(window.jQuery);
    }
}(function ($) {


    $.extend($.summernote.options, {
        nugget: {
            list: []
        }

    });
    $.extend(true, $.summernote, {
        // add localization texts
        lang: {
            'en-US': {
                nugget: {
                    Nugget: 'Nugget',
                    Insert_nugget: 'Insert Nugget'

                }
            },
            'en-GB': {
                nugget: {
                    Nugget: 'Nugget',
                    Insert_nugget: 'Insert Nugget'

                }
            },
            'pt-PT': {
                nugget: {
                    Nugget: 'Pepita',
                    Insert_nugget: 'Inserir pepita'

                }
            },
            'it-IT': {
                nugget: {
                    Nugget: 'Pepite',
                    Insert_nugget: 'Pepite Inserto'

                }
            }
        }
    });
    // Extends plugins for adding nuggets.
    //  - plugin is external module for customizing.
    $.extend($.summernote.plugins, {
        /**
         * @param {Object} context - context object has status of editor.
         */
        'nugget': function (context) {
            // ui has renders to build ui elements.
            //  - you can create a button with `ui.button`
            var ui = $.summernote.ui;
            var options = context.options.nugget;
            var context_options = context.options;
            var lang = context_options.langInfo;
            var defaultOptions = {
                label: lang.nugget.Nugget,
                tooltip: lang.nugget.Insert_nugget
            };

            // Assign default values if not supplied
            for (var propertyName in defaultOptions) {
                if (options.hasOwnProperty(propertyName) === false) {
                    options[propertyName] = defaultOptions[propertyName];
                }
            }

            // add hello button
            context.memo('button.nugget', function () {
                // create button

                var button = ui.buttonGroup([
                    ui.button({
                        className: 'dropdown-toggle',
                        contents: '<span class="nugget"> ' + options.label + '</span><span class="note-icon-caret"></span>',
                        tooltip: options.tooltip,
                        data: {
                            toggle: 'dropdown'
                        }
                    }),
                    ui.dropdown({
                        className: 'dropdown-nugget',
                        items: options.list,
                        click: function (event) {
                            event.preventDefault();

                            var $button = $(event.target);
                            var value = $button.data('value');
                            var node = document.createElement('span');
                            node.innerHTML = value;
                            context.invoke('editor.insertText', value);

                        }
                    })
                ]);

                // create jQuery object from button instance.
                return button.render();
            });
        }

    });

}));