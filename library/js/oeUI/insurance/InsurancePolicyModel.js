/**
 * Insurance Policy Model
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Discover and Change, Inc. <snielson@discoverandchange.com>
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
        if (date && typeof date === 'string' && date != "0000-00-00") {
            // need to format the date from the server of ISO8601 to the localized date format
            return new Date(date + "T00:00:00");
        }
        return null;
    }

    populate(pojo) {
        if (typeof pojo === 'object') {
            // TODO: if we need to do any validation we could do that here
            Object.assign(this, pojo);
            // server stores this as a TRUE/FALSE string which is... odd
            if (pojo.accept_assignment == 'FALSE') {
                this.accept_assignment = "NO";
            } else {
                this.accept_assignment = "YES"; // default to yes for everything
            }
            this.date = this.#getValidFormattedDateFromServerDate(pojo.date);
            this.date_end = this.#getValidFormattedDateFromServerDate(pojo.date_end);
            this.subscriber_DOB = this.#getValidFormattedDateFromServerDate(pojo.subscriber_DOB);
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
        return data;
    }
    setEffectiveEndDate(date) {
        this.date_end = date;
    }

    setId(id) {
        this.id = id;
    }

    setUuid(uuid) {
        this.uuid = uuid;
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
    }s
}
