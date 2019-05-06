import React, {Component} from 'react';
import helpers from "./helpers";

const requests = {}

const PatientDataAgent = {
    groups: () => {
            return fetch("../../../../apis/api/layout/DEM", {
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
    },

    patient: (patient_id) => {
        return fetch("../../../../apis/api/patient/" + patient_id, {
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