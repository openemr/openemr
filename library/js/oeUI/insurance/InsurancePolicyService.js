/**
 * Insurance Policy Service - This is the service that handles the communication with the server
 * for the retrieval and saving of insurance policies for the patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
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

    __insurancePatientData = null;

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
    getCurrentPatientData() {
        if (this.__insurancePatientData) {
            return Promise.resolve(this.__insurancePatientData);
        }

        let patientPromise = this.apiRequest(this.__apiURL + 'patient/' + this.__puuid);
        let employerDataPromise = this.apiRequest(this.__apiURL + 'patient/' + this.__puuid + '/employer');
        return Promise.all([patientPromise, employerDataPromise])
        .then(resultData => {
            let patientResult = resultData[0];
            let employerResult = resultData[1];
            let insurancePatientData = {};
            if (patientResult.data) {
                insurancePatientData = patientResult.data;
            }
            if (employerResult.data) {
                insurancePatientData.employer = employerResult.data;
            } else {
                insurancePatientData.employer = [];
            }
            this.__insurancePatientData = insurancePatientData;
            return this.__insurancePatientData;
        });
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
            insurance.populate(resultData.data, true);
            return insurance;
        });
    }

    findInsuranceByUuid(uuid) {
        let foundInsurance = null;
        this.__types.forEach(t => {
            this.insurancesByType[t].forEach(ins => {
                if (ins.uuid === uuid) {
                    foundInsurance = ins;
                }
            });
        });
        return foundInsurance;
    }

    getCurrentInsuranceForType(type) {
        if (this.insurancesByType[type] && this.insurancesByType[type].length) {
            // the first one is always the most current one.
            return this.insurancesByType[type].find(ins => ins.isCurrent());
        } else {
            return null;
        }
    }

    getMostRecentInsuranceByType(type) {

        if (this.insurancesByType[type] && this.insurancesByType[type].length) {
            // the first one is always the most current one.
            return this.insurancesByType[type][0];
        } else {
            return null;
        }
    }

    loadInsurancesByType() {
        return this.apiRequest(this.__apiURL + 'patient/' + this.__puuid + '/insurance')
            .then(resultData => {
                let selectedInsurance = null;
                if (resultData.data) {
                    // need to sort through the data and populate the three categories
                    resultData.data.forEach(ins => {
                        let insurance = new InsurancePolicyModel();
                        insurance.populate(ins, true);
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
                        this.sortInsurancesByDate(this.insurancesByType[t]);
                    })

                    if (this.insurancesByType[this.__types[0]].length > 0) {
                        selectedInsurance = this.insurancesByType[this.__types[0]][0];
                    }
                }
                return this.insurancesByType;
            });
    }

    sortInsurancesByDate(insurances) {
        let futureDate = new Date();
        insurances.sort((a, b) => {
            // since we can add / update policies in JS land, we need to handle the sort order here
            // need to convert the following mysql order by to js
            //         . ", (`date_end` is null or `date_end` > NOW()) DESC"
            //         . ", (`date_end` IS NOT NULL AND `date_end` > NOW()) DESC"
            //         . ", `date` DESC, `date_end` DESC, `policy_number` ASC";
            // we want to sort by date_end first so that the most current one is first
            let dateEndNullA = a.date_end === null;
            let dateEndNullB = b.date_end === null;
            let dateEndFutureA = !dateEndNullA && a.date_end > futureDate;
            let dateEndFutureB = !dateEndNullB && b.date_end > futureDate;
            let dateEndNullOrFutureA = dateEndNullA || dateEndFutureA ? 1 : 0;
            let dateEndNullOrFutureB = dateEndNullB || dateEndFutureB ? 1 : 0;
            let dateEndNotNullAndFutureA = !dateEndNullA && dateEndFutureA ? 1 : 0;
            let dateEndNotNullAndFutureB = !dateEndNullB && dateEndFutureB ? 1 : 0;

            let sortIndex = 0;
            // b - a for descending order => (`date_end` is null or `date_end` > NOW()) DESC
            sortIndex += dateEndNullOrFutureB - dateEndNullOrFutureA;
            if (sortIndex !== 0) {
                return sortIndex;
            }
            // b - a for descending order => (`date_end` IS NOT NULL AND `date_end` > NOW()) DESC
            sortIndex += dateEndNotNullAndFutureB - dateEndNotNullAndFutureA;
            if (sortIndex !== 0) {
                return sortIndex;
            }
            // `date` DESC, nulls come first, then sort by date with most recent first
            if (a.date !== null && b.date !== null) {
                sortIndex += a.date > b.date ? -1 : 1;
            } else if (b.date === null && a.date !== null) {
                sortIndex -= -1;
            } else {
                sortIndex += 1;
            }
            if (sortIndex !== 0) {
                return sortIndex;
            }
            // `date_end` DESC, nulls come first, then sort by date with most recent first
            if (!dateEndNullA && !dateEndNullB) {
                sortIndex += a.date_end > b.date_end ? -1 : 1;
            } else if (dateEndNullB && !dateEndNullA) {
                sortIndex -= -1;
            } else {
                sortIndex += 1;
            }
            if (sortIndex !== 0) {
                return sortIndex;
            }
            // `policy_number` ASC - sort by policy number ascending
            // use localeCompare to handle the case where the policy number is a string
            // pass the base sensitivity to ignore case
            if (a.policy_number !== null && b.policy_number !== null) {
                // TODO: @adunsulag if we have the locale specified here... we could pass it in instead of undefined
                return b.localeCompare(a, undefined, {sensitivity: 'base'});
            } else if (b.policy_number === null && a.policy_number !== null) {
                return -1;
            } else {
                return 1;
            }
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
                if (ins.id === policyId && ins.type === t) {
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
        this.sortInsurancesByDate(this.insurancesByType[policy.type]);
    }

    #getDataForSave(insuranceObj) {
        return insuranceObj.getDataForSave();
    }

    swapInsurance(insurancePolicy, targetType) {

        // we have a special logic we have to follow for current policies due to the way the server
        // rejects the save if more than one current policy exists for a given type.
        // we need to update the target policy to have an end date of today
        // then we need to save the target policy (we can update it's type to be the source type)
        // then we need to change the source policy to have a type of the target type and update it
        // finally we can update the target policy to have an end date of null and update it
        // this ends up being 3 updates in order to deal with the server side validation
        // I ended up doing this because I didn't want to introduce a flag on the request
        // or a new REST operation endpoint to handle the swap
        // this is less eficient and more chatty but it is a lot less code
        let getData = new URLSearchParams();
        getData.append('type', targetType);
        getData.append('uuid', insurancePolicy.uuid);
        return this.apiRequest(this.__apiURL + 'patient/' + this.__puuid + '/insurance/$swap-insurance?' + getData.toString())
        .then(resultData => {
            let validationKeys = Object.keys(resultData.validationErrors || {});
            if (validationKeys.length > 0) {
                // we should have caught all of the validation in the client side interface so log the error and throw
                console.error("Validation errors occurred. Error object", resultData.validationErrors);
                let validationFieldErrors = [];
                validationKeys.forEach(fieldName => {
                    // map the 'type' error field to the 'date' field so we can display the error on the date field
                    let validationFieldName = fieldName;
                    if (fieldName == 'type') {
                        validationFieldName = 'date';
                    }
                    let validationErrors = resultData.validationErrors[fieldName];
                    validationFieldErrors.push(new ValidationFieldError(validationFieldName, validationErrors));
                });
                throw new ValidationError("Failed to save insurance policy due to validation errors", validationFieldErrors);
            }
            let insurancePolicySrc = new InsurancePolicyModel();
            let insurancePolicyTarget = new InsurancePolicyModel();
            let oldSrcInsurance = this.findInsuranceByUuid(resultData.data.src.uuid);
            if (oldSrcInsurance) {
                this.#removePolicyInMemory(oldSrcInsurance);
            }
            insurancePolicySrc.populate(resultData.data.src, true);
            this.storePolicyInMemory(insurancePolicySrc);
            if (resultData.data.target) {
                let oldTargetInsurance = this.findInsuranceByUuid(resultData.data.target.uuid);
                if (oldTargetInsurance) {
                    this.#removePolicyInMemory(oldTargetInsurance);
                }
                insurancePolicyTarget.populate(resultData.data.target, true);
                this.storePolicyInMemory(insurancePolicyTarget);
            }
            return insurancePolicySrc;
        });
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
                    policy.populate(resultData.data, true);
                    this.#replacePolicyInMemory(insurancePolicy, policy);
                    return policy;
                } else {
                    throw new Error("Failed to save insurance policy as no data came back");
                }
            });
    }

    #removePolicyInMemory(policy) {
        let index = this.insurancesByType[policy.type].findIndex(ins => ins.id === policy.id);
        if (index !== -1) {
            this.insurancesByType[policy.type].splice(index, 1);
        }
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
