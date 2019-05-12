import React, {Component} from 'react';
import helpers from "./helpers";
import Globals from "./globals";
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
    },

}
const Lists = {
    addDataToList:(listName)=> {
        var staticLists = window['fetchList'].state["staticLists"];
        if (Array.isArray(this.state.data)) {
            this.state.data.map((mp, i) => {

                if(staticLists[listName]  ==undefined)
                {
                    staticLists[listName] = [];
                }
                staticLists[listName][i]=mp;
                var temp = staticLists[listName][i];
                let notes = mp['notes'];
                debugger;
                if (this.IsValidJSONString(notes)) {

                    let notes = JSON.parse(mp.notes);

                    temp['notes'] = notes;
                    //the json is ok

                }else{

                    temp['notes']= mp.notes;
                    //the json is not ok

                }

            })

            console.log( listName +" was added to globals :" + staticLists );
        } else {
            console.log( listName +" was not added to globals ");
        }
    },

    checkIfListExistsInLists:()=>{
        if(this.setState.listName in this.state.staticLists){
            return true;
        }
        else{
            return false;
        }
    },
    fetchList : (listName = null) =>{

        if(listName!="" && !this.checkIfListExistsInLists()) {

            fetch("../../../../apis/api/list/" + listName, {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
                .then((res) => res.json())
                .then(
                    (result) => {
                        this.state.data= result;

                        if (result == null) {
                            console.log(this.setState.listName+" WAS NOT FOUND - NO DATA WAS FETCHED ");
                        } else {
                            this.addDataToList(listName);
                        }

                    },
                    (error) => {
                        this.setState({
                            isLoaded: true,
                            error
                        });

                        console.log(error);
                    }
                )
        }

    }
}

export default {
    PatientDataAgent,Lists
};