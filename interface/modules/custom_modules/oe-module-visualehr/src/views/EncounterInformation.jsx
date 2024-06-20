import React, { Component } from "react";
import ClickAwayListener from "react-click-away-listener";
import { rxService } from '../services/Services';
import {UtilService} from '../services/UtilService'
import { UPDATE } from '../utilities/constants';
import EncounterCard from './EncounterCard'
import { format } from "date-fns";
class MiniInformation extends Component {
  constructor(props) {
    super(props);

    this.state = {
      data: {},
      currentTab:0,
      showEncounter:false
    };
  }
  handleToggle = () => {
    this.setState({showEncounter:false})
  };
  handleOnClick = () => {
     this.handleToggle()
     rxService.sendUpdate(UPDATE,this.props.data);
  };
  
handleTab=(tab)=>{
  this.setState({currentTab:tab});
}
  render() {
    return (
      <>
        <div className="w-full md:w-[45rem] m-auto bg-primaryRedBgColor min-h-[25rem] relative border-0 rounded-xl border-black overflow-hidden md:max-w-4xl ">
                <div className="md:flex">
                  <div className="sm:px-6 w-full">
                    <div className="py-4">
                        <div className="py-1 text-sm">
                          <div className="sm:flex w-full items-center justify-between">
                            <div className="flex w-full items-center justify-center">
                                <div onClick={()=>this.handleTab(0)} className={`rounded-full focus:outline-none focus:ring-2 cursor-pointer  focus:bg-primaryRedDeepColor hover:text-white focus:ring-primaryRedDeepColor`}>
                                    <div className={`py-2 px-8 ${(this.state.currentTab==0) ? 'bg-primaryRedDeepColor text-white font-sfuidisplayBlack' : 'bg-primaryRedColor text-black'} rounded-full`}>
                                        <p>Encounter</p>
                                    </div>
                                </div>
                                <div onClick={()=>this.handleTab(1)} className={`rounded-full focus:outline-none focus:ring-2 cursor-pointer focus:bg-primaryRedDeepColor focus:ring-primaryRedDeepColor ml-4 sm:ml-8`}>
                                    <div className={`py-2 px-8 ${(this.state.currentTab==1) ? 'bg-primaryRedDeepColor text-white font-sfuidisplayBlack' : 'bg-primaryRedColor text-black'} hover:text-white hover:bg-primaryRedDeepColor rounded-full `}>
                                        <p>Vitals</p>
                                    </div>
                                </div>
                                <div onClick={()=>this.handleTab(2)} className="rounded-full focus:outline-none focus:ring-2 cursor-pointer focus:bg-primaryRedDeepColor focus:ring-primaryRedDeepColor ml-4 sm:ml-8">
                                    <div className={`py-2 px-8 ${(this.state.currentTab==2) ? 'bg-primaryRedDeepColor text-white font-sfuidisplayBlack' : 'bg-primaryRedColor text-black'} hover:text-white hover:bg-primaryRedDeepColor rounded-full`}>
                                        <p>Lab Test</p>
                                    </div>
                                </div>
                            </div>
                          </div>
                        </div>

                        <div className="mt-7 overflow-x-auto">

                          {
                            (this.state.currentTab==0) ?
                              <div className={`flex flex-col w-full`}>
                                    <div className={`flex  grid grid-cols-2`}>
                                <div className="flex flex-col w-full space-y-4 mx-4 my-2">
                                  <div className="flex w-full text-sm">
                                      <div className="mr-2">Reasons:</div>
                                      <div className=" font-sfuidisplayBlack flex-wrap mr-2">
                                        {(this.props.data !==undefined) ? this.props.data.reason : ""}
                                      </div>
                                  </div>
                                  <div className="flex w-full text-sm">
                                    <div className="mr-2">Date of Dx:</div>
                                    <div className=" font-sfuidisplayBlack flex-wrap mr-2">
                                      {(this.props.data !==undefined) ? format(new Date(UtilService.serializeDate(this.props.data.date)), "dd/MM/yyyy") : "-"}
                                    </div>
                                  </div>
                                  <div className="flex w-full text-sm">
                                    <div className="mr-2">Date onset:</div>
                                    <div className=" font-sfuidisplayBlack flex-wrap mr-2">
                                      {(this.props.data !==undefined) ? (!this.props.data.onset_date+"".startsWith("0000")) ? format(new Date(UtilService.serializeDate(this.props.data.onset_date)), "dd/MM/yyyy") : "-" :"-"}
                                    </div>
                                  </div>
                                </div>

                                <div className="flex flex-col w-full space-y-4 mx-4 my-2">
                                  <div className="flex w-full text-sm">
                                    <div className="mr-2">Billing Note:</div>
                                    <div className=" font-sfuidisplayBlack flex-wrap mr-2">
                                      {(this.props.data !==undefined) ? this.props.data.billing_note:""}
                                    </div>
                                  </div>
                                  <div className="flex w-full text-sm">
                                    <div className="mr-2">Facility:</div>
                                    <div className=" font-sfuidisplayBlack flex-wrap mr-2">
                                      {(this.props.data !==undefined) ? this.props.data.facility: ""}
                                    </div>
                                  </div>
                                  <div className="flex w-full text-sm">
                                    <div className="mr-2">Provider ID:</div>
                                    <div className=" font-sfuidisplayBlack flex-wrap mr-2">
                                      {(this.props.data !==undefined) ? this.props.data.provider_id : ""}
                                    </div>
                                  </div>
                                </div>
                              </div>

                                {/* <EncounterCard /> */}
                              </div>
                              :(this.state.currentTab==1) ?
                              <div className={`flex w-full`}>
                                <table className="min-w-full px-2  text-sm">
                                  
                                    <thead className={`bg-red-50`} key={`${UtilService.makeKey(15)}`}>
                                      <tr className={`flex w-full py-2`} key={`${UtilService.makeKey(15)}`}>
                                        {
                                          this.props.vitals.map((data,i)=>{
                                            return <th className={`w-24 px-6 py-4 items-bottom overflow-hidden justify-bottom font-sfuidisplayBlack`} key={`${UtilService.makeKey(15)}`}>{data.name}</th>
                                          })
                                        }
                                      </tr>
                                    </thead>
                                    <tbody>
                                    {
                                      (this.props.data.vitals.length > 0) ? 
                                      this.props.data.vitals.map((vtal,v)=>{
                                        return <tr className={`flex w-full py-2 ${v %2==0 ? 'bg-gray-50' : 'bg-gray-100'}`} key={`${UtilService.makeKey(15)}`}>
                                          {
                                            this.props.vitals.map((vital,i)=>{
                                              var vdata = vital.id === 0 ? `${format(new Date(UtilService.serializeDate(this.props.data.date)), "yyyy-MM-dd")}` : vital.id === 1 ? `${vtal.bpd != null ? `${vtal.bps} / ${vtal.bpd}`: "-"}` : vital.id === 2 ? `${ vtal.height !== null ? parseFloat(vtal.height).toFixed(2) : "-" }` : vital.id === 3 ? `${ vtal.temp_method != null ? vtal.temp_method : "-" }` : vital.id == 4 ? `${vtal.respiration != null ? parseFloat(vtal.respiration).toFixed(2) : "-" }` : vital.id === 5 ? `${ vtal.BMI_status != null  ? vtal.BMI_status : "-" }` : vital.id === 6 ? `${ vtal.oxygen_flow_rate != null ? parseFloat(vtal.oxygen_flow_rate).toFixed(2) : "-" }` : vital.id === 7 ? `${vtal.weight !== null ? parseFloat(vtal.weight).toFixed(2): "-" }` : vital.id === 8 ? `${ vtal.temperature ? parseFloat(vtal.temperature).toFixed(2) : "-" }` : vital.id === 9 ? `${ vtal.pulse ? parseFloat(vtal.pulse).toFixed(2) : "-" }` : vital.id === 10 ? `${ vtal.BMI ? parseFloat(vtal.BMI).toFixed(2) : "-" }` : vital.id === 11 ? `${ vtal.oxygen_saturation ? parseFloat(vtal.oxygen_saturation).toFixed(2) : "-" }` : vital.id === 12 ? `${ vtal.inhaled_oxygen_concentration ? parseFloat(vtal.inhaled_oxygen_concentration).toFixed(2) : "-"}` : null;
                                              return <td className={`w-24 px-2 items-center text-center overflow-hidden justify-center text-sm`} key={`${UtilService.makeKey(15)}`}>{vdata}</td>                                      
                                            })
                                          }
                                        </tr>
                                      })
                                      
                                      :<tr key={`${UtilService.makeKey(15)}`}><td className={`flex w-full items-center px-6 h-24 bg-gray-50`}   colSpan={`${this.props.data.vitals.length}`}>No data found</td></tr>
                                    }
                                  </tbody>
                                </table>
                              </div>
                            :
                            <div className={`flex w-full`} key={`${UtilService.makeKey(15)}`}>
                                <table className="min-w-full px-2  text-sm" key={`${UtilService.makeKey(15)}`}>
                                  
                                    <thead className={`bg-red-50`} key={`${UtilService.makeKey(15)}`}>
                                      <tr className={`flex w-full py-2`} key={`${UtilService.makeKey(15)}`}>
                                        {
                                          this.props.tests.map((data,i)=>{
                                            return <th className={`w-24 px-6 py-4 items-bottom overflow-hidden justify-bottom font-sfuidisplayBlack`} key={`${UtilService.makeKey(15)}`}>{data.name}</th>
                                          })
                                        }
                                      </tr>
                                    </thead>
                                    <tbody key={`${UtilService.makeKey(15)}`}>
                                    {
                                      (this.props.data.labs.length > 0) ? 
                                      this.props.data.labs.map((testdata,v)=>{
                                        return <tr className={`flex w-full py-2 ${v %2==0 ? 'bg-gray-50' : 'bg-gray-100'}`} key={`${UtilService.makeKey(15)}`}>
                                          {
                                            this.props.tests.map((lab,i)=>{
                                              var labData = lab.id == 1 ? testdata.result_text : lab.id == 2 ? testdata.range : lab.id == 3 ? testdata.result : testdata.result_status;
                                              return <td className={`w-24 px-2 items-center text-center overflow-hidden justify-center text-sm`} key={`${UtilService.makeKey(15)}`}>{labData}</td>                                      
                                            })
                                          }
                                        </tr>
                                      })
                                      
                                      :<tr key={`${UtilService.makeKey(15)}`}><td className={`flex w-full items-center px-6 h-24 bg-gray-50`}   colSpan={`${this.props.data.labs.length}`}>No data found</td></tr>
                                    }
                                  </tbody>
                                </table>
                              </div>
                            }
                          
                        </div>
                    </div>
                  </div>
                </div>
                
              </div>
            
      </>
    );
  }
}

export default MiniInformation;
