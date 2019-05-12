import React from "react";


//class FetchList extends React.Component {
class FetchList extends React.Component {
    constructor (props) {
        super(props);
        this.state = {
            staticLists:[],
            listName:"",
            data:[]
        }
    }

    getStaticListByName(listName){
        if(this.state.staticLists[listName]  == "undefined")
        return this.state.staticLists[listName];
    }

    setListName =(listName)=>{
        this.setState({listName: listName});
    }
    IsValidJSONString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    addDataToList = (listName) => {
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
           //     debugger;
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
    }


    fetchList(listName = null) {
//debugger;
        if(listName!=null) {
            this.state.listName = listName;
        }

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


    checkIfListExistsInLists(){
        if(this.setState.listName in this.state.staticLists){
            return true;
        }
        else{
            return false;
        }
    }
    componentWillMount() {
        this.fetchList();
    }
    render(){
        return null;
    }
}

export default FetchList;