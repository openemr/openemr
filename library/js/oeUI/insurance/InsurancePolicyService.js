import {InsurancePolicyModel} from "./InsurancePolicyModel.js";

export class InsurancePolicyService
{
    insurancesByType = {};
    __insuranceProviderList = null;
    __apiCSRFToken = null;
    __puuid = null;

    constructor(csrfToken, apiURL, insuranceProviderList, types, puuid) {
        this.__apiCSRFToken = csrfToken;
        this.__apiURL = apiURL;
        this.__insuranceProviderList = insuranceProviderList;
        this.__types = types;
        this.__puuid = puuid;
    }

    apiRequest(url) {
        let headers = {
            'apicsrftoken': this.__apiCSRFToken
        };
        return window.fetch(url,
            {
                method: 'GET'
                ,redirect: 'manual'
                ,headers: headers
            })
            .then(result => result.json());
    }

    getInsuranceProvidersList() {
        return this.__insuranceProviderList;
    }

    getInsurancesByType() {
        return this.insurancesByType;
    }

    getInsuranceCategories() {
        return this.__types;
    }

    loadInsurancesByType() {
        return this.apiRequest(this.__apiURL + 'patient/' + this.__puuid + '/insurance')
            .then(resultData => {
                let selectedInsurance = null;
                if (resultData.data) {
                    // need to sort through the data and populate the three categories
                    resultData.data.forEach(ins => {
                        let insurance = new InsurancePolicyModel();
                        insurance.populate(ins);
                        // TODO: @adunsulag do we want to create a model object for this?
                        // would allow us to maintain some consistency over the code... but reduces flexibility
                        selectedInsurance = insurance;
                        if (this.insurancesByType[insurance.type]) {
                            this.insurancesByType[insurance.type].push(insurance);
                        } else {
                            this.insurancesByType[insurance.type] = [insurance];
                        }
                    });
                    this.__types.forEach(t => {
                        if (!this.insurancesByType[t]) {
                            // put in a blank one we can use
                            let blankPolicy = new InsurancePolicyModel();
                            blankPolicy.type = t;
                            this.insurancesByType[t] = [blankPolicy];
                        }
                        this.insurancesByType[t].sort((a, b) => {
                            // end date is the most current one
                            if (!a.hasOwnProperty('end_date') || a === null) {
                                return -1;
                            }
                            return a.date < b.date ? 1 : -1;
                        });
                    })

                    if (this.insurancesByType[this.__types[0]].length > 0) {
                        selectedInsurance = this.insurancesByType[this.__types[0]][0];
                    }
                }
                return this.insurancesByType;
            });
    }
}
