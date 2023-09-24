const { NOT_INFORMED, NULL_FLAVOR } = require('../constants');

const TODAY = '2021-04-10T18:55:55.301Z';

function getMockProvider(provider_since, text_modifier = '') {
    return {
        physician_type: 'mock_physician_type' + text_modifier,
        provider_since,
        npi: 'mock_npi' + text_modifier,
        taxonomy: 'mock_taxonomy' + text_modifier,
        taxonomy_description: 'mock_taxonomy_description' + text_modifier,
        fname: 'mock_provider_fname' + text_modifier,
        lname: 'mock_provider_lname' + text_modifier,
    };
}

const provider = getMockProvider('07/05/2010');

const documentData = {
    primary_care_provider: { provider },
    time_start: '2000-01-01',
    time_end: '2015-12-10',
    encounter_provider: {
        facility_oid: 'mock_facility_oid',
        facility_street: 'mock_facility_street',
        facility_city: 'mock_facility_city',
        facility_state: 'mock_facility_state',
        facility_postal_code: 'mock_facility_postal_code',
        facility_country_code: 'mock_facility_country_code',
        facility_phone: 'mock_facility_phone',
    },
    primary_diagnosis: {
        text: 'mock_primary_diagnosis_text',
        code: 'mock_primary_diagnosis_code',
        code_type: 'mock_primary_diagnosis_code_type',
    },
};

const providerResult = {
    address: [
        {
            city: documentData.encounter_provider.facility_city,
            country: documentData.encounter_provider.facility_country_code,
            state: documentData.encounter_provider.facility_state,
            street_lines: [documentData.encounter_provider.facility_street],
            zip: documentData.encounter_provider.facility_postal_code,
        },
    ],
    date_time: {
        low: {
            date: '2010-07-05',
            precision: 'tz',
        },
    },
    function_code: 'PP',
    identity: [
        {
            extension: provider.npi,
            root: '2.16.840.1.113883.4.6',
        },
    ],
    name: [
        {
            first: provider.fname,
            last: provider.lname,
        },
    ],
    phone: [
        {
            number: documentData.encounter_provider.facility_phone,
        },
    ],
    type: [
        {
            code: provider.taxonomy,
            code_system: '2.16.840.1.113883.6.101',
            code_system_name: 'NUCC Health Care Provider Taxonomy',
            name: provider.taxonomy_description,
        },
    ],
};

const PROVIDER_TEST_DATA = [
    [
        // INPUT
        provider,
        documentData,
        // RESULT
        providerResult,
    ],
    [
        // INPUT
        {
            ...provider,
            npi: undefined,
            table_id: undefined,
        },
        documentData,
        // RESULT
        {
            ...providerResult,
            identity: [
                {
                    extension: NOT_INFORMED,
                    root: documentData.encounter_provider.facility_oid,
                },
            ],
        },
    ],
    [
        // INPUT
        {
            ...provider,
            table_id: 'mock_table_id',
            physician_type: undefined,
            provider_since: undefined,
            npi: undefined,
            taxonomy: undefined,
            taxonomy_description: undefined,
            fname: undefined,
            lname: undefined,
        },
        {
            encounter_provider: {
                ...documentData.encounter_provider,
                facility_oid: undefined,
                facility_country_code: undefined,
                facility_phone: undefined,
            },
        },
        // RESULT
        {
            address: [
                {
                    city: documentData.encounter_provider.facility_city,
                    country: 'US',
                    state: documentData.encounter_provider.facility_state,
                    street_lines: [
                        documentData.encounter_provider.facility_street,
                    ],
                    zip: documentData.encounter_provider.facility_postal_code,
                },
            ],
            date_time: {
                low: { date: TODAY, precision: 'tz' },
            },
            function_code: '',
            identity: [
                {
                    extension: 'mock_table_id',
                    root: '2.16.840.1.113883.19.5.99999.1',
                },
            ],
            name: [{ first: '', last: '' }],
            phone: [{ number: '' }],
            type: [
                {
                    code: NULL_FLAVOR,
                    code_system: '2.16.840.1.113883.6.101',
                    code_system_name: 'NUCC Health Care Provider Taxonomy',
                    name: '',
                },
            ],
        },
    ],
];

const provider2 = getMockProvider('08/05/2010', '_2');
const provider3 = getMockProvider('08/05/2010', '_3');

const PROVIDERS_TEST_DATA = [
    [
        // INPUT
        {
            ...documentData,
            care_team: { provider: provider2 },
        },
        // RESULT
        {
            providers: {
                code: {
                    code: documentData.primary_diagnosis.code,
                    code_system_name: documentData.primary_diagnosis.code_type,
                    name: documentData.primary_diagnosis.text,
                },
                date_time: {
                    high: {
                        date: '2015-12-10',
                        precision: 'tz',
                    },
                    low: {
                        date: '2000-01-01',
                        precision: 'tz',
                    },
                },
                provider: [
                    {
                        address: [
                            {
                                city: documentData.encounter_provider
                                    .facility_city,
                                country:
                                    documentData.encounter_provider
                                        .facility_country_code,
                                state: documentData.encounter_provider
                                    .facility_state,
                                street_lines: [
                                    documentData.encounter_provider
                                        .facility_street,
                                ],
                                zip: documentData.encounter_provider
                                    .facility_postal_code,
                            },
                        ],
                        date_time: {
                            low: {
                                date: '2010-07-05',
                                precision: 'tz',
                            },
                        },
                        function_code: 'PP',
                        identity: [
                            {
                                extension: provider.npi,
                                root: '2.16.840.1.113883.4.6',
                            },
                        ],
                        name: [
                            {
                                first: provider.fname,
                                last: provider.lname,
                            },
                        ],
                        phone: [
                            {
                                number: documentData.encounter_provider
                                    .facility_phone,
                            },
                        ],
                        type: [
                            {
                                code: provider.taxonomy,
                                code_system: '2.16.840.1.113883.6.101',
                                code_system_name:
                                    'NUCC Health Care Provider Taxonomy',
                                name: provider.taxonomy_description,
                            },
                        ],
                    },
                    {
                        address: [
                            {
                                city: documentData.encounter_provider
                                    .facility_city,
                                country:
                                    documentData.encounter_provider
                                        .facility_country_code,
                                state: documentData.encounter_provider
                                    .facility_state,
                                street_lines: [
                                    documentData.encounter_provider
                                        .facility_street,
                                ],
                                zip: documentData.encounter_provider
                                    .facility_postal_code,
                            },
                        ],
                        date_time: {
                            low: {
                                date: '2010-08-05',
                                precision: 'tz',
                            },
                        },
                        function_code: 'PP',
                        identity: [
                            {
                                extension: provider2.npi,
                                root: '2.16.840.1.113883.4.6',
                            },
                        ],
                        name: [
                            {
                                first: provider2.fname,
                                last: provider2.lname,
                            },
                        ],
                        phone: [
                            {
                                number: documentData.encounter_provider
                                    .facility_phone,
                            },
                        ],
                        type: [
                            {
                                code: provider2.taxonomy,
                                code_system: '2.16.840.1.113883.6.101',
                                code_system_name:
                                    'NUCC Health Care Provider Taxonomy',
                                name: provider2.taxonomy_description,
                            },
                        ],
                    },
                ],
            },
        },
    ],
    [
        // INPUT
        {
            ...documentData,
            time_start: undefined,
            time_end: undefined,
            primary_diagnosis: {},
            care_team: {
                provider: [provider2, provider3],
            },
        },
        // RESULT
        {
            providers: {
                code: {
                    code: NULL_FLAVOR,
                    code_system_name: '',
                    name: '',
                },
                date_time: {
                    high: {
                        date: TODAY,
                        precision: 'tz',
                    },
                    low: {
                        date: TODAY,
                        precision: 'tz',
                    },
                },
                provider: [
                    {
                        address: [
                            {
                                city: documentData.encounter_provider
                                    .facility_city,
                                country:
                                    documentData.encounter_provider
                                        .facility_country_code,
                                state: documentData.encounter_provider
                                    .facility_state,
                                street_lines: [
                                    documentData.encounter_provider
                                        .facility_street,
                                ],
                                zip: documentData.encounter_provider
                                    .facility_postal_code,
                            },
                        ],
                        date_time: {
                            low: {
                                date: '2010-07-05',
                                precision: 'tz',
                            },
                        },
                        function_code: 'PP',
                        identity: [
                            {
                                extension: provider.npi,
                                root: '2.16.840.1.113883.4.6',
                            },
                        ],
                        name: [
                            {
                                first: provider.fname,
                                last: provider.lname,
                            },
                        ],
                        phone: [
                            {
                                number: documentData.encounter_provider
                                    .facility_phone,
                            },
                        ],
                        type: [
                            {
                                code: provider.taxonomy,
                                code_system: '2.16.840.1.113883.6.101',
                                code_system_name:
                                    'NUCC Health Care Provider Taxonomy',
                                name: provider.taxonomy_description,
                            },
                        ],
                    },
                    {
                        address: [
                            {
                                city: documentData.encounter_provider
                                    .facility_city,
                                country:
                                    documentData.encounter_provider
                                        .facility_country_code,
                                state: documentData.encounter_provider
                                    .facility_state,
                                street_lines: [
                                    documentData.encounter_provider
                                        .facility_street,
                                ],
                                zip: documentData.encounter_provider
                                    .facility_postal_code,
                            },
                        ],
                        date_time: {
                            low: {
                                date: '2010-08-05',
                                precision: 'tz',
                            },
                        },
                        function_code: 'PP',
                        identity: [
                            {
                                extension: provider2.npi,
                                root: '2.16.840.1.113883.4.6',
                            },
                        ],
                        name: [
                            {
                                first: provider2.fname,
                                last: provider2.lname,
                            },
                        ],
                        phone: [
                            {
                                number: documentData.encounter_provider
                                    .facility_phone,
                            },
                        ],
                        type: [
                            {
                                code: provider2.taxonomy,
                                code_system: '2.16.840.1.113883.6.101',
                                code_system_name:
                                    'NUCC Health Care Provider Taxonomy',
                                name: provider2.taxonomy_description,
                            },
                        ],
                    },
                    {
                        address: [
                            {
                                city: documentData.encounter_provider
                                    .facility_city,
                                country:
                                    documentData.encounter_provider
                                        .facility_country_code,
                                state: documentData.encounter_provider
                                    .facility_state,
                                street_lines: [
                                    documentData.encounter_provider
                                        .facility_street,
                                ],
                                zip: documentData.encounter_provider
                                    .facility_postal_code,
                            },
                        ],
                        date_time: {
                            low: {
                                date: '2010-08-05',
                                precision: 'tz',
                            },
                        },
                        function_code: 'PP',
                        identity: [
                            {
                                extension: provider3.npi,
                                root: '2.16.840.1.113883.4.6',
                            },
                        ],
                        name: [
                            {
                                first: provider3.fname,
                                last: provider3.lname,
                            },
                        ],
                        phone: [
                            {
                                number: documentData.encounter_provider
                                    .facility_phone,
                            },
                        ],
                        type: [
                            {
                                code: provider3.taxonomy,
                                code_system: '2.16.840.1.113883.6.101',
                                code_system_name:
                                    'NUCC Health Care Provider Taxonomy',
                                name: provider3.taxonomy_description,
                            },
                        ],
                    },
                ],
            },
        },
    ],
];

exports.TODAY = TODAY;
exports.PROVIDER_TEST_DATA = PROVIDER_TEST_DATA;
exports.PROVIDERS_TEST_DATA = PROVIDERS_TEST_DATA;
