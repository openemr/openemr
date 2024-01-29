/**
 * Represents a validation error in javascript.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
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
