const { NOT_INFORMED, NULL_FLAVOR } = require("../constants");

const TODAY = '2021-04-10T18:55:55.301Z';

const documentData = {
    encounter_provider: {
        facility_oid: 'mock_facility_oid',
        facility_street: 'mock_facility_street',
        facility_city: 'mock_facility_city',
        facility_state: 'mock_facility_state',
        facility_postal_code: 'mock_facility_postal_code',
        facility_country_code: 'mock_facility_country_code',
        facility_phone: 'mock_facility_phone',
    },
};

const provider = {
    physician_type: 'mock_physician_type',
    provider_since: '07/05/2010',
    npi: 'mock_npi',
    taxonomy: 'mock_taxonomy',
    taxonomy_description: 'mock_taxonomy_description',
    fname: 'mock_provider_fname',
    lname: 'mock_provider_lname',
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

exports.TODAY = TODAY;
exports.PROVIDER_TEST_DATA = PROVIDER_TEST_DATA;
