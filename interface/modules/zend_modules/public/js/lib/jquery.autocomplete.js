/**
*  Ajax Autocomplete for jQuery, version 1.2
*  (c) 2012 Tomas Kirda
*
*  Ajax Autocomplete for jQuery is freely distributable under the terms of an MIT-style license.
*  For details, see the web site: http://www.devbridge.com/projects/autocomplete/jquery/
*
*  Last Review: 12/19/2012
*/

/*jslint  browser: true, white: true, plusplus: true, vars: true */
/*global window: true, document: true, clearInterval: true, setInterval: true, jQuery: true */

(function ($) {
    'use strict';

    var utils = (function () {
        return {

            extend: function (target, source) {
                return $.extend(target, source);
            },

            addEvent: function (element, eventType, handler) {
                if (element.addEventListener) {
                    element.addEventListener(eventType, handler, false);
                } else if (element.attachEvent) {
                    element.attachEvent('on' + eventType, handler);
                } else {
                    throw new Error('Browser doesn\'t support addEventListener or attachEvent');
                }
            },

            removeEvent: function (element, eventType, handler) {
                if (element.removeEventListener) {
                    element.removeEventListener(eventType, handler, false);
                } else if (element.detachEvent) {
                    element.detachEvent('on' + eventType, handler);
                }
            },

            createNode: function (html) {
                var div = document.createElement('div');
                div.innerHTML = html;
                return div.firstChild;
            }

        };
    }());

    function Autocomplete(el, options) {
        var noop = function () { },
        that = this,
            defaults = {
                serviceUrl: null,
                lookup: null,
                onSelect: null,
                width: 'auto',
                minChars: 1,
                maxHeight: 300,
                deferRequestBy: 0,
                params: {},
                formatResult: Autocomplete.formatResult,
                delimiter: null,
                zIndex: 9999,
                type: 'GET',
                noCache: false,
                onSearchStart: noop,
                onSearchComplete: noop
            };

        // Shared variables:
        that.element = el;
        that.el = $(el);
        that.suggestions = [];
        that.badQueries = [];
        that.selectedIndex = -1;
        that.currentValue = that.element.value;
        that.intervalId = 0;
        that.cachedResponse = [];
        that.onChangeInterval = null;
        that.onChange = null;
        that.ignoreValueChange = false;
        that.isLocal = false;
        that.suggestionsContainer = null;
        that.options = defaults;
        that.classes = {
            selected: 'autocomplete-selected',
            suggestion: 'autocomplete-suggestion'
        };

        // Initialize and set options:
        that.initialize();
        that.setOptions(options);
    }

    Autocomplete.utils = utils;

    $.Autocomplete = Autocomplete;

    Autocomplete.formatResult = function (suggestion, currentValue) {
        var reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g'),
            pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';

        return suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>');
    };

    Autocomplete.prototype = {

        killerFn: null,

        initialize: function () {
            var that = this,
                suggestionSelector = '.' + that.classes.suggestion;

            // Remove autocomplete attribute to prevent native suggestions:
            this.element.setAttribute('autocomplete', 'off');

            this.killerFn = function (e) {
                if ($(e.target).closest('.autocomplete').length === 0) {
                    that.killSuggestions();
                    that.disableKillerFn();
                }
            };

            // Determine suggestions width:
            if (!this.options.width || this.options.width === 'auto') {
                this.options.width = this.el.outerWidth();
            }

            this.suggestionsContainer = Autocomplete.utils.createNode('<div class="autocomplete-suggestions" style="position: absolute; display: none;"></div>');

            var container = $(this.suggestionsContainer);

            container.appendTo('body').width(this.options.width);

            // Listen for mouse over event on suggestions list:
            container.on('mouseover', suggestionSelector, function () {
                that.activate($(this).data('index'));
            });

            // Listen for click event on suggestions list:
            container.on('click', suggestionSelector, function () {
                that.select($(this).data('index'));
            });

            this.fixPosition();

            // Opera does not like keydown:
            if (window.opera) {
                this.el.on('keypress', function (e) { that.onKeyPress(e); });
            } else {
                this.el.on('keydown', function (e) { that.onKeyPress(e); });
            }

            this.el.on('keyup', function (e) { that.onKeyUp(e); });
            this.el.on('blur', function () { that.onBlur(); });
            this.el.on('focus', function () { that.fixPosition(); });
        },

        onBlur: function () {
            this.enableKillerFn();
        },

        setOptions: function (suppliedOptions) {
            var options = this.options;

            utils.extend(options, suppliedOptions);

            this.isLocal = $.isArray(options.lookup);

            if (this.isLocal) {
                options.lookup = this.verifySuggestionsFormat(options.lookup);
            }

            // Adjust height, width and z-index:
            $(this.suggestionsContainer).css({
                'max-height': options.maxHeight + 'px',
                'width': options.width + 'px',
                'z-index': options.zIndex
            });
        },

        clearCache: function () {
            this.cachedResponse = [];
            this.badQueries = [];
        },

        disable: function () {
            this.disabled = true;
        },

        enable: function () {
            this.disabled = false;
        },

        fixPosition: function () {
            var offset = this.el.offset();
            $(this.suggestionsContainer).css({
                top: (offset.top + this.el.outerHeight()) + 'px',
                left: offset.left + 'px'
            });
        },

        enableKillerFn: function () {
            var that = this;
            $(document).on('click', that.killerFn);
        },

        disableKillerFn: function () {
            var that = this;
            $(document).off('click', that.killerFn);
        },

        killSuggestions: function () {
            var that = this;
            that.stopKillSuggestions();
            that.intervalId = window.setInterval(function () {
                that.hide();
                that.stopKillSuggestions();
            }, 300);
        },

        stopKillSuggestions: function () {
            window.clearInterval(this.intervalId);
        },

        onKeyPress: function (e) {
            // If suggestions are hidden and user presses arrow down, display suggestions:
            if (!this.disabled && !this.visible && e.keyCode === 40 && this.currentValue) {
                this.suggest();
                return;
            }

            if (this.disabled || !this.visible) {
                return;
            }

            switch (e.keyCode) {
                case 27: //KEY_ESC:
                    this.el.val(this.currentValue);
                    this.hide();
                    break;
                case 9: //KEY_TAB:
                case 13: //KEY_RETURN:
                    if (this.selectedIndex === -1) {
                        this.hide();
                        return;
                    }
                    this.select(this.selectedIndex);
                    if (e.keyCode === 9) {
                        return;
                    }
                    break;
                case 38: //KEY_UP:
                    this.moveUp();
                    break;
                case 40: //KEY_DOWN:
                    this.moveDown();
                    break;
                default:
                    return;
            }

            // Cancel event if function did not return:
            e.stopImmediatePropagation();
            e.preventDefault();
        },

        onKeyUp: function (e) {
            if (this.disabled) {
                return;
            }

            switch (e.keyCode) {
                case 38: //KEY_UP:
                case 40: //KEY_DOWN:
                    return;
            }

            var that = this;

            clearInterval(that.onChangeInterval);

            if (that.currentValue !== that.el.val()) {
                if (that.options.deferRequestBy > 0) {
                    // Defer lookup in case when value changes very quickly:
                    that.onChangeInterval = setInterval(function () {
                        that.onValueChange();
                    }, that.options.deferRequestBy);
                } else {
                    that.onValueChange();
                }
            }
        },

        onValueChange: function () {
            clearInterval(this.onChangeInterval);
            this.currentValue = this.element.value;

            var q = this.getQuery(this.currentValue);
            this.selectedIndex = -1;

            if (this.ignoreValueChange) {
                this.ignoreValueChange = false;
                return;
            }

            if (q === '' || q.length < this.options.minChars) {
                this.hide();
            } else {
                this.getSuggestions(q);
            }
        },

        getQuery: function (value) {
            var delimiter = this.options.delimiter,
                parts;

            if (!delimiter) {
                return $.trim(value);
            }
            parts = value.split(delimiter);
            return $.trim(parts[parts.length - 1]);
        },

        getSuggestionsLocal: function (q) {
            q = q.toLowerCase();

            return {
                suggestions: $.grep(this.options.lookup, function (suggestion) {
                    return suggestion.value.toLowerCase().indexOf(q) !== -1;
                })
            };
        },

        getSuggestions: function (q) {
            var response,
                that = this,
                options = that.options;

            response = that.isLocal ? that.getSuggestionsLocal(q) : that.cachedResponse[q];

            if (response && $.isArray(response.suggestions)) {
                that.suggestions = response.suggestions;
                that.suggest();
            } else if (!that.isBadQuery(q)) {
                options.onSearchStart.call(that.element, q);
                options.params.query = q;
                $.ajax({
                    url: options.serviceUrl,
                    data: options.params,
                    type: options.type,
                    dataType: 'text'
                }).done(function (txt) {
                    that.processResponse(txt);
                    options.onSearchComplete.call(that.element, q);
                });
            }
        },

        isBadQuery: function (q) {
            var badQueries = this.badQueries,
                i = badQueries.length;

            while (i--) {
                if (q.indexOf(badQueries[i]) === 0) {
                    return true;
                }
            }

            return false;
        },

        hide: function () {
            this.visible = false;
            this.selectedIndex = -1;
            $(this.suggestionsContainer).hide();
        },

        suggest: function () {
            if (this.suggestions.length === 0) {
                this.hide();
                return;
            }

            var formatResult = this.options.formatResult,
                value = this.getQuery(this.currentValue),
                className = this.classes.suggestion,
                classSelected = this.classes.selected,
                container = $(this.suggestionsContainer),
                html = '';

            // Build suggestions inner HTML:
            $.each(this.suggestions, function (i, suggestion) {
                html += '<div class="' + className + '" data-index="' + i + '">' + formatResult(suggestion, value) + '</div>';
            });

            container.html(html).show();
            this.visible = true;

            // Select first value by default:
            this.selectedIndex = 0;
            container.children().first().addClass(classSelected);
        },

        verifySuggestionsFormat: function (suggestions) {
            // If suggestions is string array, convert them to supported format:
            if (suggestions.length && typeof suggestions[0] === 'string') {
                return $.map(suggestions, function (value) {
                    return { value: value, data: null };
                });
            }

            return suggestions;
        },

        processResponse: function (text) {
            var response = $.parseJSON(text);

            response.suggestions = this.verifySuggestionsFormat(response.suggestions);

            // Cache results if cache is not disabled:
            if (!this.options.noCache) {
                this.cachedResponse[response.query] = response;
                if (response.suggestions.length === 0) {
                    this.badQueries.push(response.query);
                }
            }

            // Display suggestions only if returned query matches current value:
            if (response.query === this.getQuery(this.currentValue)) {
                this.suggestions = response.suggestions;
                this.suggest();
            }
        },

        activate: function (index) {
            var activeItem,
                selected = this.classes.selected,
                container = $(this.suggestionsContainer),
                children = container.children();

            container.children('.' + selected).removeClass(selected);

            this.selectedIndex = index;

            if (this.selectedIndex !== -1 && children.length > this.selectedIndex) {
                activeItem = children.get(this.selectedIndex);
                $(activeItem).addClass(selected);
                return activeItem;
            }

            return null;
        },

        select: function (i) {
            var selectedValue = this.suggestions[i];

            if (selectedValue) {
                this.el.val(selectedValue);
                this.ignoreValueChange = true;
                this.hide();
                this.onSelect(i);
            }
        },

        moveUp: function () {
            if (this.selectedIndex === -1) {
                return;
            }

            if (this.selectedIndex === 0) {
                $(this.suggestionsContainer).children().first().removeClass(this.classes.selected);
                this.selectedIndex = -1;
                this.el.val(this.currentValue);
                return;
            }

            this.adjustScroll(this.selectedIndex - 1);
        },

        moveDown: function () {
            if (this.selectedIndex === (this.suggestions.length - 1)) {
                return;
            }

            this.adjustScroll(this.selectedIndex + 1);
        },

        adjustScroll: function (index) {
            var activeItem = this.activate(index),
                offsetTop,
                upperBound,
                lowerBound,
                heightDelta = 25;

            if (!activeItem) {
                return;
            }

            offsetTop = activeItem.offsetTop;
            upperBound = $(this.suggestionsContainer).scrollTop();
            lowerBound = upperBound + this.options.maxHeight - heightDelta;

            if (offsetTop < upperBound) {
                $(this.suggestionsContainer).scrollTop(offsetTop);
            } else if (offsetTop > lowerBound) {
                $(this.suggestionsContainer).scrollTop(offsetTop - this.options.maxHeight + heightDelta);
            }

            this.el.val(this.getValue(this.suggestions[index].value));
        },

        onSelect: function (index) {
            var that = this,
                onSelectCallback = that.options.onSelect,
                suggestion = that.suggestions[index];

            that.el.val(that.getValue(suggestion.value));

            if ($.isFunction(onSelectCallback)) {
                onSelectCallback.call(that.element, suggestion);
            }
        },

        getValue: function (value) {
            var that = this,
                delimiter = that.options.delimiter,
                currentValue,
                parts;

            if (!delimiter) {
                return value;
            }

            currentValue = that.currentValue;
            parts = currentValue.split(delimiter);

            if (parts.length === 1) {
                return value;
            }

            return currentValue.substr(0, currentValue.length - parts[parts.length - 1].length) + value;
        }
    };

    // Create chainable jQuery plugin:
    $.fn.autocomplete = function (options, args) {
        return this.each(function () {
            var dataKey = 'autocomplete',
                inputElement = $(this),
                instance;

            if (typeof options === 'string') {
                instance = inputElement.data(dataKey);
                if (typeof instance[options] === 'function') {
                    instance[options](args);
                }
            } else {
                instance = new Autocomplete(this, options);
                inputElement.data(dataKey, instance);
            }
        });
    };

}(jQuery));
