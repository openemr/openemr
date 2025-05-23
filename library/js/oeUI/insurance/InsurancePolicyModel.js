/**
 * Insurance Policy Model
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
export class InsurancePolicyModel {
    id = null;
    insureruuid = null;
    pid = null;
    puuid = null;
    type = "primary";
    uuid = null;
    provider = "";

    accept_assignment = "YES";
    copay = "";
    // yes it doesn't work well if you're on the edge of a timezone for ISO but as a default its fine
    date = new Date();
    date_end = null;
    group_number = "";
    plan_name = "";
    policy_number = "";
    policy_type = "";
    subscriber_DOB = "";
    subscriber_city = "";
    subscriber_country = "USA";
    subscriber_employer = "";
    subscriber_employer_city = "";
    subscriber_employer_country = "";
    subscriber_employer_postal_code = "";
    subscriber_employer_state = "";
    subscriber_employer_street = "";
    subscriber_employer_street_line_2 = "";
    subscriber_fname = "";
    subscriber_lname = "";
    subscriber_mname = "";
    subscriber_phone = "";
    subscriber_postal_code = "";
    subscriber_relationship = "";
    subscriber_sex = "";
    subscriber_ss = "";
    subscriber_state = "";
    subscriber_street = "";
    subscriber_street_line_2 = null;

    #hasChanged = false;

    getSubscriberAddress() {
        return {
            street: this.subscriber_street || "",
            street_line_2: this.subscriber_street_line_2 || "",
            city: this.subscriber_city || "",
            state: this.subscriber_state || "",
            postal_code: this.subscriber_postal_code || ""
        };
    }

    getEmployerAddress() {
        return {
            street: this.subscriber_employer_street || "",
            street_line_2: this.subscriber_employer_street_line_2 || "",
            city: this.subscriber_employer_city || "",
            state: this.subscriber_employer_state || "",
            postal_code: this.subscriber_employer_postal_code || ""
        };
    }

    isCurrent() {
        return this.date_end === null;
    }

    validate() {
        // need to handle validation errors here.
    }

    clone() {
        let newPolicy = new InsurancePolicyModel();
        newPolicy.populate(this);
        return newPolicy;
    }

    isCurrentPolicy() {
        return this.id !== null && this.date_end === null;
    }

    #getValidFormattedDateFromServerDate(date) {
        if (date) {
            if (date instanceof Date) {
                let newDate = new Date(date.getTime());
                newDate.setHours(0,0,0,0);
                return newDate;
            }
            if (typeof date === 'string' && date != "0000-00-00") {
                // need to format the date from the server of ISO8601 to the localized date format
                return new Date(date + "T00:00:00");
            }
        }
        return null;
    }
    getPatientDataFields() {
        return [
            'subscriber_fname',
            'subscriber_mname',
            'subscriber_lname',
            'subscriber_DOB',
            'subscriber_sex',
            'subscriber_ss',
            'subscriber_street',
            'subscriber_street_line_2',
            'subscriber_city',
            'subscriber_state',
            'subscriber_postal_code',
            'subscriber_phone',
            'subscriber_country',
            'subscriber_employer',
            'subscriber_employer_street',
            'subscriber_employer_street_line_2',
            'subscriber_employer_city',
            'subscriber_employer_state',
            'subscriber_employer_postal_code',
            'subscriber_employer_country'
        ];
    }

    populateWithPatientData(patientData) {
        if (typeof patientData === 'object') {
            let employers = patientData.employer || [];
            let employer = employers.length > 0 ? employers[0] : {};

            let dataFields = this.getPatientDataFields();
            dataFields.forEach((key) => {
                let parts = key.split('_');
                let property = '';
                let value = '';
                if (parts[1] == 'employer') {
                    if (parts.length == 2) {
                        property = 'name';
                    } else {
                        property = parts.slice(2).join('_');
                    }
                    value = employer[property] ?? null;
                } else {
                    property = parts.slice(1).join('_');
                    // mapping of the data fields to the patient data fields
                    if (property == 'country') {
                        property = 'country_code';
                    }
                    value = patientData[property] ?? null;
                }
                if (property == 'DOB' && value) {
                    value = this.#getValidFormattedDateFromServerDate(value);
                }
                this.setProperty(key, value);
            });
        }
    }
    isPatientDataField(field) {
        let dataFields = this.getPatientDataFields();
        return dataFields.includes(field);
    }

    populate(pojo, markAsUnchanged = false) {
        if (typeof pojo === 'object') {
            // we use setProperty here in order to make sure we can track if the value has changed
            let objectKeys = Object.keys(pojo);
            objectKeys.forEach((key) => {
                if (['date', 'date_end', 'subscriber_DOB','accept_assignment'].includes(key)) {
                    return;
                }
                this.setProperty(key, pojo[key]);
            });
            // Object.assign(this, pojo);
            // server stores this as a TRUE/FALSE string which is... odd
            if (pojo.accept_assignment == 'FALSE') {
                this.setProperty('accept_assignment', "NO");
            } else {
                this.setProperty('accept_assignment', "YES");  // default to yes for everything
            }
            this.setProperty('date', this.#getValidFormattedDateFromServerDate(pojo.date));
            this.setProperty('date_end', this.#getValidFormattedDateFromServerDate(pojo.date_end));
            this.setProperty('subscriber_DOB', this.#getValidFormattedDateFromServerDate(pojo.subscriber_DOB));
            if (markAsUnchanged) {
                this.#hasChanged = false;
            }
        }
    }
    getDataForSave() {
        let data = Object.assign({}, this);
        // wierd way of how the server saves the data.
        if (data.accept_assignment == 'YES') {
            data.accept_assignment = "TRUE";
        } else {
            data.accept_assignment = "FALSE";
        }
        if (this.date) {
            data.date = this.date.toISOString().slice(0,10);
        }
        if (this.date_end) {
            data.date_end = this.date_end.toISOString().slice(0,10);
        }
        if (this.subscriber_DOB) {
            data.subscriber_DOB = this.subscriber_DOB.toISOString().slice(0,10);
        }
        // clear out this property so it doesn't get saved.
        if (data['#hasChanged']) {
            delete data['#hasChanged'];
        }
        return data;
    }
    setProperty(key, value) {
        if (typeof this[key] !== 'undefined') {
            if (this[key] !== value) {
                this.#hasChanged = true;
            }
            this[key] = value;
        }
    }

    hasChanged() {
        return this.#hasChanged;
    }
    setEffectiveEndDate(date) {
        this.date_end = date;
        this.#hasChanged = true;
    }

    setId(id) {
        this.id = id;
        this.#hasChanged = true;
    }

    setUuid(uuid) {
        this.uuid = uuid;
        this.#hasChanged = true;
    }

    toString() {
        const dateFormatSettingYMD = 0;
        let names = [this.plan_name || "", " "];
        if (this.date) {
            names.push(window.top.oeFormatters.I18NDateFormat(this.date, dateFormatSettingYMD));
        } else {
            names.push(window.top.xl("No start date"));
        }
        if (this.date_end) {
            names.push(" - ");
            names.push(window.top.oeFormatters.I18NDateFormat(this.date_end, dateFormatSettingYMD));
        } else {
            names.push(" - ");
            names.push(window.top.xl("Current"));
        }
        return names.join("");
    }
}
