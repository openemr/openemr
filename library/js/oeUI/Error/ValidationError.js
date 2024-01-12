export class ValidationError extends Error {
    validationErrors = [];
    constructor(message, validationErrors = []) {
        super(message);
        this.name = "ValidationError";
        this.validationErrors = validationErrors;
    }
}

export class ValidationFieldError {
    field = null;
    validationErrors = {};

    constructor(field, validationErrors = {}) {
        this.field = field;
        this.validationErrors = validationErrors;
    }

    getCombinedMessages() {
        let messages = [];
        for (let key in this.validationErrors) {
            messages.push(this.validationErrors[key]);
        }
        return messages.join(" ");
    }
}
