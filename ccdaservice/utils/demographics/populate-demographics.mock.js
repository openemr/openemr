const MOCK_ADDRESS = require('./previous-addresses.mock').TEST_CASES[0][0];
const { fDate } = require('../date/date');
const {
    NULL_FLAVOR,
    DECLINED_TO_SPECIFY,
    NOT_INFORMED,
} = require('../constants');
const TODAY = '2021-04-06T18:55:55.301Z';

const mockPatient = {
    race: 'mock_race',
    race_group: 'mock_race_group',
    ethnicity: 'mock_ethnicity',
    prefix: 'Ms',
    fname: 'Catherine',
    mname: 'Zeta',
    lname: 'Jones',
    suffix: 'The daughter of dragons',
    birth_mname: 'Not at all',
    birth_lname: 'Famous',
    birth_fname: 'Lilly',
    dob: '1969-09-25',
    gender: 'female',
    uuid: '052698',
    status: 'married',
    phone_home: '111-1111',
    phone_mobile: '222-2222',
    phone_work: '333-3333',
    phone_emergency: '444-4444',
    email: 'cathy@example.com',
    language: '',
    ...MOCK_ADDRESS,
};

const mockGuardian = {
    display_name: 'Mary Elizabeth Mcglynn',
    relation: 'sister',
    city: 'Borgo Marino',
    address: 'Borgo Lamberto 942',
    state: 'Pavia',
    country: 'IT',
    postalCode: '65210',
    telecom: '4589-63254',
};

const twoPartsName = 'Lauren Cohan';

const encounter_provider = {
    facility_phone: '555-5555',
    facility_name: 'The Easy Lab',
    facility_street: '88792 Myrna Point',
    facility_city: 'Riley Causeway',
    facility_state: 'Hawaii',
    facility_postal_code: '93789-5373',
    facility_country_code: 'US',
    facility_npi: 'mock_npi',
    facility_oid: 'mock_oid',
};

const TEST_CASES = [
    [
        // INPUT
        { patient: mockPatient, guardian: mockGuardian, encounter_provider },
        // RESULT
        {
            addresses: [
                {
                    city: MOCK_ADDRESS.city,
                    country: MOCK_ADDRESS.country,
                    date_time: {
                        low: {
                            date: TODAY,
                            precision: 'day',
                        },
                    },
                    state: MOCK_ADDRESS.state,
                    street_lines: MOCK_ADDRESS.street,
                    use: 'HP',
                    zip: MOCK_ADDRESS.postalCode,
                },
                {
                    city: MOCK_ADDRESS.previous_addresses.address[0].city,
                    country: MOCK_ADDRESS.previous_addresses.address[0].country,
                    date_time: {
                        high: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[0]
                                    .period_end
                            ),
                            precision: 'day',
                        },
                        low: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[0]
                                    .period_start
                            ),
                            precision: 'day',
                        },
                    },
                    state: MOCK_ADDRESS.previous_addresses.address[0].state,
                    street_lines:
                        MOCK_ADDRESS.previous_addresses.address[0].street,
                    use: MOCK_ADDRESS.previous_addresses.address[0].use,
                    zip: MOCK_ADDRESS.previous_addresses.address[0].postalCode,
                },
                {
                    city: MOCK_ADDRESS.previous_addresses.address[1].city,
                    country: 'US',
                    date_time: {
                        high: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[1]
                                    .period_end
                            ),
                            precision: 'day',
                        },
                        low: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[1]
                                    .period_start
                            ),
                            precision: 'day',
                        },
                    },
                    state: MOCK_ADDRESS.previous_addresses.address[1].state,
                    street_lines:
                        MOCK_ADDRESS.previous_addresses.address[1].street,
                    use: MOCK_ADDRESS.previous_addresses.address[1].use,
                    zip: MOCK_ADDRESS.previous_addresses.address[1].postalCode,
                },
            ],
            attributed_provider: {
                address: [
                    {
                        city: encounter_provider.facility_city,
                        country: encounter_provider.facility_country_code,
                        state: encounter_provider.facility_state,
                        street_lines: [encounter_provider.facility_street],
                        use: 'work place',
                        zip: encounter_provider.facility_postal_code,
                    },
                ],
                identity: [
                    {
                        extension: encounter_provider.facility_npi,
                        root: '2.16.840.1.113883.4.6',
                    },
                ],
                name: [{ full: encounter_provider.facility_name }],
                phone: [{ number: encounter_provider.facility_phone }],
            },
            birth_name: {
                first: mockPatient.birth_fname,
                last: mockPatient.birth_lname,
                middle: mockPatient.birth_mname,
            },
            dob: { point: { date: mockPatient.dob, precision: 'day' } },
            ethnicity: mockPatient.ethnicity,
            gender: mockPatient.gender.toUpperCase(),
            guardians: [
                {
                    addresses: [
                        {
                            city: mockGuardian.city,
                            country: mockGuardian.country,
                            state: mockGuardian.state,
                            street_lines: [mockGuardian.address],
                            use: 'primary home',
                            zip: mockGuardian.postalCode,
                        },
                    ],
                    names: [
                        {
                            first: mockGuardian.display_name.split(' ')[0],
                            last: mockGuardian.display_name.split(' ')[2],
                        },
                    ],
                    phone: [
                        { number: mockGuardian.telecom, type: 'primary home' },
                    ],
                    relation: mockGuardian.relation,
                },
            ],
            identifiers: [
                {
                    extension: mockPatient.uuid,
                    identifier: encounter_provider.facility_oid,
                },
            ],
            languages: [
                {
                    language: 'en-US',
                    mode: 'Expressed spoken',
                    preferred: true,
                    proficiency: 'Good',
                },
            ],
            marital_status: mockPatient.status.toUpperCase(),
            name: {
                first: mockPatient.fname,
                last: mockPatient.lname,
                middle: [mockPatient.mname],
                prefix: mockPatient.prefix,
                suffix: mockPatient.suffix,
            },
            phone: [
                { number: mockPatient.phone_home, type: 'primary home' },
                { number: mockPatient.phone_mobile, type: 'primary mobile' },
                { number: mockPatient.phone_work, type: 'work place' },
                {
                    number: mockPatient.phone_emergency,
                    type: 'emergency contact',
                },
                { email: mockPatient.email, type: 'contact_email' },
            ],
            race: mockPatient.race,
            race_additional: mockPatient.race_group,
        },
    ],
    [
        // INPUT
        {
            patient: {
                ...mockPatient,
                mname: '',
                language: 'English',
                race: DECLINED_TO_SPECIFY,
                race_group: DECLINED_TO_SPECIFY,
                ethnicity: DECLINED_TO_SPECIFY,
            },
            guardian: { display_name: twoPartsName },
            encounter_provider: {
                ...encounter_provider,
                facility_oid: '',
                facility_npi: '',
                facility_phone: '',
                facility_name: '',
                facility_country_code: '',
            },
        },
        // RESULT
        {
            addresses: [
                {
                    city: MOCK_ADDRESS.city,
                    country: MOCK_ADDRESS.country,
                    date_time: {
                        low: {
                            date: TODAY,
                            precision: 'day',
                        },
                    },
                    state: MOCK_ADDRESS.state,
                    street_lines: MOCK_ADDRESS.street,
                    use: 'HP',
                    zip: MOCK_ADDRESS.postalCode,
                },
                {
                    city: MOCK_ADDRESS.previous_addresses.address[0].city,
                    country: MOCK_ADDRESS.previous_addresses.address[0].country,
                    date_time: {
                        high: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[0]
                                    .period_end
                            ),
                            precision: 'day',
                        },
                        low: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[0]
                                    .period_start
                            ),
                            precision: 'day',
                        },
                    },
                    state: MOCK_ADDRESS.previous_addresses.address[0].state,
                    street_lines:
                        MOCK_ADDRESS.previous_addresses.address[0].street,
                    use: MOCK_ADDRESS.previous_addresses.address[0].use,
                    zip: MOCK_ADDRESS.previous_addresses.address[0].postalCode,
                },
                {
                    city: MOCK_ADDRESS.previous_addresses.address[1].city,
                    country: 'US',
                    date_time: {
                        high: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[1]
                                    .period_end
                            ),
                            precision: 'day',
                        },
                        low: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[1]
                                    .period_start
                            ),
                            precision: 'day',
                        },
                    },
                    state: MOCK_ADDRESS.previous_addresses.address[1].state,
                    street_lines:
                        MOCK_ADDRESS.previous_addresses.address[1].street,
                    use: MOCK_ADDRESS.previous_addresses.address[1].use,
                    zip: MOCK_ADDRESS.previous_addresses.address[1].postalCode,
                },
            ],
            attributed_provider: {
                address: [
                    {
                        city: encounter_provider.facility_city,
                        country: encounter_provider.facility_country_code,
                        state: encounter_provider.facility_state,
                        street_lines: [encounter_provider.facility_street],
                        use: 'work place',
                        zip: encounter_provider.facility_postal_code,
                    },
                ],
                identity: [
                    { extension: NOT_INFORMED, root: '2.16.840.1.113883.4.6' },
                ],
                name: [{ full: '' }],
                phone: [{ number: '' }],
            },
            birth_name: {
                first: mockPatient.birth_fname,
                last: mockPatient.birth_lname,
                middle: mockPatient.birth_mname,
            },
            dob: { point: { date: mockPatient.dob, precision: 'day' } },
            ethnicity: NULL_FLAVOR,
            gender: mockPatient.gender.toUpperCase(),
            guardians: [
                {
                    addresses: [
                        {
                            city: undefined,
                            country: 'US',
                            state: undefined,
                            street_lines: [undefined],
                            use: 'primary home',
                            zip: undefined,
                        },
                    ],
                    names: [
                        {
                            first: twoPartsName.split(' ')[0],
                            last: twoPartsName.split(' ')[1],
                        },
                    ],
                    phone: [{ number: undefined, type: 'primary home' }],
                    relation: undefined,
                },
            ],
            identifiers: [
                {
                    extension: mockPatient.uuid,
                    identifier: '2.16.840.1.113883.19.5.99999.1',
                },
            ],
            languages: [
                {
                    language: 'en-US',
                    mode: 'Expressed spoken',
                    preferred: true,
                    proficiency: 'Good',
                },
            ],
            marital_status: mockPatient.status.toUpperCase(),
            name: {
                first: mockPatient.fname,
                last: mockPatient.lname,
                middle: [''],
                prefix: mockPatient.prefix,
                suffix: mockPatient.suffix,
            },
            phone: [
                { number: mockPatient.phone_home, type: 'primary home' },
                { number: mockPatient.phone_mobile, type: 'primary mobile' },
                { number: mockPatient.phone_work, type: 'work place' },
                {
                    number: mockPatient.phone_emergency,
                    type: 'emergency contact',
                },
                { email: mockPatient.email, type: 'contact_email' },
            ],
            race: NULL_FLAVOR,
            race_additional: NULL_FLAVOR,
        },
    ],
    [
        // INPUT
        {
            patient: {
                ...mockPatient,
                language: 'Spanish',
                race: undefined,
                race_group: undefined,
                ethnicity: undefined,
                gender: '',
                birth_fname: undefined,
                birth_lname: undefined,
                birth_mname: undefined,
            },
            guardian: { display_name: '' },
            encounter_provider,
        },
        // RESULT
        {
            addresses: [
                {
                    city: MOCK_ADDRESS.city,
                    country: MOCK_ADDRESS.country,
                    date_time: {
                        low: {
                            date: TODAY,
                            precision: 'day',
                        },
                    },
                    state: MOCK_ADDRESS.state,
                    street_lines: MOCK_ADDRESS.street,
                    use: 'HP',
                    zip: MOCK_ADDRESS.postalCode,
                },
                {
                    city: MOCK_ADDRESS.previous_addresses.address[0].city,
                    country: MOCK_ADDRESS.previous_addresses.address[0].country,
                    date_time: {
                        high: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[0]
                                    .period_end
                            ),
                            precision: 'day',
                        },
                        low: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[0]
                                    .period_start
                            ),
                            precision: 'day',
                        },
                    },
                    state: MOCK_ADDRESS.previous_addresses.address[0].state,
                    street_lines:
                        MOCK_ADDRESS.previous_addresses.address[0].street,
                    use: MOCK_ADDRESS.previous_addresses.address[0].use,
                    zip: MOCK_ADDRESS.previous_addresses.address[0].postalCode,
                },
                {
                    city: MOCK_ADDRESS.previous_addresses.address[1].city,
                    country: 'US',
                    date_time: {
                        high: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[1]
                                    .period_end
                            ),
                            precision: 'day',
                        },
                        low: {
                            date: fDate(
                                MOCK_ADDRESS.previous_addresses.address[1]
                                    .period_start
                            ),
                            precision: 'day',
                        },
                    },
                    state: MOCK_ADDRESS.previous_addresses.address[1].state,
                    street_lines:
                        MOCK_ADDRESS.previous_addresses.address[1].street,
                    use: MOCK_ADDRESS.previous_addresses.address[1].use,
                    zip: MOCK_ADDRESS.previous_addresses.address[1].postalCode,
                },
            ],
            attributed_provider: {
                address: [
                    {
                        city: encounter_provider.facility_city,
                        country: encounter_provider.facility_country_code,
                        state: encounter_provider.facility_state,
                        street_lines: [encounter_provider.facility_street],
                        use: 'work place',
                        zip: encounter_provider.facility_postal_code,
                    },
                ],
                identity: [
                    {
                        extension: encounter_provider.facility_npi,
                        root: '2.16.840.1.113883.4.6',
                    },
                ],
                name: [{ full: encounter_provider.facility_name }],
                phone: [{ number: encounter_provider.facility_phone }],
            },
            birth_name: { first: '', last: '', middle: '' },
            dob: { point: { date: mockPatient.dob, precision: 'day' } },
            ethnicity: '',
            gender: NULL_FLAVOR,
            guardians: '',
            identifiers: [
                {
                    extension: mockPatient.uuid,
                    identifier: encounter_provider.facility_oid,
                },
            ],
            languages: [
                {
                    language: 'sp-US',
                    mode: 'Expressed spoken',
                    preferred: true,
                    proficiency: 'Good',
                },
            ],
            marital_status: mockPatient.status.toUpperCase(),
            name: {
                first: mockPatient.fname,
                last: mockPatient.lname,
                middle: [mockPatient.mname],
                prefix: mockPatient.prefix,
                suffix: mockPatient.suffix,
            },
            phone: [
                { number: mockPatient.phone_home, type: 'primary home' },
                { number: mockPatient.phone_mobile, type: 'primary mobile' },
                { number: mockPatient.phone_work, type: 'work place' },
                {
                    number: mockPatient.phone_emergency,
                    type: 'emergency contact',
                },
                { email: mockPatient.email, type: 'contact_email' },
            ],
            race: NULL_FLAVOR,
            race_additional: NULL_FLAVOR,
        },
    ],
];

exports.TEST_CASES = TEST_CASES;
exports.TODAY = TODAY;
