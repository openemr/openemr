/*!
 * jquery.undone.js 0.0.1 - https://github.com/yckart/jquery.undone.js
 * The undo/redo manager for well formed javascript applications.
 *
 * Inspired by: http://blog.asgaard.co.uk/2012/11/21/undo-redo-in-javascript
 *
 * Copyright (c) 2012 Yannick Albert (http://yckart.com)
 * Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php).
 * 2013/03/16
 **/
;(function($){
    var pluginName = "undone";
    $[pluginName] = $.fn[pluginName] = function (options) {
        var args = arguments,
            returns;

        if (!(this instanceof $)) return $.fn[pluginName].apply($(window), arguments);

        this.each(function() {
            var instance = $.data(this, 'plugin_' + pluginName);
            if (typeof options === 'string' && options[0] !== '_') {
                if (instance instanceof Undone && typeof instance[options] === 'function') {
                    returns = instance[options].apply(instance, Array.prototype.slice.call(args, 1));
                }
            } else if(!instance) {
                $.data(this, 'plugin_' + pluginName, new Undone(options));
            }
        });
        return returns === undefined ? this : returns;
    };
}(jQuery));