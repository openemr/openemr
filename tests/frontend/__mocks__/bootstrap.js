/**
 * Bootstrap mock for testing
 * Provides a mock implementation of Bootstrap JavaScript components
 */

const createBootstrapMock = () => {
    // Base component class mock
    const BaseComponent = class {
        constructor(element, config = {}) {
            this._element = element;
            this._config = config;
            this._isShown = false;
        }

        dispose() {
            this._element = null;
            this._config = null;
        }

        _getConfig(config) {
            return { ...this._config, ...config };
        }

        static getInstance(element) {
            return element._bootstrapInstance || null;
        }

        static getOrCreateInstance(element, config = {}) {
            return this.getInstance(element) || new this(element, config);
        }
    };

    // Modal component mock
    const Modal = class extends BaseComponent {
        constructor(element, config = {}) {
            super(element, config);
            this._backdrop = null;
            this._isTransitioning = false;

            // Store instance on element
            if (element) {
                element._bootstrapInstance = this;
            }
        }

        show() {
            if (this._isShown || this._isTransitioning) {
                return;
            }

            this._isShown = true;

            // Trigger events
            const showEvent = new CustomEvent('show.bs.modal', {
                bubbles: true,
                cancelable: true
            });

            if (this._element) {
                this._element.dispatchEvent(showEvent);

                // Simulate modal opening
                setTimeout(() => {
                    const shownEvent = new CustomEvent('shown.bs.modal', {
                        bubbles: true
                    });
                    this._element.dispatchEvent(shownEvent);
                }, 10);
            }
        }

        hide() {
            if (!this._isShown || this._isTransitioning) {
                return;
            }

            this._isShown = false;

            // Trigger events
            const hideEvent = new CustomEvent('hide.bs.modal', {
                bubbles: true,
                cancelable: true
            });

            if (this._element) {
                this._element.dispatchEvent(hideEvent);

                // Simulate modal closing
                setTimeout(() => {
                    const hiddenEvent = new CustomEvent('hidden.bs.modal', {
                        bubbles: true
                    });
                    this._element.dispatchEvent(hiddenEvent);
                }, 10);
            }
        }

        toggle() {
            return this._isShown ? this.hide() : this.show();
        }

        static jQueryInterface(config, relatedTarget) {
            return function() {
                const data = Modal.getOrCreateInstance(this);

                if (typeof config === 'string') {
                    if (data[config] === undefined) {
                        throw new TypeError(`No method named "${config}"`);
                    }
                    data[config](relatedTarget);
                } else if (config.show !== false) {
                    data.show(relatedTarget);
                }
            };
        }
    };

    // Tooltip component mock
    const Tooltip = class extends BaseComponent {
        constructor(element, config = {}) {
            super(element, config);
            this._isEnabled = true;
            this._timeout = 0;
            this._isHovered = null;
            this._activeTrigger = {};
        }

        enable() {
            this._isEnabled = true;
        }

        disable() {
            this._isEnabled = false;
        }

        toggleEnabled() {
            this._isEnabled = !this._isEnabled;
        }

        toggle() {
            if (!this._isEnabled) {
                return;
            }

            this._activeTrigger.click = !this._activeTrigger.click;

            if (this._isShown()) {
                this._leave();
            } else {
                this._enter();
            }
        }

        dispose() {
            clearTimeout(this._timeout);
            super.dispose();
        }

        show() {
            if (!this._isEnabled) {
                return;
            }

            const showEvent = new CustomEvent('show.bs.tooltip', {
                bubbles: true,
                cancelable: true
            });

            if (this._element) {
                this._element.dispatchEvent(showEvent);

                setTimeout(() => {
                    const shownEvent = new CustomEvent('shown.bs.tooltip', {
                        bubbles: true
                    });
                    this._element.dispatchEvent(shownEvent);
                }, 10);
            }
        }

        hide() {
            const hideEvent = new CustomEvent('hide.bs.tooltip', {
                bubbles: true,
                cancelable: true
            });

            if (this._element) {
                this._element.dispatchEvent(hideEvent);

                setTimeout(() => {
                    const hiddenEvent = new CustomEvent('hidden.bs.tooltip', {
                        bubbles: true
                    });
                    this._element.dispatchEvent(hiddenEvent);
                }, 10);
            }
        }

        _isShown() {
            return this._activeTrigger.click ||
                   this._activeTrigger.focus ||
                   this._activeTrigger.hover;
        }

        _enter() {
            this._activeTrigger.focus = true;
            this.show();
        }

        _leave() {
            this._activeTrigger.focus = false;
            this.hide();
        }

        static jQueryInterface(config) {
            return function() {
                const data = Tooltip.getOrCreateInstance(this);

                if (typeof config === 'string') {
                    if (data[config] === undefined) {
                        throw new TypeError(`No method named "${config}"`);
                    }
                    data[config]();
                }
            };
        }
    };

    // Dropdown component mock
    const Dropdown = class extends BaseComponent {
        constructor(element, config = {}) {
            super(element, config);
            this._menu = null;
            this._isShown = false;
        }

        show() {
            if (this._isShown) {
                return;
            }

            this._isShown = true;

            const showEvent = new CustomEvent('show.bs.dropdown', {
                bubbles: true,
                cancelable: true
            });

            if (this._element) {
                this._element.dispatchEvent(showEvent);

                setTimeout(() => {
                    const shownEvent = new CustomEvent('shown.bs.dropdown', {
                        bubbles: true
                    });
                    this._element.dispatchEvent(shownEvent);
                }, 10);
            }
        }

        hide() {
            if (!this._isShown) {
                return;
            }

            this._isShown = false;

            const hideEvent = new CustomEvent('hide.bs.dropdown', {
                bubbles: true,
                cancelable: true
            });

            if (this._element) {
                this._element.dispatchEvent(hideEvent);

                setTimeout(() => {
                    const hiddenEvent = new CustomEvent('hidden.bs.dropdown', {
                        bubbles: true
                    });
                    this._element.dispatchEvent(hiddenEvent);
                }, 10);
            }
        }

        toggle() {
            return this._isShown ? this.hide() : this.show();
        }

        static jQueryInterface(config) {
            return function() {
                const data = Dropdown.getOrCreateInstance(this);

                if (typeof config === 'string') {
                    if (data[config] === undefined) {
                        throw new TypeError(`No method named "${config}"`);
                    }
                    data[config]();
                } else {
                    data.toggle();
                }
            };
        }
    };

    // Collapse component mock
    const Collapse = class extends BaseComponent {
        constructor(element, config = {}) {
            super(element, config);
            this._isTransitioning = false;
            this._isShown = false;
        }

        show() {
            if (this._isTransitioning || this._isShown) {
                return;
            }

            this._isTransitioning = true;

            const showEvent = new CustomEvent('show.bs.collapse', {
                bubbles: true,
                cancelable: true
            });

            if (this._element) {
                this._element.dispatchEvent(showEvent);

                setTimeout(() => {
                    this._isTransitioning = false;
                    this._isShown = true;

                    const shownEvent = new CustomEvent('shown.bs.collapse', {
                        bubbles: true
                    });
                    this._element.dispatchEvent(shownEvent);
                }, 350); // Bootstrap's default transition duration
            }
        }

        hide() {
            if (this._isTransitioning || !this._isShown) {
                return;
            }

            this._isTransitioning = true;

            const hideEvent = new CustomEvent('hide.bs.collapse', {
                bubbles: true,
                cancelable: true
            });

            if (this._element) {
                this._element.dispatchEvent(hideEvent);

                setTimeout(() => {
                    this._isTransitioning = false;
                    this._isShown = false;

                    const hiddenEvent = new CustomEvent('hidden.bs.collapse', {
                        bubbles: true
                    });
                    this._element.dispatchEvent(hiddenEvent);
                }, 350);
            }
        }

        toggle() {
            return this._isShown ? this.hide() : this.show();
        }

        static jQueryInterface(config) {
            return function() {
                const data = Collapse.getOrCreateInstance(this);

                if (typeof config === 'string') {
                    if (data[config] === undefined) {
                        throw new TypeError(`No method named "${config}"`);
                    }
                    data[config]();
                } else {
                    data.toggle();
                }
            };
        }
    };

    // Tab component mock
    const Tab = class extends BaseComponent {
        constructor(element, config = {}) {
            super(element, config);
        }

        show() {
            const showEvent = new CustomEvent('show.bs.tab', {
                bubbles: true,
                cancelable: true
            });

            if (this._element) {
                this._element.dispatchEvent(showEvent);

                setTimeout(() => {
                    const shownEvent = new CustomEvent('shown.bs.tab', {
                        bubbles: true
                    });
                    this._element.dispatchEvent(shownEvent);
                }, 10);
            }
        }

        static jQueryInterface(config) {
            return function() {
                const data = Tab.getOrCreateInstance(this);

                if (typeof config === 'string') {
                    if (data[config] === undefined) {
                        throw new TypeError(`No method named "${config}"`);
                    }
                    data[config]();
                }
            };
        }
    };

    // Main Bootstrap object
    const bootstrap = {
        Modal,
        Tooltip,
        Dropdown,
        Collapse,
        Tab,

        // Utility functions
        Util: {
            getUID: jest.fn(() => Math.random().toString(36).substr(2, 9)),
            getSelectorFromElement: jest.fn(element => {
                const selector = element.getAttribute('data-target') ||
                               element.getAttribute('href') ||
                               element.getAttribute('data-bs-target');
                return selector && selector !== '#' ? selector : null;
            }),
            getElementFromSelector: jest.fn(element => {
                const selector = bootstrap.Util.getSelectorFromElement(element);
                return selector ? document.querySelector(selector) : null;
            }),
            isElement: jest.fn(obj => {
                return obj && obj.nodeType === 1;
            }),
            typeCheckConfig: jest.fn(() => {}),
            isVisible: jest.fn(element => {
                return !!(element.offsetWidth || element.offsetHeight || element.getClientRects().length);
            }),
            isDisabled: jest.fn(element => {
                return element.hasAttribute('disabled') ||
                       element.classList.contains('disabled') ||
                       (element.hasAttribute('aria-disabled') && element.getAttribute('aria-disabled') !== 'false');
            }),
            findShadowRoot: jest.fn(element => {
                return null; // Simplified for testing
            }),
            noop: jest.fn(() => {}),
            reflow: jest.fn(element => {
                return element.offsetHeight;
            })
        },

        // Event system
        EventHandler: {
            on: jest.fn((element, event, handler) => {
                if (element && element.addEventListener) {
                    element.addEventListener(event, handler);
                }
            }),
            off: jest.fn((element, event, handler) => {
                if (element && element.removeEventListener) {
                    element.removeEventListener(event, handler);
                }
            }),
            one: jest.fn((element, event, handler) => {
                const wrappedHandler = (e) => {
                    handler(e);
                    element.removeEventListener(event, wrappedHandler);
                };
                if (element && element.addEventListener) {
                    element.addEventListener(event, wrappedHandler);
                }
            }),
            trigger: jest.fn((element, event, extraParameters = {}) => {
                if (element) {
                    const evt = new CustomEvent(event, {
                        bubbles: true,
                        cancelable: true,
                        detail: extraParameters
                    });
                    element.dispatchEvent(evt);
                    return evt;
                }
            })
        }
    };

    return bootstrap;
};

const bootstrap = createBootstrapMock();

module.exports = bootstrap;