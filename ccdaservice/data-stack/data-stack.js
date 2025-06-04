'use strict';

class DataStack {
    #buffer;
    #delimiter;

    constructor(delimiter) {
        this.#delimiter = delimiter;
        this.#buffer = '';
    }

    endOfCcda() {
        return this.#buffer.length === 0 || this.#buffer.indexOf(this.#delimiter) === -1;
    }

    push(data) {
        this.#buffer += data;
    }

    #fetchBuffer() {
        const delimiterIndex = this.#buffer.indexOf(this.#delimiter);
        if (delimiterIndex === -1) return null;
        const data = this.#buffer.slice(0, delimiterIndex);
        this.#buffer = this.#buffer.replace(data + this.#delimiter, '');
        return data;
    }

    returnData() {
        return this.#fetchBuffer();
    }

    clear() {
        this.#buffer = '';
    }

    readStackByDelimiter(delimiter) {
        const originalDelimiter = this.#delimiter;
        this.#delimiter = delimiter;
        const message = this.#fetchBuffer();
        this.#delimiter = originalDelimiter;
        return message;
    }
}

exports.DataStack = DataStack;
