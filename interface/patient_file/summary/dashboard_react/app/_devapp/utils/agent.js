import React, {Component} from 'react';
import helpers from "./helpers";

const requests = {}

const PatientDataAgent = {
    groups: (group_name) => {
            return fetch("../../../../apis/api/layout/list/" + group_name, {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
        })
            .then((res) => res.json())
            .catch((error) => {
                // console.error("A");
                // console.error(error);
                // console.error("B");
            })
    },

    byGroupId: (group_name, group_id) => {
        return fetch("../../../../apis/api/layout/list/" + group_name + "/" + group_id, {
            method: "GET",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
            .then((res) => res.json())
            .catch((error) => {
                // console.error("A");
                // console.error(error);
                // console.error("B");
            })
    },

    patient: (patient_id) => {
        return fetch("../../../../apis/api/patient/extended/" + patient_id, {
            method: "GET",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
            .then((res) => res.json())
            // .catch((error) => {
            //     console.error(error);
            // })
    }
}

export default {
    PatientDataAgent
};