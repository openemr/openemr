/**
 * Test setup configuration for Portal Messaging tests
 *
 * This file configures the testing environment and mocks necessary
 * for running frontend tests that simulate browser behavior.
 */

// DOM environment is already provided by Jest's jsdom environment
// No need to manually require jsdom-global

// Configure Jest environment
global.console = {
    ...console,
    // Suppress console.log in tests unless explicitly needed
    log: jest.fn(),
    warn: jest.fn(),
    error: jest.fn()
};

// Mock common browser APIs
global.Audio = jest.fn(() => ({
    play: jest.fn(() => Promise.resolve()),
    pause: jest.fn(),
    load: jest.fn(),
    addEventListener: jest.fn(),
    removeEventListener: jest.fn()
}));

global.Notification = {
    requestPermission: jest.fn(callback => {
        if (typeof callback === 'function') {
            callback('granted');
        }
        return Promise.resolve('granted');
    }),
    permission: 'granted'
};

// Mock XMLHttpRequest for AJAX testing
global.XMLHttpRequest = jest.fn(() => ({
    open: jest.fn(),
    send: jest.fn(),
    setRequestHeader: jest.fn(),
    readyState: 4,
    status: 200,
    responseText: '{"success": true}',
    addEventListener: jest.fn(),
    removeEventListener: jest.fn()
}));

// Mock fetch API
global.fetch = jest.fn(() =>
    Promise.resolve({
        ok: true,
        status: 200,
        json: () => Promise.resolve({ success: true }),
        text: () => Promise.resolve('success')
    })
);

// Mock URL API
global.URL = {
    createObjectURL: jest.fn(() => 'mock-blob-url'),
    revokeObjectURL: jest.fn()
};

// Mock FileReader
global.FileReader = jest.fn(() => ({
    readAsDataURL: jest.fn(),
    readAsText: jest.fn(),
    readAsArrayBuffer: jest.fn(),
    result: 'mock-file-content',
    addEventListener: jest.fn(),
    removeEventListener: jest.fn()
}));

// Mock localStorage
const localStorageMock = {
    getItem: jest.fn(key => null),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn(),
    length: 0,
    key: jest.fn()
};
global.localStorage = localStorageMock;

// Mock sessionStorage
const sessionStorageMock = {
    getItem: jest.fn(key => null),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn(),
    length: 0,
    key: jest.fn()
};
global.sessionStorage = sessionStorageMock;

// Mock crypto API
global.crypto = {
    getRandomValues: jest.fn(arr => {
        for (let i = 0; i < arr.length; i++) {
            arr[i] = Math.floor(Math.random() * 256);
        }
        return arr;
    }),
    randomUUID: jest.fn(() => 'mock-uuid-1234-5678-9abc-def0')
};

// Mock ResizeObserver
global.ResizeObserver = jest.fn(() => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
    disconnect: jest.fn()
}));

// Mock IntersectionObserver
global.IntersectionObserver = jest.fn(() => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
    disconnect: jest.fn()
}));

// Mock MutationObserver
global.MutationObserver = jest.fn(() => ({
    observe: jest.fn(),
    disconnect: jest.fn(),
    takeRecords: jest.fn(() => [])
}));

// Mock window.matchMedia
global.matchMedia = jest.fn(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: jest.fn(),
    removeListener: jest.fn(),
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    dispatchEvent: jest.fn()
}));

// Mock getComputedStyle
global.getComputedStyle = jest.fn(() => ({
    getPropertyValue: jest.fn(() => ''),
    setProperty: jest.fn(),
    removeProperty: jest.fn()
}));

// Mock performance API
global.performance = {
    now: jest.fn(() => Date.now()),
    mark: jest.fn(),
    measure: jest.fn(),
    getEntriesByName: jest.fn(() => []),
    getEntriesByType: jest.fn(() => []),
    navigation: {
        type: 0
    },
    timing: {
        navigationStart: Date.now() - 1000,
        loadEventEnd: Date.now()
    }
};

// Setup DOM helpers
global.createMockElement = (tagName, attributes = {}) => {
    const element = document.createElement(tagName);
    Object.keys(attributes).forEach(key => {
        if (key === 'className') {
            element.className = attributes[key];
        } else if (key === 'innerHTML') {
            element.innerHTML = attributes[key];
        } else {
            element.setAttribute(key, attributes[key]);
        }
    });
    return element;
};

global.createMockForm = (fields = {}) => {
    const form = document.createElement('form');
    Object.keys(fields).forEach(name => {
        const input = document.createElement('input');
        input.name = name;
        input.value = fields[name];
        form.appendChild(input);
    });
    return form;
};

// Mock canvas context for charts
global.HTMLCanvasElement.prototype.getContext = jest.fn(() => ({
    fillRect: jest.fn(),
    clearRect: jest.fn(),
    getImageData: jest.fn(() => ({ data: new Array(4) })),
    putImageData: jest.fn(),
    createImageData: jest.fn(() => []),
    setTransform: jest.fn(),
    drawImage: jest.fn(),
    save: jest.fn(),
    fillText: jest.fn(),
    restore: jest.fn(),
    beginPath: jest.fn(),
    moveTo: jest.fn(),
    lineTo: jest.fn(),
    closePath: jest.fn(),
    stroke: jest.fn(),
    translate: jest.fn(),
    scale: jest.fn(),
    rotate: jest.fn(),
    arc: jest.fn(),
    fill: jest.fn(),
    measureText: jest.fn(() => ({ width: 0 })),
    transform: jest.fn(),
    rect: jest.fn(),
    clip: jest.fn()
}));

// Clean up function for tests
global.cleanupTestEnvironment = () => {
    // Clear all mocks
    jest.clearAllMocks();

    // Reset DOM
    document.body.innerHTML = '';
    document.head.innerHTML = '';

    // Clear storage
    localStorage.clear();
    sessionStorage.clear();

    // Reset global variables
    delete global.angular;
    delete global.$;
    delete global.jQuery;
    delete global.bootstrap;
    delete global.DOMPurify;
    delete global.CKEDITOR;
    delete global.Chart;
    delete global.moment;
    delete global.validate;
};

// Set up test timeout
jest.setTimeout(10000);

// Mock console methods for cleaner test output
const originalConsole = global.console;
global.console = {
    ...originalConsole,
    log: jest.fn(),
    warn: jest.fn(),
    error: jest.fn(),
    info: jest.fn(),
    debug: jest.fn()
};

// Restore console for specific tests that need it
global.restoreConsole = () => {
    global.console = originalConsole;
};

// Helper to create mock HTTP responses
global.createMockResponse = (data, status = 200) => ({
    ok: status >= 200 && status < 300,
    status,
    statusText: status === 200 ? 'OK' : 'Error',
    json: () => Promise.resolve(data),
    text: () => Promise.resolve(JSON.stringify(data)),
    headers: {
        get: jest.fn(key => {
            if (key === 'content-type') return 'application/json';
            return null;
        })
    }
});

// Helper to simulate async operations
global.waitFor = (ms = 0) => new Promise(resolve => setTimeout(resolve, ms));

// Helper to create mock events
global.createMockEvent = (type, options = {}) => {
    const event = new Event(type, { bubbles: true, cancelable: true, ...options });
    Object.keys(options).forEach(key => {
        if (!(key in event)) {
            event[key] = options[key];
        }
    });
    return event;
};

module.exports = {
    cleanupTestEnvironment: global.cleanupTestEnvironment,
    createMockElement: global.createMockElement,
    createMockForm: global.createMockForm,
    createMockResponse: global.createMockResponse,
    waitFor: global.waitFor,
    createMockEvent: global.createMockEvent
};