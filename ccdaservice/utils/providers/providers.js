const { fDate } = require('../date/date');
const { cleanCode } = require('../clean-code/clean-code');
const { countEntities } = require('../count-entities/count-entities');
const { NOT_INFORMED } = require('../constants');

function populateProvider(provider, documentData) {
    const oidFacility =
        documentData?.encounter_provider?.facility_oid ||
        '2.16.840.1.113883.19.5.99999.1';
    // The provider role is a maybe and will only be provided for physicians as a
    // primary care role. All other team members will id via taxonomy only and if not physicians.
    return {
        function_code: provider.physician_type ? 'PP' : '',
        date_time: {
            low: {
                date: fDate(provider.provider_since || ''),
                precision: 'tz',
            },
        },
        identity: [
            {
                root: provider.npi ? '2.16.840.1.113883.4.6' : oidFacility,
                extension: provider.npi || provider.table_id || NOT_INFORMED,
            },
        ],
        type: [
            {
                name: provider.taxonomy_description || '',
                code: cleanCode(provider.taxonomy) || '',
                code_system: '2.16.840.1.113883.6.101',
                code_system_name: 'NUCC Health Care Provider Taxonomy',
            },
        ],
        name: [
            {
                last: provider.lname || '',
                first: provider.fname || '',
            },
        ],
        address: [
            {
                street_lines: [documentData.encounter_provider.facility_street],
                city: documentData.encounter_provider.facility_city,
                state: documentData.encounter_provider.facility_state,
                zip: documentData.encounter_provider.facility_postal_code,
                country:
                    documentData.encounter_provider.facility_country_code ||
                    'US',
            },
        ],

        phone: [
            {
                number: documentData.encounter_provider.facility_phone || '',
            },
        ],
    };
}

function populateProviders(documentData) {
    const toPopulate = [documentData.primary_care_provider.provider];
    const count = countEntities(documentData.care_team.provider);
    if (count === 1) {
        toPopulate.push(documentData.care_team.provider);
    } else if (count > 1) {
        for (let i in documentData.care_team.provider) {
            toPopulate.push(documentData.care_team.provider[i]);
        }
    }
    return {
        providers: {
            date_time: {
                low: {
                    date: fDate(documentData.time_start || ''),
                    precision: 'tz',
                },
                high: {
                    date: fDate(documentData.time_end || ''),
                    precision: 'tz',
                },
            },
            code: {
                name: documentData.primary_diagnosis.text || '',
                code: cleanCode(documentData.primary_diagnosis.code || ''),
                code_system_name:
                    documentData.primary_diagnosis.code_type || '',
            },
            provider: toPopulate.map((p) => populateProvider(p, documentData)),
        },
    };
}

exports.populateProvider = populateProvider;
exports.populateProviders = populateProviders;
