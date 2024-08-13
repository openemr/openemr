'use strict';

function fDate(str, lim8 = false) {
    const input = String(str);

    if (lim8)
        return input.substring(0, 8);

    if (Number(input) === 0 || input.length === 1 || input === '0000-00-00')
        return (new Date()).toISOString();

    if (input.length === 8 || (input.length === 14 && Number(input.substring(12, 14)) === 0))
        return `${input.slice(0, 4)}-${input.slice(4, 6)}-${input.slice(6, 8)}`;

    // mm/dd/yyyy or mm-dd-yyyy
    if (input.length === 10 && Number(input.substring(0, 2) <= 12))
        return `${input.slice(6, 10)}-${input.slice(0, 2)}-${input.slice(3, 5)}`;

    if (input.length === 17) {
        const sections = input.split(' ');
        return `${sections[0].slice(0, 4)}-${sections[0].slice(4, 6)}-${sections[0].slice(6, 8)} ${sections[1]}`;
    }

    if (input.length === 19 && (input[14] === '-')) {
        const sections = input.split('-');
        const date = `${sections[0].slice(0, 4)}-${sections[0].slice(4, 6)}-${sections[0].slice(6, 8)}`;
        const time = `${sections[0].slice(8, 10)}:${sections[0].slice(10, 12)}:${sections[0].slice(12, 14)}`;
        return `${date} ${time}-${sections[1]}`;
    }

    return input;
}

function templateDate(date, precision) {
    return { date: fDate(date), precision };
}

exports.fDate = fDate;
exports.templateDate = templateDate;
