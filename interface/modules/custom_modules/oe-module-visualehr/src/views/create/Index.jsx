import React from 'react';
import { rxService } from '../../services/Services';
import "react-tiny-fab/dist/styles.css";
import { format } from "date-fns";
import Dropdown from '../../utilities/Dropdown'
import DatePicker from "../../utilities/DatePickerUtil";
import ColorPicker from "../../utilities/ColorPicker";
import { fetchService } from "../../services/RxServices";
import ICD10Diagnoses from './ICD10Diagnoses'
import { UtilService } from '../../services/UtilService';

class Index extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
          hasRequest: false,
          showAlert: false,
          isReset:false,
          alertBg: "bg-red-400",
          toogleEncounter: false,
          createNewDate:false,
          isLoading:true,
          groupTimeline:[],
          showDialog:false,
          selectedData:{},
          isUpdate:false,
          loadFavorites:[],
          timeline:{
              id:1,
              type:"medical_problem"
          },
          showColorPicker: false,
          showICD10Dignosis: false,
          maxDate: format(new Date(), "yyyy-MM-dd"),
          input: {
            pid: UtilService.getPid(),
            name_display:"",
            type:"",
            subtype:"",
            universal_device_id:"",
            outcome:"",
            icd_10_diagnoses: [],
            icd_10:"",
            color:"",
            destination:"",
            referred_by:"",
            occurrence:"",
            verification:"",
            comments:"",
            active_codes:"",
            start_date: format(new Date(), "yyyy-MM-dd"),
            end_date:"",
            med_usage:"",
            severity:"",
            reaction:"",
            med_request_intent:"",
          },
            occurrence_options: [],
            verification_options: [],
            outcome_list: [],
            favourite_list: [],
            icd_10_diagnoses: [],
            med_usage_list: [],
            medication_request_list: [],
            severity_options: [],
            reaction_options: [],
            allergy_favorites: [],
            medication_favorites: [],
            problem_favorites: [],
            suregery_favorites: [],
            active_codes: [],
            method:""
        };
        this.handleKeypressed = this.handleKeypressed.bind(this);
      }
    componentDidMount(){
        this.subscription = rxService.getSubscription().subscribe(data => {

           
            if(data.data !== undefined){
                if (!data.data.error) {
                    if(data.data.vitals !== undefined){
                        this.setState({groupTimeline: data.data.sidebarlist})
                    
                    }
                }
                this.handeFetchRequest()
            }

            if(data.text !==undefined && data.data !== undefined){
                this.setState({showDialog:true,method:data.text})
                this.handleUpdateRecords(data.data)
            }else{
                if(data.text !== undefined){
                    
                    this.setState({showDialog:true,method:data.text,timeline: (this.state.groupTimeline.length >0) ? this.state.groupTimeline[0] :{"id":1}})
                }
            }
            
            
        });

    }
    componentWillUnmount() {
        this.subscription.unsubscribe();
    }
    handeFetchRequest=()=>{
        fetchService.fetchCurrentICD10(UtilService.getPid()).subscribe({
            next: result => {
              this.setState({
                active_codes: result,
                isLoading: false,
              });
            },
            complete: () =>{}
        })
        fetchService.fetchDropdowns().subscribe({
            next: data => {
                let input = this.state.input;

                if (data.occurrence.length > 0)
                  input["occurrence"] = data.occurrence[0].id;
        
                if (data.verification.length > 0)
                  input["verification"] = data.verification[0].id;
        
                if (data.outcome.length > 0)
                  input["outcome"] = data.outcome[0].id;
        
                if (data.medication_usage.length > 0)
                  input["med_usage"] = data.medication_usage[0].id;
        
                if (data.request_intent.length > 0)
                  input["med_request_intent"] = data.request_intent[0].id;
        
                if (data.severity.length > 0)
                  input["severity"] =
                    data.severity[data.severity.length - 1].id;
        
                if (data.reaction.length > 0)
                  input["reaction"] = data.reaction[0].id;
                  
                this.setState({
                  
                  occurrence_options: data.occurrence,
                  verification_options: data.verification,
                  outcome_list: data.outcome,
                  med_usage_list: data.medication_usage,
                  med_request_intent:data.request_intent,
                  medication_request_list: data.request_intent,
                  severity_options: data.severity,
                  reaction_options: data.reaction,
                  input,
                });
            },
            complete: () =>{}
        })
        fetchService.fetchFavorites().subscribe({
            next: data => {
                var favorites =
                this.state.timeline.id == 1
                  ? data.problem_favorites
                  : data.medication_favorites;

                let input = this.state.input;
                input["type"] = this.state.timeline.type;

              this.setState({input,
                favourite_list: favorites,
                loadFavorites:favorites,
                problem_favorites: data.problem_favorites,
                medication_favorites: data.medication_favorites,
                allergy_favorites: data.allergy_favorites,
                suregery_favorites: data.suregery_favorites,
                isFavoriteLoading: false,
              });
            },
            complete: () =>{}
        })
    }
    handleCloseDialog=()=>{
        this.setState({showDialog:false},this.handleEmpty)
    }
    handleOpenICD1 = () => {
        this.setState({ showICD10Dignosis: true });
    };
    handleSubmit = () => {
        if (!this.state.hasRequest) {
            this.handleRequest();
        }
    };

    handleUpdateRecords = (myData) => {
        if (myData != undefined) {
            var timelinetype = this.state.groupTimeline.find((obj) => {
                return obj.type === myData.type;
              });
           if(timelinetype !==undefined){
               this.setState({timeline:timelinetype,isUpdate:true})
           }
            
          setTimeout(() => {
            this.setState({
              input: {
                id: parseInt(myData.id),
                comments: myData.comments,
                destination: myData.destination,
                end_date: myData.endDate,
                icd_10: myData.diagnosis,
                icd_10_diagnoses: [myData.diagnosis],
                pid: UtilService.getPid(),
                med_request_intent: myData.request_intent,
                med_usage: myData.medication_usage,
                name_display: myData.name,
                occurrence: myData.occurrence,
                outcome: myData.outcome,
                color: myData.color,
                reaction: myData.reaction,
                referred_by: myData.referredby,
                severity: myData.severity_al,
                start_date: myData.startDate,
                subtype: myData.subtype,
                type: myData.type,
                universal_device_id: myData.udi,
                verification: myData.verification,
              },
            });
          }, 500);
       }
      };
    handleRequest = () => {
        this.setState({
          showAlert: true,
          alertMessage: "Saving data, please wait....",
          alertBg: "bg-green-500",
        });
        let input = this.state.input;
    
        let commonData = {};
        commonData["pid"] = input.pid;
        commonData["id"] = input.id;
        commonData["type"] = UtilService.toStringNull(input.type);
        commonData["color"] = UtilService.toStringNull(input.color);
        commonData["icd_10_diagnoses"] = input.icd_10_diagnoses;
        commonData["icd_10"] = input.icd_10;
        commonData["date"] = format(new Date(), "yyyy-MM-dd H:mm:ss");
        commonData["start_date"] = UtilService.toStringNull(input.start_date);
        commonData["end_date"] = UtilService.toStringNull(input.end_date);
        commonData["occurrence"] = UtilService.toStringNull(input.occurrence);
        commonData["verification"] = UtilService.toStringNull(input.verification);
        commonData["comments"] = UtilService.toStringNull(input.comments);
        commonData["outcome"] = UtilService.toStringNull(input.outcome);
        commonData["destination"] = UtilService.toStringNull(input.destination);
        commonData["referred_by"] = UtilService.toStringNull(input.referred_by);
        commonData["name_display"] = UtilService.toStringNull(input.name_display);
        commonData["subtype"] = UtilService.toStringNull(input.subtype);
    
        if (
          input["name_display"] != "" &&
          input["color"] != "" &&
          input["icd_10_diagnoses"].length > 0
        ) {
            if (this.state.timeline.id == 2) {
                commonData["med_usage"] = this.state.input.med_usage;
                commonData["med_request_intent"] =
                  this.state.input.med_request_intent;
              } else if (this.state.timeline.id == 3) {
                commonData["severity"] = this.state.input.severity;
                commonData["reaction"] = this.state.input.reaction;
              } else if (this.state.timeline.id == 6) {
                commonData["universal_device_id"] =
                  this.state.input.universal_device_id;
              }
              this.sendRequest(commonData);
        } else {
          this.setState({
            alertMessage: "Please complete the required fields",
            alertBg: "bg-red-400",
          });
        }
      };
    
    sendRequest=(commonData)=>{
        fetchService.storeRequst(commonData,this.state.method).subscribe({
            next: result => {
                if(result.error !==undefined){
                    this.setState({
                        alertMessage: "Unknown error occurred!",
                        alertBg: "bg-red-400",
                    });
                }else{
                    this.setState({
                        showAlert: true,
                        alertMessage: result.message,
                        alertBg: result.status == 1 ? "bg-green-500" : "bg-red-500",
                    });
                    if (result.status == 1) {
                        setTimeout(this.handleCloseDialog.bind(this), 1000);
                        fetchService.fetchSiberData()
                      }
                }
              
            },
            complete: () =>{}
        })
    }
    handleEmpty = () => {
        let input = this.state.input;
        input["type"] = "";
        input["name_display"] = "";
        input["universal_device_id"] = "";
        input["outcome"] = this.handleFirstItems(this.state.outcome_list);
        input["icd_10_diagnoses"] = [];
        input["icd_10"] = "";
        input["destination"] = "";
        input["referred_by"] = "";
        input["occurrence"] = this.handleFirstItems(this.state.occurrence_options);
        input["verification"] = this.handleFirstItems(this.state.verification_options);
        input["comments"] = "";
        input["color"] = "";
        input["icd_10"] = "";
        input["start_date"] = format(new Date(), "yyyy-MM-dd");
        input["end_date"] = "";
        input["severity"] = this.handleFirstItems(this.state.severity_options);
        input["reaction"] = this.handleFirstItems(this.state.reaction_options);
        input["med_request_intent"] = this.handleFirstItems(this.state.med_request_intent);
        input["med_usage"] = this.handleFirstItems(this.state.med_usage_list);
        this.setState({ input,showAlert:false,hasRequest:false });
      };
    handleFirstItems=(data)=>{
        if(data === undefined)
            return ""
            
        if(data.length > 0) {
            return data[0].id
        }else{
            return ""
        }
    }
    handleOnClick=(data)=>{
        if(!this.state.isUpdate){
            this.handleEmpty()
        }
        
        let input = this.state.input;
        let loadFavorites = this.state.loadFavorites;
        input["type"] =data.type;
        if(data.id==2){
            loadFavorites=this.state.medication_favorites
        }else if(data.id==3){
            loadFavorites=this.state.allergy_favorites
        }else if(data.id==4){
            loadFavorites=this.state.suregery_favorites
        }else{
            loadFavorites=this.state.favourite_list
        }
        this.setState({ timeline: data,isReset:true,input,loadFavorites},this.handleResetOptions);
    }
    handleResetOptions=()=>{
        let _self = this
        setTimeout(function(){
            _self.setState({isReset:false})
        },2000)
    }
    handelCloseAlert = () => {
        this.setState({ showAlert: false });
    };
    handleMedicationRequest = (data) => {
        let input = this.state.input;
        input["med_request_intent"] = data.id;
        this.setState({ input });
      };
    handleFavorite = (data) => {
        let input = this.state.input;
        input["name_display"] = data.name;
        if (data.codes != null && data.codes != "") {
          input["icd_10_diagnoses"] = [data.codes];
          input["icd_10"] = data.codes;
        }
    
        this.setState({ input, favorite_selected: data.id });
      };
      handleMedUsage = (data) => {
        let input = this.state.input;
        input["med_usage"] = data.id;
        this.setState({ input });
      };
      handleOutcome = (data) => {
        let input = this.state.input;
        input["outcome"] = data.id;
        this.setState({ input });
      };
      handleSeverity = (data) => {
        let input = this.state.input;
        input["severity"] = data.id;
      };
      handleReaction = (data) => {
        let input = this.state.input;
        input["reaction"] = data.id;
      };
      handleVerification = (data) => {
        let input = this.state.input;
        input["verification"] = data.id;
      };
    
      handleOccurrences = (data) => {
        let input = this.state.input;
        input["occurrence"] = data.id;
      };
    
      handleShowPicker = (getstart_date) => {
        this.setState({
          showDatePicker: true,
          isstart_date: getstart_date
        });
      };
      handleCancelDate=(type)=>{
        let input = this.state.input;
        if(type){
            input["start_date"] = "";
        }else{
            input["end_date"] = "";
        }
        this.setState({input})
      }
      handleKeypressed=(event)=>{
        let input = this.state.input;
        var inputtext =event.target.value
        var len = event.target.value.length;
        var text = inputtext.split("").pop()
        const re = /^[0-9-]+$/;
        if(!re.test(inputtext)){
            return
        }
        
        if(len > 10)
            return 
        // If they don't add the slash, do it for them...
        if(len === 4) {
            inputtext += '-';
        }
        // If they don't add the slash, do it for them...
        if(len === 7) {
            inputtext += '-';
          }
        input[event.target.name] = inputtext;
        this.setState({input});
        
      }

      handleChange = (event) => {
        let input = this.state.input;
        input[event.target.name] = event.target.value;
        this.setState({ input });
      };
      selectedDate = (date) => {
        let input = this.state.input;
        if (this.state.isstart_date) {
          input["start_date"] =
            date != "" ? date : format(new Date(), "yyyy-MM-dd");
        } else {
          input["end_date"] = date != "" ? date : format(new Date(), "yyyy-MM-dd");
        }
        this.setState({ showDatePicker: false, input });
      };
    handleToggle=()=>{
        this.setState({showICD10Dignosis:false})
    }
    handleICD10=(data)=>{
        let input = this.state.input;
        input["icd_10"] = data.join(",")
        this.setState({input,icd_10_diagnoses:data},this.handleToggle)
    }
    handleColorPicker = () => {
        this.setState({ showColorPicker: true });
    };
    closeColorPicker = () => {
        this.setState({ showColorPicker: false });
    };
    selectedColor = (color) => {
        let input = this.state.input;
        input["color"] = color;
        this.setState({ input, showColorPicker: false });
    };
    render() {
        return (
            <>

                {
                    (this.state.showDialog) ? 
                    <>
                      <div className="fixed bg-black bg-opacity-25 h-full w-full top-0 left-0 z-50 overflow-y-auto font-sfuidisplay">
                    {this.state.showColorPicker ? (
                        <ColorPicker
                            closeColorPicker={this.closeColorPicker}
                            selectedColor={this.selectedColor}
                        />
                    ) :""}
                    {this.state.showICD10Dignosis ? (
                        <ICD10Diagnoses
                            handleToggle={this.handleToggle}
                            handleICD10={this.handleICD10}
                            timeline={this.state.timeline}
                            icd_10_selected_diagnoses={this.state.input.icd_10_diagnoses}
                            icd_10_diagnoses={this.state.icd_10_diagnoses}
                        />
                        ) :""}
                    {this.state.showDatePicker ? (
                        <DatePicker
                            maxDate={this.state.maxDate}
                            selectedDate={this.selectedDate}
                        />
                        ) :""}
                    <div className="grid h-screen place-items-center px-1">
                        <div className="w-full m-auto bg-primaryRedBgColor border-4 rounded-3xl border-black overflow-hidden md:max-w-5xl ">
                            <div className="md:flex">
                                <div className="w-full">
                                    <div className="px-4 flex justify-between text-black">
                                
                                        <div className="flex w-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" onClick={this.handleCloseDialog} className="h-8 w-8 mt-4 cursor-pointer" viewBox="0 0 20 20" fill="currentColor" >
                                                <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd"/>
                                            </svg>
                                            <div className="flex w-full mx-10 items-center justify-center font-sfuidisplayBlack">
                                                <div className="flex  grid grid-cols-6 gap-6 py-1 my-1 w-full justify-center items-center">
                                                {this.state.groupTimeline
                                                    .filter((d) => d.id != 7 && d.id != 8)
                                                    ?.map((data, i) => {
                                                    return (
                                                        <div onClick={() => this.handleOnClick(data)} key={`${UtilService.makeKey(15)}`} className={`cursor-pointer flex w-full bg-primaryRedColor mx-4 justify-center items-center rounded`} >
                                                        <div key={`${UtilService.makeKey(15)}`} className={`px-4 m-1 py-3 w-full h-full rounded-sm flex items-center justify-center ${
                                                            data.id == this.state.timeline.id
                                                                ? "bg-red-100 "
                                                                : ""
                                                            }`}
                                                        >
                                                            <p key={`${UtilService.makeKey(15)}`}>{data.tab_name}</p>
                                                        </div>
                                                        </div>
                                                    );
                                                    })
                                                }
                                                </div>   
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" onClick={this.handleSubmit} className="h-8 w-8 mt-4 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        
                                    </div>
                                    {this.state.showAlert ? (
                                        <div
                                        className={`${this.state.alertBg} text-white py-2 items-center text-center relative`}
                                        >
                                        {this.state.alertMessage}
                                        <div
                                            className="absolute right-5 top-2 w-5 h-5 cursor-pointer"
                                            onClick={() => this.handelCloseAlert()}
                                        >
                                            <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            className="h-5 w-5"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            >
                                            <path
                                                fillRule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clipRule="evenodd"
                                            />
                                            </svg>
                                        </div>
                                        </div>
                                    ) : null}
                                    <form method="post" className="flex flex-col w-full">
                                        <div className="flex flex-col w-full">
                                            <div className="flex flex-col w-full">
                                            {this.state.isLoading ? (
                                                    <div className="flex w-full h-96 grid place-items-center">
                                                        <div className="flex w-full flex-col justify-center items-center">
                                                        <svg
                                                            role="status"
                                                            className="inline w-8 h-8 dark:text-white animate-spin"
                                                            viewBox="0 0 100 101"
                                                            fill="none"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                        >
                                                            <path
                                                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                            fill="#E5E7EB"
                                                            />
                                                            <path
                                                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                            fill="currentColor"
                                                            />
                                                        </svg>
                                                        <div className="mt-10">Fetching Data</div>
                                                        </div>
                                                    </div>
                                                    ) : (
                                                    <div className="flex w-full">
                                                        <div className="flex flex-col w-full font-sfuidisplay">
                                                        <div className="flex flex-col w-full px-4 py-6 space-y-4 ">
                                                            {this.state.timeline.id == 6 ? (
                                                            <div className="flex w-full">
                                                                <div className="flex w-full bg-white grid grid-cols-4 border border-black gap-4 py-1 rounded-2xl items-center">
                                                                <div className="font-black font-sfuidisplayBlack required-field">
                                                                    Universal Device ID{" "}
                                                                </div>
                                                                <div className="col-span-3 flex flex-row justify-between w-full text-center italic">
                                                                    <div className="flex w-full items-center justify-center">
                                                                    <div className="relative w-full group">
                                                                        <input
                                                                        type="text"
                                                                        name="universal_device_id"
                                                                        onChange={this.handleChange}
                                                                        value={UtilService.toString( this.state.input.universal_device_id)}
                                                                        className="block py-1 px-0 w-full text-sm text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-white focus:outline-none focus:ring-0 focus:border-blue-600 peer placeholder-black text-center italic"
                                                                        placeholder="Enter UUID  or search "
                                                                        required
                                                                        />
                                                                    </div>
                                                                    </div>
                                                                </div>
                                                                </div>
                                                            </div>
                                                            ) : (
                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-4 gap-4 rounded-xl">
                                                                <div className="font-black py-2 font-sfuidisplayBlack">
                                                                Favorites/ Previous
                                                                </div>
                                                                <div className="col-span-3 w-full text-center italic">
                                                                <div className="flex flex-col h-16 w-full overflow-y-auto bg-white">
                                                                    {!this.state.isFavoriteLoading ? (
                                                                    this.state.loadFavorites.length > 0 ? (
                                                                        this.state.loadFavorites.map((data, d) => {
                                                                        return (
                                                                            <div
                                                                            className={`flex w-full ${
                                                                                data.id == this.state.favorite_selected
                                                                                ? "font-sfuidisplayBlack"
                                                                                : "font-sfuidisplay"
                                                                            } cursor-pointer hover:bg-gray-100 py-2 px-4`}
                                                                            key={`${UtilService.makeKey(15)}`}
                                                                            onClick={() => this.handleFavorite(data)}
                                                                            >
                                                                            {data.name}
                                                                            </div>
                                                                        );
                                                                        })
                                                                    ) :""
                                                                    ) : (
                                                                    <div className="italic flex w-full ">Loading....</div>
                                                                    )}
                                                                </div>
                                                                </div>
                                                            </div>
                                                            )}

                                                            <div className="flex w-full">
                                                            <div className="flex w-full bg-white grid grid-cols-4 border border-black gap-4 py-1 rounded-2xl items-center">
                                                                <div className="font-black font-sfuidisplayBlack required-field">
                                                                {this.state.timeline.name} Display{" "}
                                                                </div>
                                                                <div className="col-span-3 flex flex-row justify-between w-full text-center italic">
                                                                <div className="flex w-full items-center justify-center">
                                                                    <div className="relative w-full group">
                                                                    <input
                                                                        type="text"
                                                                        name="name_display"
                                                                        onChange={this.handleChange}
                                                                        value={UtilService.toString(this.state.input.name_display)}
                                                                        className="block py-1 px-0 w-full text-sm text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-white focus:outline-none focus:ring-0 focus:border-blue-600 peer placeholder-black text-center italic"
                                                                        placeholder="Free Text Entry or Selected from Above "
                                                                        required
                                                                    />
                                                                    </div>
                                                                </div>
                                                                </div>
                                                            </div>
                                                            </div>

                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-8 gap-7 py-1 rounded-xl">
                                                            <div className="col-span-2 font-black font-sfuidisplayBlack required-field">
                                                                {this.state.timeline.id == 1
                                                                ? "ICD-10 Coded as"
                                                                : "Associated ICD-10"}{" "}
                                                            </div>
                                                            <div className="col-span-6 flex flex-row justify-between w-full text-center italic">
                                                                <div className="w-full">
                                                                <div className="col-span-4 flex w-full bg-white cursor-pointer border border-black gap-4 rounded-xl items-center">
                                                                    <div className="flex w-full grid grid-cols-7 relative">
                                                                    <div className="col-span-6 flex flex-row justify-between w-full text-center italic">
                                                                        <div className="flex w-full items-center justify-center">
                                                                        <div className="relative w-full group">
                                                                            <div
                                                                            onClick={this.handleOpenICD1}
                                                                            className="block py-1 px-0 w-full text-sm text-gray-900 overflow-hidden bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-white focus:outline-none focus:ring-0 focus:border-blue-600 peer placeholder-black text-center italic"
                                                                            >
                                                                            {(this.state.input.icd_10 != "" && this.state.input.icd_10_diagnoses.length >
                                                                            0) ? (
                                                                                <div>
                                                                                {this.state.input.icd_10_diagnoses.join(
                                                                                    ","
                                                                                )}
                                                                                </div>
                                                                            ) : (
                                                                                <span>
                                                                                ICD-10 Search - Click to Launch, or Add
                                                                                from Dropdown
                                                                                </span>
                                                                            )}
                                                                            </div>
                                                                        </div>
                                                                        </div>
                                                                    </div>

                                                                    <div className="flex w-full col-span-1 items-end justify-end">
                                                                        <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        className="h-5 w-5 mr-1 absolute top-1"
                                                                        viewBox="0 0 20 20"
                                                                        fill="currentColor"
                                                                        >
                                                                        <path
                                                                            fillRule="evenodd"
                                                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                                            clipRule="evenodd"
                                                                        />
                                                                        </svg>
                                                                    </div>
                                                                    </div>
                                                                </div>
                                                                </div>
                                                                <div className="flex w-60 justify-end items-end ml-10">
                                                                {this.state?.active_codes?.length > 0 ? (
                                                                    <Dropdown
                                                                    data={this.state.active_codes}
                                                                    handleChange={this.handleCurrentICD10}
                                                                    defaultValue={`${this.state.input.active_codes}`}
                                                                    DropdownClass={`bg-white text-black`}
                                                                    placeholder={`Current ICD-10`}
                                                                    selectedRef={`h-7 `}
                                                                    DropdownListClass={`flex w- text-black hover:bg-gray-200`}
                                                                    className={`flex w-full text-black italic  font-bold text-sm px-6 border border-black rounded-xl py-1 outline-none focus:outline-none`}
                                                                    />
                                                                ) :""}
                                                                </div>
                                                            </div>
                                                            </div>
                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-6 gap-5 py-1 rounded-xl">
                                                            <div className="font-black font-sfuidisplayBlack required-field">
                                                                Color
                                                            </div>
                                                            <div className="col-span-5 flex flex-row justify-between w-full text-center italic">
                                                                <div
                                                                className={`flex w-full h-7 justify-center text-sm items-center cursor-pointer text-center ${
                                                                    this.state.input.color != "" &&
                                                                    this.state.input.color != null
                                                                    ? "text-white"
                                                                    : "text-black"
                                                                }`}
                                                                style={{ backgroundColor: `${this.state.input.color}` }}
                                                                onClick={this.handleColorPicker}
                                                                >
                                                                {this.state.input.color == null || this.state.input.color == ""
                                                                    ? " Click to pick color"
                                                                    : `${this.state.input.color}`}
                                                                </div>
                                                            </div>
                                                            </div>
                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-6 gap-5 py-1 rounded-xl">
                                                            <div className="font-black font-sfuidisplayBlack required-field">
                                                                Date 
                                                            </div>
                                                            <div className="col-span-5 flex flex-row justify-between w-full text-center italic">
                                                                <div className="flex flex-row w-full grid grid-cols-5 gap-4 text-sm items-center">
                                                                <div className="col-span-2 relative">
                                                                    <input type={`text`} 
                                                                    className="block py-1 px-0 w-full text-sm text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-white focus:outline-none focus:ring-0 focus:border-blue-600 peer placeholder-black text-center italic"
                                                                    name="start_date" onChange={this.handleKeypressed} 
                                                                    value={`${(UtilService.toStringNull(this.state.input.start_date) && this.state.input.start_date != this.state.maxDate) ? this.state.input.start_date : ''}`} 
                                                                    placeholder={`Date of onset diagnosis - default TODAY`}/>
                                                                    
                                                                    {
                                                                        (UtilService.toStringNull(this.state.input.start_date) && this.state.input.start_date != this.state.maxDate) ? 
                                                                        <svg xmlns="http://www.w3.org/2000/svg" onClick={()=>this.handleCancelDate(true)} viewBox="0 0 24 24" fill="currentColor" className="w-4 h-4 cursor-pointer absolute right-0 top-1">
                                                                            <path fillRule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clipRule="evenodd" />
                                                                        </svg>
                                                                        :null
                                                                    }
                                                                    

                                                                </div>
                                                                <div className="flex flex-row space-x-4 bg-white h-8 border border-black items-center justify-center rounded-xl">
                                                                    <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    onClick={() => this.handleShowPicker(true)}
                                                                    className="h-5 w-5 cursor-pointer mr-2"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke="currentColor"
                                                                    strokeWidth={2}
                                                                    >
                                                                    <path
                                                                        strokeLinecap="round"
                                                                        strokeLinejoin="round"
                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                                    />
                                                                    </svg>
                                                                    <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    className="h-5 w-5"
                                                                    viewBox="0 0 20 20"
                                                                    fill="currentColor"
                                                                    >
                                                                    <path
                                                                        fillRule="evenodd"
                                                                        d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"
                                                                        clipRule="evenodd"
                                                                    />
                                                                    </svg>
                                                                    <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    onClick={() => this.handleShowPicker(false)}
                                                                    className="h-5 w-5 cursor-pointer mr-2"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke="currentColor"
                                                                    strokeWidth={2}
                                                                    >
                                                                    <path
                                                                        strokeLinecap="round"
                                                                        strokeLinejoin="round"
                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                                    />
                                                                    </svg>
                                                                </div>
                                                                <div
                                                                    className="col-span-2 relative"
                                                                >
                                                                    <input type={`text`} 
                                                                    className="block py-1 px-0 w-full text-sm text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-white focus:outline-none focus:ring-0 focus:border-blue-600 peer placeholder-black text-center italic"
                                                                    name="end_date" onChange={this.handleKeypressed} 
                                                                    value={`${UtilService.toStringNull(this.state.input.end_date) != null && this.state.input.end_date !=this.state.maxDate ? this.state.input.end_date : ''}`}
                                                                    placeholder={`End date - Default 2 weeks if acute, N/A if Chronic`} />
                                                                    {
                                                                        (UtilService.toStringNull(this.state.input.end_date) != null && this.state.input.end_date !=this.state.maxDate) ? 
                                                                        <svg xmlns="http://www.w3.org/2000/svg" onClick={()=>this.handleCancelDate(false)} viewBox="0 0 24 24" fill="currentColor" className="w-4 h-4 cursor-pointer absolute right-1 top-1">
                                                                            <path fillRule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clipRule="evenodd" />
                                                                        </svg>
                                                                        :null
                                                                    }
                                                                   
                                                                </div>
                                                                </div>
                                                            </div>
                                                            </div>
                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-6 gap-6 py-1 rounded-xl items-center">
                                                            <div className="col-span-3 flex flex-row w-full grid grid-cols-3 gap-3">
                                                                <div className="font-black font-sfuidisplayBlack">
                                                                Occurrence
                                                                </div>
                                                                <div className="col-span-2 flex w-full items-center justify-center">
                                                                {this.state.occurrence_options.length > 0 ? (
                                                                    <Dropdown
                                                                    data={this.state.occurrence_options}
                                                                    handleChange={this.handleOccurrences}
                                                                    reset={this.state.isReset}
                                                                    defaultValue={`${this.state.input.occurrence}`}
                                                                    DropdownClass={`bg-white text-black`}
                                                                    placeholder={`Select occurrence`}
                                                                    DropdownListClass={`flex w-full text-black hover:bg-gray-200`}
                                                                    className={`mx-auto text-black italic font-bold text-sm px-6 border border-black rounded-xl py-1 outline-none focus:outline-none`}
                                                                    />
                                                                ) :""}
                                                                </div>
                                                            </div>
                                                            <div className="col-span-1"></div>
                                                            <div className="col-span-2 flex flex-row w-full grid grid-cols-3 gap-3">
                                                                <div className="font-black font-sfuidisplayBlack">
                                                                Verification
                                                                </div>
                                                                <div className="col-span-2 items-center">
                                                                {this.state.verification_options.length > 0 ? (
                                                                    <Dropdown
                                                                    data={this.state.verification_options}
                                                                    handleChange={this.handleVerification}
                                                                    reset={this.state.isReset}
                                                                    defaultValue={this.state.input.verification}
                                                                    DropdownClass={`bg-white text-black`}
                                                                    placeholder={`Confirmed?`}
                                                                    DropdownListClass={`flex w-full text-black hover:bg-gray-200`}
                                                                    className={`flex w-full text-black italic font-bold text-sm px-6 border border-black rounded-xl py-1 outline-none focus:outline-none`}
                                                                    />
                                                                ) :""}
                                                                </div>
                                                            </div>
                                                            </div>
                                                            {this.state.timeline.id == 3 ? (
                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-6 gap-6 py-1 rounded-xl items-center">
                                                                <div className="col-span-3 flex flex-row w-full grid grid-cols-3 gap-3">
                                                                <div className="font-black font-sfuidisplayBlack">
                                                                    Severity
                                                                </div>
                                                                <div className="col-span-2 flex w-full items-center justify-center">
                                                                    {this.state.severity_options.length > 0 ? (
                                                                    <Dropdown
                                                                        data={this.state.severity_options}
                                                                        handleChange={this.handleSeverity}
                                                                        reset={this.state.isReset}
                                                                        defaultValue={`${this.state.input.severity}`}
                                                                        DropdownClass={`bg-white text-black`}
                                                                        placeholder={`Unassigned`}
                                                                        DropdownListClass={`flex w-full text-black hover:bg-gray-200`}
                                                                        className={`mx-auto text-black italic font-bold text-sm px-6 border border-black rounded-xl py-1 outline-none focus:outline-none`}
                                                                    />
                                                                    ) :""}
                                                                </div>
                                                                </div>
                                                                <div className="col-span-1"></div>
                                                                <div className="col-span-2 flex flex-row w-full grid grid-cols-3 gap-3">
                                                                <div className="font-black font-sfuidisplayBlack">
                                                                    Reaction
                                                                </div>
                                                                <div className="col-span-2 items-center">
                                                                    {this.state.reaction_options.length > 0 ? (
                                                                    <Dropdown
                                                                        data={this.state.reaction_options}
                                                                        handleChange={this.handleReaction}
                                                                        reset={this.state.isReset}
                                                                        DropdownClass={`bg-white text-black`}
                                                                        placeholder={`Unassigned`}
                                                                        DropdownListClass={`flex w-full text-black hover:bg-gray-200`}
                                                                        className={`flex w-full text-black italic font-bold text-sm px-6 border border-black rounded-xl py-1 outline-none focus:outline-none`}
                                                                    />
                                                                    ) :""}
                                                                </div>
                                                                </div>
                                                            </div>
                                                            ) :""}
                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-4 gap-4 py-1 rounded-xl">
                                                            <div className="font-black font-sfuidisplayBlack">
                                                                Referred By
                                                            </div>
                                                            <div className="col-span-3 w-full text-center italic">
                                                                <div className="flex w-full items-center justify-center">
                                                                <div className="relative w-full group">
                                                                    <input
                                                                    type="text"
                                                                    name="referred_by"
                                                                    onChange={this.handleChange}
                                                                    value={UtilService.toString(this.state.input.referred_by)}
                                                                    className="block py-1 px-0 w-full text-sm text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-white focus:outline-none focus:ring-0 focus:border-blue-600 peer placeholder-black text-center italic"
                                                                    placeholder="Optional"
                                                                    />
                                                                </div>
                                                                </div>
                                                            </div>
                                                            </div>
                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-4 gap-4 py-1 rounded-xl">
                                                            <div className="font-black font-sfuidisplayBlack">
                                                                Comments
                                                            </div>
                                                            <div className="col-span-3 w-full text-center italic">
                                                                <div className="flex w-full items-center justify-center">
                                                                <div className="relative w-full group">
                                                                    <textarea
                                                                    type="text"
                                                                    name="comments"
                                                                    onChange={this.handleChange}
                                                                    value={UtilService.toString(this.state.input.comments)}
                                                                    className="block py-1 px-0 w-full text-sm text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-white focus:outline-none focus:ring-0 focus:border-blue-600 peer placeholder-black text-center italic"
                                                                    placeholder="Optional"
                                                                    ></textarea>
                                                                </div>
                                                                </div>
                                                            </div>
                                                            </div>
                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-4 gap-4 py-1 rounded-xl">
                                                            <div className="font-black font-sfuidisplayBlack">
                                                                Outcome
                                                            </div>
                                                            <div className="col-span-3 w-full text-center italic">
                                                                <div className="flex w-full items-center justify-center">
                                                                {this.state.outcome_list.length > 0 ? (
                                                                    <Dropdown
                                                                    data={this.state.outcome_list}
                                                                    handleChange={this.handleOutcome}
                                                                    reset={this.state.isReset}
                                                                    defaultValue={`${this.state.input.outcome}`}
                                                                    DropdownClass={`bg-white text-black`}
                                                                    placeholder={`Improved?`}
                                                                    DropdownListClass={`flex w-full text-black hover:bg-gray-200`}
                                                                    className={`mx-auto text-black italic font-bold text-sm px-6 border border-black rounded-xl py-1 outline-none focus:outline-none`}
                                                                    />
                                                                ) :""}
                                                                </div>
                                                            </div>
                                                            </div>
                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-4 gap-4 py-1 rounded-xl">
                                                            <div className="font-black font-sfuidisplayBlack">
                                                                Destination
                                                            </div>
                                                            <div className="col-span-3 w-full text-center italic">
                                                                <div className="flex w-full items-center justify-center">
                                                                <div className="relative w-full group">
                                                                    <input
                                                                    type="text"
                                                                    name="destination"
                                                                    onChange={this.handleChange}
                                                                    value={UtilService.toString(this.state.input.destination)}
                                                                    className="block py-1 px-0 w-full text-sm text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-white focus:outline-none focus:ring-0 focus:border-blue-600 peer placeholder-black text-center italic"
                                                                    placeholder="Optional"
                                                                    />
                                                                </div>
                                                                </div>
                                                            </div>
                                                            </div>

                                                            {this.state.timeline.id == 2 ? (
                                                            <div className="flex w-full bg-primaryRedColor grid grid-cols-6 gap-6 py-1 rounded-xl items-center">
                                                                <div className="col-span-3 flex flex-row w-full grid grid-cols-3 gap-3">
                                                                <div className="font-black font-sfuidisplayBlack">
                                                                    Med Usage
                                                                </div>
                                                                <div className="col-span-2 flex w-full items-center justify-center">
                                                                    {this.state.med_usage_list.length > 0 ? (
                                                                    <Dropdown
                                                                        data={this.state.med_usage_list}
                                                                        reset={this.state.isReset}
                                                                        handleChange={this.handleMedUsage}
                                                                        DropdownClass={`bg-white text-black`}
                                                                        placeholder={`Outpatient`}
                                                                        DropdownListClass={`flex w-full text-black hover:bg-gray-200`}
                                                                        className={`mx-auto text-black italic font-bold text-sm px-6 border border-black rounded-xl py-1 outline-none focus:outline-none`}
                                                                    />
                                                                    ) :""}
                                                                </div>
                                                                </div>
                                                                <div className="col-span-3 flex flex-row w-full">
                                                                <div className="flex w-full font-black font-sfuidisplayBlack ">
                                                                    Medication Request Intent
                                                                </div>
                                                                <div className="flex w-full items-center justify-end">
                                                                    {this.state.medication_request_list.length > 0 ? (
                                                                    <Dropdown
                                                                        data={this.state.medication_request_list}
                                                                        handleChange={this.handleMedicationRequest}
                                                                        reset={this.state.isReset}
                                                                        DropdownClass={`bg-white text-black`}
                                                                        placeholder={`Confirmed?`}
                                                                        DropdownListClass={`flex w-full text-black hover:bg-gray-200`}
                                                                        className={`flex w-full text-black italic font-bold text-sm px-6 border border-black rounded-xl py-1 outline-none focus:outline-none`}
                                                                    />
                                                                    ) :""}
                                                                </div>
                                                                </div>
                                                            </div>
                                                            ) :""}
                                                        </div>
                                                        </div>
                                                    </div>
                                                    )}
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           
                    </>
                    :null
                }
            
               </>
            
        );
    }
}

export default  Index;