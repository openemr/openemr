'use strict';

function fDate(str, lim8 = false) {
    const input = String(str).trim();

    // Handle null-like values
    if (!input || input === '0000-00-00' || Number(input) === 0) {
        return new Date().toISOString();
    }

    // If lim8 is set, return only first 8 digits
    if (lim8) {
        // Support for yyyy-mm-dd to yyyymmdd
        const justDigits = input.replace(/[^\d]/g, '');
        return justDigits.substring(0, 8);
    }

    // Handle plain yyyymmdd or yyyymmddhhmmss
    if (input.length === 8 || (input.length === 14 && Number(input.substring(12, 14)) === 0)) {
        return `${input.slice(0, 4)}-${input.slice(4, 6)}-${input.slice(6, 8)}`;
    }

    // Handle mm/dd/yyyy or mm-dd-yyyy
    if (input.length === 10 && Number(input.substring(0, 2)) <= 12) {
        return `${input.slice(6, 10)}-${input.slice(0, 2)}-${input.slice(3, 5)}`;
    }

    // Handle yyyy-mm-dd
    if (/^\d{4}-\d{2}-\d{2}$/.test(input)) {
        return input;
    }

    // Handle yyyymmdd hh:mm:ss
    if (input.length === 17 && input.includes(' ')) {
        const [datePart, timePart] = input.split(' ');
        return `${datePart.slice(0, 4)}-${datePart.slice(4, 6)}-${datePart.slice(6, 8)} ${timePart}`;
    }

    // Handle yyyymmddhhmmss-zzzz
    if (input.length === 19 && input[14] === '-') {
        const [datePart, offset] = input.split('-');
        const date = `${datePart.slice(0, 4)}-${datePart.slice(4, 6)}-${datePart.slice(6, 8)}`;
        const time = `${datePart.slice(8, 10)}:${datePart.slice(10, 12)}:${datePart.slice(12, 14)}`;
        return `${date} ${time}-${offset}`;
    }

    // Handle yyyymmddhhmmss±zzzz
    if (input.length === 19 && (input[14] === '+' || input[14] === '-')) {
        const date = `${input.slice(0, 4)}-${input.slice(4, 6)}-${input.slice(6, 8)}`;
        const time = `${input.slice(8, 10)}:${input.slice(10, 12)}:${input.slice(12, 14)}`;
        return `${date}T${time}${input.slice(14)}`;
    }

    return input;
}

function templateDate(date, precision) {
    return { date: fDate(date), precision };
}

exports.fDate = fDate;
exports.templateDate = templateDate;
