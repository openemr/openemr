'use strict';

const { fDate } = require('../date/date');
const { countEntities } = require('../count-entities/count-entities');

function buildStreetLines(streets) {
    const streetLines = [streets[0]];
    if (streets[1]?.length > 0) streetLines.push(streets[1]);
    return streetLines;
}

function buildAddressItem(input, isCurrent = false) {
    return {
        use: isCurrent ? 'HP' : input.use,
        street_lines: buildStreetLines(input.street),
        city: input.city,
        state: input.state,
        zip: input.postalCode,
        country: input.country || 'US',
        date_time: {
            low: {
                date: fDate(isCurrent ? '' : input.period_start),
                precision: 'day',
            },
            ...(isCurrent
                ? {}
                : {
                      high: {
                          date: fDate(input.period_end) || fDate(''),
                          precision: 'day',
                      },
                  }),
        },
    };
}

function fetchPreviousAddresses(input) {
    const addresses = [];
    const currentAddress = buildAddressItem(input, true);
    addresses.push(currentAddress);
    const previousAddresses = input.previous_addresses.address;
    const count = countEntities(previousAddresses);
    if (count === 1) {
        // how do we ever get here where we just have one object?
        const previous = buildAddressItem(previousAddresses);
        addresses.push(previous);
    } else if (count > 1) {
        for (let i in previousAddresses) {
            const previous = buildAddressItem(previousAddresses[i]);
            addresses.push(previous);
        }
    }
    return addresses;
}

exports.fetchPreviousAddresses = fetchPreviousAddresses;
