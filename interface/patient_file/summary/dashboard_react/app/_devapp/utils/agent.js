import React, {Component} from 'react';
import helpers from "./helpers";

const requests = {}
const lists ={};
const PatientDataAgent = {

    setGlobalPatientId: (patientId) => {

        return fetch("../../../../apis/api/patient/extended/" + patientId, {
            method: "POST",
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({

            })
        })
            .then((res) => res.json())
            .then(
                (result) => {

                },
                (error) => {
                    this.setState({
                        isLoaded: true,
                        error
                    });
                }
            )
    },

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
    },

}
const Lists = {
    addDataToList: async (listName,result)=> {
        //debugger;
        const addToList = new Promise((resolve, reject) => {
            var staticLists = lists;
            if (Array.isArray(result)) {
                result.map((mp, i) => {

                    if (staticLists[listName] == undefined) {
                        staticLists[listName] = [];
                    }
                    staticLists[listName][i] = mp;
                    var temp = staticLists[listName][i];
                    let notes = mp['notes'];
                    // //debugger;
                    if (Lists.IsValidJSONString(notes)) {

                        let notes = JSON.parse(mp.notes);

                        temp['notes'] = notes;
                        //the json is ok

                    } else {

                        temp['notes'] = mp.notes;
                        //the json is not ok

                    }

                })

                console.log(listName + " was added to globals");
            } else {
                console.log(listName + " was not added to globals ");
            }
            console.log('add to listArray');
            return resolve("done adding to listArray");


        });
        return await Promise.all([addToList]);
    },

    checkIfListExistsInLists:(listName)=>{
        //debugger;

        if(listName in  lists){
            return true;
        }
        else{
            return false;
        }
    },
    IsValidJSONString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    },
    getList : (listName = null) =>{
        if(!listName) {
            console.log("NO LIST WAS SELECTED FOR RETRIEVAL.")
            return;
        }

        return lists[listName];

    },
    getAllList : () =>{
        return lists;

    },
    fetchList : async (listName = null) =>{

        if(listName!="" && !Lists.checkIfListExistsInLists(listName)) {

            const fetchListPromise = await new Promise((resolve, reject) => {


                fetch("../../../../apis/api/list/" + listName, {
                    method: "GET",
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                })
                .then((res) => res.json())
                .then((result) => {
                        if (result == null) {
                            console.log(listName+" WAS NOT FOUND - NO DATA WAS FETCHED ");
                        } else {

                            console.log('added to list');
                            Lists.addDataToList(listName, result).then((res)=>{
                                console.log('fetchList to list');
                                return resolve("done fetchList and adding to list");
                            });

                        }

                    },
                    (error) => {
                        console.log(error);
                })
             });
            //debugger;
            return fetchListPromise;
        }

    }
}

export default {
    PatientDataAgent,Lists
};