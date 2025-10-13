/**
 * AngularJS mock for testing
 * Provides a mock implementation of AngularJS functionality used in portal messaging
 */

const createAngularMock = () => {
    const moduleMock = {
        // Module methods
        controller: jest.fn(function(name, constructor) {
            this._controllers = this._controllers || {};
            this._controllers[name] = constructor;
            return this;
        }),

        directive: jest.fn(function(name, factory) {
            this._directives = this._directives || {};
            this._directives[name] = factory;
            return this;
        }),

        filter: jest.fn(function(name, factory) {
            this._filters = this._filters || {};
            this._filters[name] = factory;
            return this;
        }),

        service: jest.fn(function(name, constructor) {
            this._services = this._services || {};
            this._services[name] = constructor;
            return this;
        }),

        factory: jest.fn(function(name, factory) {
            this._factories = this._factories || {};
            this._factories[name] = factory;
            return this;
        }),

        provider: jest.fn(function(name, provider) {
            this._providers = this._providers || {};
            this._providers[name] = provider;
            return this;
        }),

        value: jest.fn(function(name, value) {
            this._values = this._values || {};
            this._values[name] = value;
            return this;
        }),

        constant: jest.fn(function(name, value) {
            this._constants = this._constants || {};
            this._constants[name] = value;
            return this;
        }),

        config: jest.fn(function(configFn) {
            this._configs = this._configs || [];
            this._configs.push(configFn);
            return this;
        }),

        run: jest.fn(function(runFn) {
            this._runs = this._runs || [];
            this._runs.push(runFn);
            return this;
        }),

        // Internal properties for testing
        _controllers: {},
        _directives: {},
        _filters: {},
        _services: {},
        _factories: {},
        _providers: {},
        _values: {},
        _constants: {},
        _configs: [],
        _runs: []
    };

    const angular = {
        // Version info
        version: {
            full: '1.8.3',
            major: 1,
            minor: 8,
            dot: 3
        },

        // Module system
        module: jest.fn((name, requires) => {
            if (requires === undefined) {
                // Getter - return existing module
                return moduleMock;
            } else {
                // Setter - create new module
                const newModule = Object.create(moduleMock);
                newModule.name = name;
                newModule.requires = requires || [];
                return newModule;
            }
        }),

        // Utility functions
        forEach: jest.fn((obj, iterator, context) => {
            if (!obj) return obj;

            if (obj.forEach && obj.forEach === Array.prototype.forEach) {
                obj.forEach(iterator, context);
            } else if (Array.isArray(obj)) {
                for (let i = 0; i < obj.length; i++) {
                    iterator.call(context, obj[i], i, obj);
                }
            } else {
                for (let key in obj) {
                    if (Object.prototype.hasOwnProperty.call(obj, key)) {
                        iterator.call(context, obj[key], key, obj);
                    }
                }
            }
            return obj;
        }),

        extend: jest.fn((dst, ...sources) => {
            sources.forEach(source => {
                if (source) {
                    for (let key in source) {
                        if (Object.prototype.hasOwnProperty.call(source, key)) {
                            dst[key] = source[key];
                        }
                    }
                }
            });
            return dst;
        }),

        copy: jest.fn((source, destination) => {
            if (!source) return source;

            if (destination) {
                // Deep copy into existing object
                for (let key in source) {
                    if (Object.prototype.hasOwnProperty.call(source, key)) {
                        if (typeof source[key] === 'object' && source[key] !== null) {
                            destination[key] = angular.copy(source[key]);
                        } else {
                            destination[key] = source[key];
                        }
                    }
                }
                return destination;
            } else {
                // Create new copy
                if (Array.isArray(source)) {
                    return source.map(item => angular.copy(item));
                } else if (typeof source === 'object') {
                    const copy = {};
                    for (let key in source) {
                        if (Object.prototype.hasOwnProperty.call(source, key)) {
                            copy[key] = angular.copy(source[key]);
                        }
                    }
                    return copy;
                } else {
                    return source;
                }
            }
        }),

        merge: jest.fn((dst, ...sources) => {
            return angular.extend(dst, ...sources);
        }),

        // Type checking functions
        isUndefined: jest.fn(value => value === undefined),
        isDefined: jest.fn(value => value !== undefined),
        isObject: jest.fn(value => value !== null && typeof value === 'object'),
        isString: jest.fn(value => typeof value === 'string'),
        isNumber: jest.fn(value => typeof value === 'number'),
        isDate: jest.fn(value => value instanceof Date),
        isArray: jest.fn(value => Array.isArray(value)),
        isFunction: jest.fn(value => typeof value === 'function'),
        isElement: jest.fn(value => !!(value && value.nodeName)),

        // Comparison functions
        equals: jest.fn((o1, o2) => {
            if (o1 === o2) return true;
            if (o1 === null || o2 === null) return false;
            if (o1 !== o1 && o2 !== o2) return true; // NaN === NaN

            const t1 = typeof o1;
            const t2 = typeof o2;
            if (t1 !== t2) return false;

            if (t1 === 'object') {
                if (Array.isArray(o1) !== Array.isArray(o2)) return false;
                if (Array.isArray(o1)) {
                    if (o1.length !== o2.length) return false;
                    for (let i = 0; i < o1.length; i++) {
                        if (!angular.equals(o1[i], o2[i])) return false;
                    }
                    return true;
                } else {
                    const keys1 = Object.keys(o1);
                    const keys2 = Object.keys(o2);
                    if (keys1.length !== keys2.length) return false;
                    for (let key of keys1) {
                        if (!angular.equals(o1[key], o2[key])) return false;
                    }
                    return true;
                }
            }

            return false;
        }),

        // JSON functions
        toJson: jest.fn(obj => JSON.stringify(obj)),
        fromJson: jest.fn(json => {
            try {
                return JSON.parse(json);
            } catch (e) {
                return undefined;
            }
        }),

        // DOM element wrapper
        element: jest.fn(element => {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }

            const jqLite = {
                0: element,
                length: element ? 1 : 0,

                // DOM methods
                addClass: jest.fn(() => jqLite),
                removeClass: jest.fn(() => jqLite),
                toggleClass: jest.fn(() => jqLite),
                hasClass: jest.fn(() => true),
                attr: jest.fn((name, value) => {
                    if (value === undefined) return element ? element.getAttribute(name) : null;
                    if (element) element.setAttribute(name, value);
                    return jqLite;
                }),
                prop: jest.fn((name, value) => {
                    if (value === undefined) return element ? element[name] : null;
                    if (element) element[name] = value;
                    return jqLite;
                }),
                html: jest.fn((html) => {
                    if (html === undefined) return element ? element.innerHTML : '';
                    if (element) element.innerHTML = html;
                    return jqLite;
                }),
                text: jest.fn((text) => {
                    if (text === undefined) return element ? element.textContent : '';
                    if (element) element.textContent = text;
                    return jqLite;
                }),
                val: jest.fn((value) => {
                    if (value === undefined) return element ? element.value : '';
                    if (element) element.value = value;
                    return jqLite;
                }),

                // Traversal
                find: jest.fn(selector => {
                    const found = element ? element.querySelector(selector) : null;
                    return angular.element(found);
                }),
                parent: jest.fn(() => {
                    const parent = element ? element.parentNode : null;
                    return angular.element(parent);
                }),
                children: jest.fn(() => {
                    const children = element ? Array.from(element.children) : [];
                    return children.map(child => angular.element(child));
                }),

                // Events
                on: jest.fn((event, handler) => {
                    if (element) element.addEventListener(event, handler);
                    return jqLite;
                }),
                off: jest.fn((event, handler) => {
                    if (element) element.removeEventListener(event, handler);
                    return jqLite;
                }),
                trigger: jest.fn((event) => {
                    if (element) {
                        const evt = new Event(event, { bubbles: true });
                        element.dispatchEvent(evt);
                    }
                    return jqLite;
                }),

                // Angular-specific methods
                scope: jest.fn(() => ({
                    $id: Math.floor(Math.random() * 1000),
                    $parent: null,
                    $root: null,
                    $apply: jest.fn(),
                    $digest: jest.fn(),
                    $watch: jest.fn(),
                    $on: jest.fn(),
                    $emit: jest.fn(),
                    $broadcast: jest.fn(),
                    $destroy: jest.fn()
                })),
                isolateScope: jest.fn(() => null),
                inheritedData: jest.fn(() => null),
                data: jest.fn((key, value) => {
                    if (value === undefined) return null;
                    return jqLite;
                }),
                removeData: jest.fn(() => jqLite),

                // Controller access
                controller: jest.fn(name => {
                    return {
                        name: name,
                        instance: {}
                    };
                }),
                injector: jest.fn(() => ({
                    get: jest.fn(name => ({}))
                }))
            };

            return jqLite;
        }),

        // Bootstrap function
        bootstrap: jest.fn((element, modules, config) => {
            return {
                injector: {
                    get: jest.fn(name => ({}))
                }
            };
        }),

        // Injector
        injector: jest.fn((modules) => ({
            get: jest.fn(name => {
                // Return mock services
                switch (name) {
                    case '$http':
                        return {
                            get: jest.fn(() => Promise.resolve({ data: {} })),
                            post: jest.fn(() => Promise.resolve({ data: {} })),
                            put: jest.fn(() => Promise.resolve({ data: {} })),
                            delete: jest.fn(() => Promise.resolve({ data: {} })),
                            defaults: { headers: { post: {}, common: {} } }
                        };
                    case '$q':
                        return {
                            defer: jest.fn(() => ({
                                promise: Promise.resolve(),
                                resolve: jest.fn(),
                                reject: jest.fn()
                            })),
                            when: jest.fn(value => Promise.resolve(value)),
                            all: jest.fn(promises => Promise.all(promises)),
                            reject: jest.fn(reason => Promise.reject(reason))
                        };
                    case '$timeout':
                        return jest.fn((fn, delay) => setTimeout(fn, delay || 0));
                    case '$interval':
                        return jest.fn((fn, delay) => setInterval(fn, delay || 1000));
                    case '$window':
                        return global.window || {};
                    case '$document':
                        return angular.element(document);
                    case '$location':
                        return {
                            path: jest.fn(),
                            url: jest.fn(),
                            search: jest.fn(),
                            hash: jest.fn()
                        };
                    case '$filter':
                        return jest.fn(name => value => value);
                    default:
                        return {};
                }
            }),
            invoke: jest.fn(fn => {
                if (Array.isArray(fn)) {
                    const func = fn[fn.length - 1];
                    const deps = fn.slice(0, -1).map(dep => ({}));
                    return func.apply(null, deps);
                } else if (typeof fn === 'function') {
                    return fn();
                }
                return null;
            }),
            instantiate: jest.fn(constructor => new constructor()),
            has: jest.fn(name => true),
            annotate: jest.fn(fn => {
                if (Array.isArray(fn)) {
                    return fn.slice(0, -1);
                }
                return [];
            })
        })),

        // Error handling
        errorHandlingConfig: jest.fn(() => ({})),

        // No-op functions for testing
        noop: jest.fn(() => {}),
        identity: jest.fn(value => value),

        // Mock digest cycle
        $$phase: null,
        $$postDigest: jest.fn(fn => setTimeout(fn, 0))
    };

    return angular;
};

const angular = createAngularMock();

module.exports = angular;