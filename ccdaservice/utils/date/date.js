'use strict';

function fDate(str, lim8 = false) {
    const input = String(str);

    if (lim8) return input.substring(0, 8);

    if (Number(input) === 0 || input.length === 1 || input === '0000-00-00') {
        return (new Date()).toISOString();
    }

    if (input.length === 8 || (input.length === 14 && Number(input.substring(12, 14)) === 0)) {
        return `${input.slice(0, 4)}-${input.slice(4, 6)}-${input.slice(6, 8)}`;
    }

    if (input.length === 10 && Number(input.substring(0, 2)) <= 12) {
        return `${input.slice(6, 10)}-${input.slice(0, 2)}-${input.slice(3, 5)}`;
    }

    if (input.length === 17) {
        const [datePart, timePart] = input.split(' ');
        return `${datePart.slice(0, 4)}-${datePart.slice(4, 6)}-${datePart.slice(6, 8)} ${timePart}`;
    }

    if (input.length === 19 && input[14] === '-') {
        const [datePart, offset] = input.split('-');
        const date = `${datePart.slice(0, 4)}-${datePart.slice(4, 6)}-${datePart.slice(6, 8)}`;
        const time = `${datePart.slice(8, 10)}:${datePart.slice(10, 12)}:${datePart.slice(12, 14)}`;
        return `${date} ${time}-${offset}`;
    }

    // Check for format yyyymmddmmss+zzzz
    if (input.length === 19 && (input[14] === '+' || input[14] === '-')) {
        const date = `${input.slice(0, 4)}-${input.slice(4, 6)}-${input.slice(6, 8)}`;
        const time = `${input.slice(8, 10)}:${input.slice(10, 12)}:${input.slice(12, 14)}`;
        const offset = `${input.slice(14, 16)}:${input.slice(16, 18)}`;
        return `${date}T${time}${input.slice(14)}`;
    }

    return input;
}

function templateDate(date, precision) {
    return { date: fDate(date), precision };
}

exports.fDate = fDate;
exports.templateDate = templateDate;
