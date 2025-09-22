/**
 * jQuery mock for testing
 * Provides a mock implementation of jQuery functionality used in portal messaging
 */

const createJQueryMock = () => {
    const jQueryObject = {
        // Core jQuery methods
        ready: jest.fn(callback => {
            if (typeof callback === 'function') {
                setTimeout(callback, 0);
            }
            return jQueryObject;
        }),

        // DOM manipulation
        append: jest.fn(() => jQueryObject),
        prepend: jest.fn(() => jQueryObject),
        html: jest.fn(content => {
            if (content === undefined) return 'mocked-html';
            return jQueryObject;
        }),
        text: jest.fn(content => {
            if (content === undefined) return 'mocked-text';
            return jQueryObject;
        }),
        val: jest.fn(value => {
            if (value === undefined) return 'mocked-value';
            return jQueryObject;
        }),
        attr: jest.fn((attr, value) => {
            if (value === undefined) return 'mocked-attr';
            return jQueryObject;
        }),
        prop: jest.fn((prop, value) => {
            if (value === undefined) return true;
            return jQueryObject;
        }),
        data: jest.fn((key, value) => {
            if (value === undefined) return 'mocked-data';
            return jQueryObject;
        }),
        addClass: jest.fn(() => jQueryObject),
        removeClass: jest.fn(() => jQueryObject),
        toggleClass: jest.fn(() => jQueryObject),
        hasClass: jest.fn(() => true),

        // Traversal
        find: jest.fn(() => jQueryObject),
        parent: jest.fn(() => jQueryObject),
        parents: jest.fn(() => jQueryObject),
        children: jest.fn(() => jQueryObject),
        siblings: jest.fn(() => jQueryObject),
        closest: jest.fn(() => jQueryObject),
        first: jest.fn(() => jQueryObject),
        last: jest.fn(() => jQueryObject),
        eq: jest.fn(() => jQueryObject),
        filter: jest.fn(() => jQueryObject),
        not: jest.fn(() => jQueryObject),

        // Events
        on: jest.fn(() => jQueryObject),
        off: jest.fn(() => jQueryObject),
        click: jest.fn(() => jQueryObject),
        submit: jest.fn(() => jQueryObject),
        change: jest.fn(() => jQueryObject),
        focus: jest.fn(() => jQueryObject),
        blur: jest.fn(() => jQueryObject),
        hover: jest.fn(() => jQueryObject),
        trigger: jest.fn(() => jQueryObject),

        // Effects
        show: jest.fn(() => jQueryObject),
        hide: jest.fn(() => jQueryObject),
        toggle: jest.fn(() => jQueryObject),
        fadeIn: jest.fn(() => jQueryObject),
        fadeOut: jest.fn(() => jQueryObject),
        slideUp: jest.fn(() => jQueryObject),
        slideDown: jest.fn(() => jQueryObject),

        // CSS
        css: jest.fn((property, value) => {
            if (value === undefined) return 'mocked-css-value';
            return jQueryObject;
        }),
        height: jest.fn(value => {
            if (value === undefined) return 100;
            return jQueryObject;
        }),
        width: jest.fn(value => {
            if (value === undefined) return 200;
            return jQueryObject;
        }),
        offset: jest.fn(() => ({ top: 0, left: 0 })),
        position: jest.fn(() => ({ top: 0, left: 0 })),
        scrollTop: jest.fn(value => {
            if (value === undefined) return 0;
            return jQueryObject;
        }),
        scrollLeft: jest.fn(value => {
            if (value === undefined) return 0;
            return jQueryObject;
        }),

        // Bootstrap integration
        modal: jest.fn(options => {
            if (typeof options === 'string') {
                // Handle method calls like 'show', 'hide', 'toggle'
                return jQueryObject;
            }
            // Handle initialization with options
            return jQueryObject;
        }),
        tooltip: jest.fn(options => {
            if (typeof options === 'string') {
                return jQueryObject;
            }
            return jQueryObject;
        }),
        dropdown: jest.fn(options => {
            if (typeof options === 'string') {
                return jQueryObject;
            }
            return jQueryObject;
        }),
        collapse: jest.fn(options => {
            if (typeof options === 'string') {
                return jQueryObject;
            }
            return jQueryObject;
        }),

        // Summernote integration
        summernote: jest.fn(options => {
            if (typeof options === 'string') {
                switch (options) {
                    case 'code':
                        return '<p>Mocked summernote content</p>';
                    case 'destroy':
                        return jQueryObject;
                    case 'isEmpty':
                        return false;
                    case 'reset':
                        return jQueryObject;
                    default:
                        return jQueryObject;
                }
            }
            // Handle initialization with options
            return jQueryObject;
        }),

        // Form handling
        serialize: jest.fn(() => 'mocked=serialized&data=values'),
        serializeArray: jest.fn(() => [
            { name: 'field1', value: 'value1' },
            { name: 'field2', value: 'value2' }
        ]),

        // AJAX methods
        get: jest.fn(() => Promise.resolve({ data: 'mocked-get-response' })),
        post: jest.fn(() => Promise.resolve({ data: 'mocked-post-response' })),
        ajax: jest.fn(() => Promise.resolve({ data: 'mocked-ajax-response' })),

        // Array-like properties
        length: 1,
        0: document.createElement('div'),

        // Iteration
        each: jest.fn((callback) => {
            if (typeof callback === 'function') {
                callback.call(jQueryObject[0], 0, jQueryObject[0]);
            }
            return jQueryObject;
        })
    };

    return jQueryObject;
};

const jQuery = jest.fn((selector, context) => {
    // Return a new jQuery object for each call
    return createJQueryMock();
});

// Static methods
jQuery.param = jest.fn(obj => {
    if (!obj || typeof obj !== 'object') return '';
    return Object.keys(obj)
        .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(obj[key])}`)
        .join('&');
});

jQuery.extend = jest.fn((target, ...sources) => {
    sources.forEach(source => {
        if (source) {
            Object.assign(target, source);
        }
    });
    return target;
});

jQuery.when = jest.fn((...deferreds) => {
    return Promise.all(deferreds);
});

jQuery.Deferred = jest.fn(() => {
    let state = 'pending';
    const callbacks = {
        done: [],
        fail: [],
        always: []
    };

    const deferred = {
        resolve: jest.fn(() => {
            state = 'resolved';
            callbacks.done.forEach(cb => cb());
            callbacks.always.forEach(cb => cb());
            return deferred;
        }),
        reject: jest.fn(() => {
            state = 'rejected';
            callbacks.fail.forEach(cb => cb());
            callbacks.always.forEach(cb => cb());
            return deferred;
        }),
        done: jest.fn(callback => {
            if (typeof callback === 'function') {
                if (state === 'resolved') {
                    callback();
                } else {
                    callbacks.done.push(callback);
                }
            }
            return deferred;
        }),
        fail: jest.fn(callback => {
            if (typeof callback === 'function') {
                if (state === 'rejected') {
                    callback();
                } else {
                    callbacks.fail.push(callback);
                }
            }
            return deferred;
        }),
        always: jest.fn(callback => {
            if (typeof callback === 'function') {
                if (state !== 'pending') {
                    callback();
                } else {
                    callbacks.always.push(callback);
                }
            }
            return deferred;
        }),
        state: jest.fn(() => state),
        promise: jest.fn(() => ({
            done: deferred.done,
            fail: deferred.fail,
            always: deferred.always,
            state: deferred.state
        }))
    };

    return deferred;
});

// jQuery version info
jQuery.fn = {
    jquery: '3.7.1'
};

// Make $ an alias for jQuery
jQuery.noConflict = jest.fn(() => jQuery);

module.exports = jQuery;