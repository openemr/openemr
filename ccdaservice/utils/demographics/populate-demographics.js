'use strict';

const { fetchPreviousAddresses } = require('./previous-addresses');
const { fDate } = require('../date/date');
const {
    NOT_INFORMED,
    NULL_FLAVOR,
    DECLINED_TO_SPECIFY,
} = require('../constants');

function getGuardianNames(display_name) {
    const parts = display_name.split(' ');
    return parts.length === 3
        ? [{ first: parts[0], last: parts[2] }]
        : parts.length === 2
        ? [{ first: parts[0], last: parts[1] }]
        : [{ first: NOT_INFORMED, last: NOT_INFORMED }];
}

function getGuardianInfo(guardian) {
    return [
        {
            relation: guardian.relation,
            addresses: [
                {
                    street_lines: [guardian.address],
                    city: guardian.city,
                    state: guardian.state,
                    zip: guardian.postalCode,
                    country: guardian.country || 'US',
                    use: 'primary home',
                },
            ],
            names: getGuardianNames(guardian.display_name),
            phone: [
                {
                    number: guardian.telecom,
                    type: 'primary home',
                },
            ],
        },
    ];
}

function setNullFlavorIfUnspecifiedOrEmpty(patient, property) {
    if (patient[property] === DECLINED_TO_SPECIFY || patient[property] === '') {
        patient[property] = NULL_FLAVOR;
    }
}

function getLanguageCode(patient) {
    return patient.language === 'English'
        ? 'en-US'
        : patient.language === 'Spanish'
            ? 'sp-US'
            : 'en-US';
}

function getNpiFacility(documentData, useFallback) {
    return useFallback
    ? documentData.encounter_provider.facility_npi || NOT_INFORMED
    : documentData.encounter_provider.facility_npi;
}

function populateDemographics(documentData, npiFacility) {
    const patient = documentData.patient;
    const guardian = documentData.guardian;
    const oidFacility =
        documentData.encounter_provider.facility_oid ||
        '2.16.840.1.113883.19.5.99999.1';

    setNullFlavorIfUnspecifiedOrEmpty(patient, 'race');
    setNullFlavorIfUnspecifiedOrEmpty(patient, 'race_group');
    setNullFlavorIfUnspecifiedOrEmpty(patient, 'ethnicity');

    return {
        name: {
            prefix: patient.prefix,
            suffix: patient.suffix,
            middle: [patient.mname],
            last: patient.lname,
            first: patient.fname,
        },
        birth_name: {
            middle: patient.birth_mname || '',
            last: patient.birth_lname || '',
            first: patient.birth_fname || '',
        },
        dob: {
            point: {
                date: fDate(patient.dob),
                precision: 'day',
            },
        },
        gender: patient.gender.toUpperCase() || NULL_FLAVOR,
        identifiers: [
            {
                identifier: oidFacility || npiFacility,
                extension: patient.uuid,
            },
        ],
        marital_status: patient.status.toUpperCase(),
        addresses: fetchPreviousAddresses(patient),
        phone: [
            {
                number: patient.phone_home,
                type: 'primary home',
            },
            {
                number: patient.phone_mobile,
                type: 'primary mobile',
            },
            {
                number: patient.phone_work,
                type: 'work place',
            },
            {
                number: patient.phone_emergency,
                type: 'emergency contact',
            },
            {
                email: patient.email,
                type: 'contact_email',
            },
        ],
        ethnicity: patient.ethnicity || '',
        race: patient.race || NULL_FLAVOR,
        race_additional: patient.race_group || NULL_FLAVOR,
        languages: [
            {
                language: getLanguageCode(patient),
                preferred: true,
                mode: 'Expressed spoken',
                proficiency: 'Good',
            },
        ],
        attributed_provider: {
            identity: [
                {
                    root: '2.16.840.1.113883.4.6',
                    extension: npiFacility || '',
                },
            ],
            phone: [
                {
                    number:
                        documentData.encounter_provider.facility_phone || '',
                },
            ],
            name: [
                {
                    full: documentData.encounter_provider.facility_name || '',
                },
            ],
            address: [
                {
                    street_lines: [
                        documentData.encounter_provider.facility_street,
                    ],
                    city: documentData.encounter_provider.facility_city,
                    state: documentData.encounter_provider.facility_state,
                    zip: documentData.encounter_provider.facility_postal_code,
                    country:
                        documentData.encounter_provider.facility_country_code ||
                        'US',
                    use: 'work place',
                },
            ],
        },
        // not required
        guardians: guardian.display_name ? getGuardianInfo(guardian) : '',
    };
}

exports.populateDemographics = populateDemographics;
exports.getNpiFacility = getNpiFacility;
