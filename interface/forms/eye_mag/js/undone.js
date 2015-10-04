/*!
 * undone.js 0.0.1 - https://github.com/yckart/undone.js
 * The undo/redo manager for well formed javascript applications.
 *
 * Inspired by: http://blog.asgaard.co.uk/2012/11/21/undo-redo-in-javascript
 *
 * Copyright (c) 2012 Yannick Albert (http://yckart.com)
 * Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php).
 * 2013/03/16
 **/
;(function(window){

    /**
      * @constructor
      */
    var Undone = function(options) {

        // define the defaults
        this.options = {
            buffer: 1000
        };

        // extend the defaults
        for(var i in options) this.options[i] = options[i];

        this.history = {
            undo: [],
            redo: []
        };
    };

    /**
      * Executes an action and adds it to the undo stack.
      * @param {Function} action - Action function.
      * @param {Function} reverse - Reverse function.
      * @param {Object} ctx - The 'this' argument for the action/reverse functions.
      */
    Undone.prototype.register = function(action, reverse, ctx) {
        if(this.history.undo.length >= this.options.buffer) this.history.undo.unshift();
        this.history.undo.push( {action: action, reverse: reverse, ctx: ctx} );
        action.call(ctx);
        this.history.redo.length = 0;
        this.change("register", this.history.undo.length, this.history.redo.length);
    };

    Undone.prototype.undo = function(n) {
        var len = this.history.undo.length;

        n = n || 1;
        if(n > len) n = len;
        if(len < this.options.buffer) {
            while(n--) {
                var c = this.history.undo.pop();
                if (!c) return;

                c.reverse.call(c.ctx);
                this.history.redo.push(c);
            }
            if( len !== this.history.undo.length ) this.change("undo", this.history.undo.length, this.history.redo.length);
        }
    };

    Undone.prototype.redo = function(n) {

        var len = this.history.redo.length;

        n = n || 1;
        if(n > len) n = len;
        if(len < this.options.buffer) {
            while(n--) {
                var c = this.history.redo.pop();
                if (!c) return;
                c.action.call(c.ctx);
                this.history.undo.push(c);
            }
            if( len !== this.history.redo.length ) this.change("redo", this.history.undo.length, this.history.redo.length);
        }
    };

    Undone.prototype.onChange = function(){};
    Undone.prototype.change = function(event, undoLen, redoLen){
        if (this.onChange) this.onChange(event, undoLen, redoLen);
        if(window.jQuery) $(window).trigger("undone:change", [event, undoLen, redoLen]);
    };

    Undone.prototype.clear = function(){
        this.history = {
            undo: [],
            redo: []
        };
        this.change("clear", 0, 0);
    };

    window.Undone = Undone;
}(window));