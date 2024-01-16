/**
 * Insurance Policy Service - This is the service that handles the communication with the server
 * for the retrieval and saving of insurance policies for the patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
// note we do not use import statements here as we need to be able to dynamically load the classes as needed for cache
// busting using the openemr asset version.  Another option would be to use a bundler but we don't have a consistent
// bundler in place yet so this is the best option for now.
let {InsurancePolicyModel} = await import("./InsurancePolicyModel.js?v=" + window.top.jsGlobals.assetVersion);
let {ValidationFieldError, ValidationError} = await import("../Error/ValidationError.js?v=" + window.top.jsGlobals.assetVersion);

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
        window.top.restoreSession();
        return window.fetch(url,
            {
                method: 'GET'
                ,redirect: 'manual'
                ,headers: headers
            })
            .then(result => result.json());
    }
    addInsuranceProviderToList(insuranceCompanyId, insuranceCompanyName) {
        this.__insuranceProviderList[insuranceCompanyId] = insuranceCompanyName;
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

    getPatientInsuranceData(puuid, insuranceuuid) {
        return this.apiRequest(this.__apiURL + 'patient/' + puuid + '/insurance/' + insuranceuuid)
        .then(resultData => {
            let insurance = new InsurancePolicyModel();
            insurance.populate(resultData.data);
            return insurance;
        });
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

    copyPolicyFromExistingPolicy(policyId) {
        let copyPolicy = this.getInsuranceByPolicyId(policyId);
        let newPolicy = copyPolicy.clone();
        newPolicy.setId(null);
        newPolicy.setEffectiveEndDate(null);
        newPolicy.setUuid(null);
        return newPolicy;
    }

    getInsuranceByPolicyId(policyId) {
        let foundInsurance = null;
        this.__types.forEach(t => {
            this.insurancesByType[t].forEach(ins => {
                if (ins.id === policyId) {
                    foundInsurance = ins;
                }
            });
        });
        return foundInsurance;
    }
    createNewPolicy(type) {
        let newPolicy = new InsurancePolicyModel();
        newPolicy.type = type;
        return newPolicy;
    }

    createInMemoryPolicy(type, copyPolicyId) {
        let newPolicy = this.createNewPolicy(type);
        if (copyPolicyId) {
            newPolicy = this.copyPolicyFromExistingPolicy(copyPolicyId);
        }

        // store in our system
        this.storePolicyInMemory(newPolicy);

        return newPolicy;
    }

    storePolicyInMemory(policy) {
        if (!this.insurancesByType[policy.type]) {
            this.insurancesByType[policy.type] = [];
        }
        let index = this.insurancesByType[policy.type].findIndex(ins => ins.id === policy.id);
        if (index === -1) {
            // have new policies stored at the beginning so they show up on top.
            this.insurancesByType[policy.type].unshift(policy);
        }
        else {
            // replace the existing policy in memory
            this.insurancesByType[policy.type][index] = policy;
        }
    }

    #getDataForSave(insuranceObj) {
        return insuranceObj.getDataForSave();
    }

    saveInsurance(insurancePolicy) {
        if (!insurancePolicy.type) {
            throw new Error("Cannot save insurance policy without a type");
        }
        let url = this.__apiURL + 'patient/' + this.__puuid + '/insurance';
        let method = 'POST';

        if (insurancePolicy.id) {
            method = 'PUT';
            url = url + '/' + insurancePolicy.uuid;
        }
        let headers = {
            'apicsrftoken': this.__apiCSRFToken
            ,'Content-Type': 'application/json'
        };
        let saveObj = this.#getDataForSave(insurancePolicy);
        let body = JSON.stringify(saveObj);
        return window.fetch(url,
            {
                method: method
                ,redirect: 'manual'
                ,headers: headers
                ,body: body
            })
            .then(result => {
                if (result.ok) {
                    return result.json();
                } else {
                    return result.json();
                }
            })
            .then(resultData => {
                let validationKeys = Object.keys(resultData.validationErrors || {});
                if (validationKeys.length > 0) {
                    // we should have caught all of the validation in the client side interface so log the error and throw
                    console.error("Validation errors occurred. Error object", resultData.validationErrors);
                    let validationFieldErrors = [];
                    validationKeys.forEach(fieldName => {
                        let validationErrors = resultData.validationErrors[fieldName];
                        validationFieldErrors.push(new ValidationFieldError(fieldName, validationErrors));
                    });
                    throw new ValidationError("Failed to save insurance policy due to validation errors", validationFieldErrors);
                }
                if (resultData.data) {
                    let policy = new InsurancePolicyModel();
                    policy.populate(resultData.data);
                    this.#replacePolicyInMemory(insurancePolicy, policy);
                    return policy;
                } else {
                    throw new Error("Failed to save insurance policy as no data came back");
                }
            });
    }

    #replacePolicyInMemory(oldPolicy, newPolicy) {
        // we replace the old policy with the new policy in memory
        // if we don't find the old policy we just add the new policy to the list
        let index = this.insurancesByType[oldPolicy.type].findIndex(ins => ins.id === oldPolicy.id);
        if (index !== -1) {
            this.insurancesByType[oldPolicy.type][index] = newPolicy;
        } else {
            this.insurancesByType[oldPolicy.type].push(newPolicy);
        }
    }
}
