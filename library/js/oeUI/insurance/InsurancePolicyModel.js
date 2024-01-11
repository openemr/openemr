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

    accept_assignment = "yes";
    copay = "";
    // yes it doesn't work well if you're on the edge of a timezone for ISO but as a default its fine
    date = new Date().toISOString().slice(0,10); // grab YYYY-MM-DD
    date_end = null;
    group_number = "";
    plan_name = "";
    policy_number = "";
    policy_type = "";
    subscriber_DOB = "";
    subscriber_city = "";
    subscriber_country = "";
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

    populate(pojo) {
        if (typeof pojo === 'object') {
            // TODO: if we need to do any validation we could do that here
            Object.assign(this, pojo);
            // server stores this as a TRUE/FALSE string which is... odd
            if (pojo.accept_assignment == 'TRUE') {
                this.accept_assignment = "yes";
            } else {
                this.accept_assignment = "no";
            }
        }
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
        let names = [this.plan_name || "", " "];
        if (this.date) {
            names.push(this.date);
        } else {
            names.push(window.top.xl("No start date"));
        }
        if (this.date_end) {
            names.push(" - " + this.date_end);
        } else {
            names.push(" - " + window.top.xl("Current"));
        }
        return names.join("");
    }s
}
