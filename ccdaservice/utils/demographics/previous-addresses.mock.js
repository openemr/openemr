const TODAY = '2021-04-06T18:55:55.301Z';

const cityA = {
    city: 'Blue Bell',
    state: 'MA',
    postalCode: '02368',
    country: 'US',
    street: ['17 Daws Rd.'],
    use: 'primary home',
};

const cityB = {
    city: 'Borgo Marino',
    state: 'Pavia',
    country: 'IT',
    postalCode: '65210',
    street: ['Borgo Lamberto 942', 'Appartamento 59'],
    use: 'primary home',
    period_start: '01/07/2019',
    period_end: '12/31/2022',
};

const cityC = {
    city: 'Riley Causeway',
    state: 'Hawaii',
    postalCode: '93789-5373',
    street: ['88792 Myrna Point', 'Suite 258'],
    use: 'work place',
    period_start: '05/09/2023',
};

const TEST_CASES = [
    [
        // INPUT
        {
            ...cityA,
            previous_addresses: {
                address: [cityB, cityC],
            },
        },
        // RESULT
        [
            {
                city: cityA.city,
                country: cityA.country,
                date_time: {
                    low: {
                        date: TODAY,
                        precision: 'day',
                    },
                },
                state: cityA.state,
                street_lines: [cityA.street[0]],
                use: 'HP',
                zip: cityA.postalCode,
            },
            {
                city: cityB.city,
                country: cityB.country,
                date_time: {
                    high: {
                        date: '2022-12-31',
                        precision: 'day',
                    },
                    low: {
                        date: '2019-01-07',
                        precision: 'day',
                    },
                },
                state: cityB.state,
                street_lines: [cityB.street[0], cityB.street[1]],
                use: 'primary home',
                zip: cityB.postalCode,
            },
            {
                city: cityC.city,
                country: 'US',
                date_time: {
                    high: {
                        date: 'undefined',
                        precision: 'day',
                    },
                    low: {
                        date: '2023-05-09',
                        precision: 'day',
                    },
                },
                state: cityC.state,
                street_lines: [cityC.street[0], cityC.street[1]],
                use: 'work place',
                zip: cityC.postalCode,
            },
        ],
    ],
    [
        // INPUT
        {
            ...cityC,
            previous_addresses: {
                address: [cityA, cityB],
            },
        },
        // RESULT
        [
            {
                city: cityC.city,
                country: 'US',
                date_time: {
                    low: {
                        date: TODAY,
                        precision: 'day',
                    },
                },
                state: cityC.state,
                street_lines: [cityC.street[0], cityC.street[1]],
                use: 'HP',
                zip: cityC.postalCode,
            },
            {
                city: cityA.city,
                country: 'US',
                date_time: {
                    high: {
                        date: 'undefined',
                        precision: 'day',
                    },
                    low: {
                        date: 'undefined',
                        precision: 'day',
                    },
                },
                state: cityA.state,
                street_lines: [cityA.street[0]],
                use: cityA.use,
                zip: cityA.postalCode,
            },
            {
                city: cityB.city,
                country: cityB.country,
                date_time: {
                    high: {
                        date: '2022-12-31',
                        precision: 'day',
                    },
                    low: {
                        date: '2019-01-07',
                        precision: 'day',
                    },
                },
                state: cityB.state,
                street_lines: [cityB.street[0], cityB.street[1]],
                use: cityB.use,
                zip: cityB.postalCode,
            },
        ],
    ],
    [
        // INPUT
        {
            ...cityB,
            previous_addresses: {
                address: cityC,
            },
        },
        // RESULT
        [
            {
                city: cityB.city,
                country: cityB.country,
                date_time: {
                    low: {
                        date: TODAY,
                        precision: 'day',
                    },
                },
                state: cityB.state,
                street_lines: [cityB.street[0], cityB.street[1]],
                use: 'HP',
                zip: cityB.postalCode,
            },
            {
                city: cityC.city,
                country: 'US',
                date_time: {
                    high: {
                        date: 'undefined',
                        precision: 'day',
                    },
                    low: {
                        date: '2023-05-09',
                        precision: 'day',
                    },
                },
                state: cityC.state,
                street_lines: [cityC.street[0], cityC.street[1]],
                use: cityC.use,
                zip: cityC.postalCode,
            },
        ],
    ],
];

exports.TEST_CASES = TEST_CASES;
exports.TODAY = TODAY;
