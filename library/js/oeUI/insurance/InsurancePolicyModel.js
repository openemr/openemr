export class InsurancePolicyModel {
    id = null;
    insureruuid = null;
    pid = null;
    puuid = null;
    type = "primary";
    uuid = null;
    provider = "";
    accept_assignment = "TRUE";
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

    validate() {
        // need to handle validation errors here.
    }

    isCurrentPolicy() {
        return this.id !== null && this.date_end === null;
    }

    populate(pojo) {
        if (typeof pojo === 'object') {
            // TODO: if we need to do any validation we could do that here
            Object.assign(this, pojo);
        }
    }
}
