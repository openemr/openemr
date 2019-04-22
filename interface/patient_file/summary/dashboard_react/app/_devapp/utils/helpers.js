import React, {Component} from 'react';

//export const PatientID = React.createContext(helpers.getQueryVariable("set_pid"));

const helpers = {
    getQueryVariable: function (variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] == variable) {
                return pair[1];
            }
        }
        return (false);
    },

    getPatientId: function () {
        return this.getQueryVariable("set_pid");
    }
}

export default helpers;