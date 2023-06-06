
import React from 'react';
import {UtilService} from '../services/UtilService'
import { YEARS,MONTHS,UPDATE } from '../utilities/constants';
import withRouter from '../utilities/WithRouter'
import { rxService } from '../services/Services';
import { format } from "date-fns";
import Tooltip,{tooltipClasses} from "@mui/material/Tooltip";
import { styled } from '@mui/material/styles';
import MiniInformation from './MiniInformation';
import EncounterInformation from './EncounterInformation';

const HtmlTooltip = styled(({ className, ...props }) => (
    <Tooltip {...props} classes={{ popper: className }} />
  ))(({ theme }) => ({
    [`& .${tooltipClasses.tooltip}`]: {
      backgroundColor: '#f2e4e5',
      maxWidth: 600,
      color: 'rgba(0, 0, 0, 0.87)',
    },
  }));

class Tiles extends React.Component {

    constructor(props) {
        super(props);

        this.zooming = React.createRef();
        this.state = {
            timelines: [],
            encounterLimit: -1,
            limit: -5,
            containerWidth:UtilService.zoomMinWidth(1),
            viewType: YEARS
        };
    }

    componentDidMount(){
        
    }
    handleIndictor = (timeline, data) => {
        rxService.sendUpdate(UPDATE,data);
    };

    handleEncounter=(encounter)=>{
        rxService.sendEncounterRequest(encounter)
    }

    handleContainer=(index,date,lastDate)=>{
        var loadedEncounter = this.props.viewType == YEARS ? this.props.encounters.years : (this.props.viewType == MONTHS) ? this.props.encounters.months : this.props.encounters.days;
        return <div className="flex flex-col" style={{transition: `width 0.5s`,width:`${this.props.tileWidth}px`}} key={`${UtilService.makeKey(15)}`}  ref={this.zooming}>
                {
                    (index==0) ? 
                        <div className={`font-black h-8 py-1 overflow-hidden font-sfuidisplayBlack`} key={`${UtilService.makeKey(15)}`} title={`${date}`}>{date}</div>
                    :null
                }
                {
                    (this.props.toogleEncounter) ? 
                        (this.props.group.id !=0) ? 
                        <div className={`flex w-full z-50 relative`} key={`${UtilService.makeKey(15)}`}>
                            {this.handleEncounterOverlay(loadedEncounter,date,lastDate)}
                        </div>
                        :null
                        
                    :null
                }
                <div className={`flex flex-col w-full z-10`} key={`${UtilService.makeKey(15)}`}>
                    {
                        (this.props.timelines?.length > 0 && this.props.collapsed) ?
                            <div className="flex flex-col w-full">
                                {
                                    this.handleTitles(loadedEncounter,date,lastDate)
                                }
                                <div key={`${UtilService.makeKey(15)}`} className={`w-full bg-black h-8`} ></div>
                            </div>
                        :
                        <div className={`flex w-full h-16`} key={`${UtilService.makeKey(15)}`}>
                            <div className={`uppercase flex z-20 font-sfuidisplayBlack text-end justify-end border-b border-black items-center w-full h-16 px-2 bg-secondaryColor`} key={`${UtilService.makeKey(15)}`}>
                                {
                                    (date.toString().startsWith(lastDate)) ? this.props.group.name : null
                                }
                            </div>
                        </div>
                    }
                    
                </div>
        </div>
    }
    /**
     * 
     * @param {*} handleTitles 
     * @param {*} date 
     * @param {*} lastDate 16/10/2022
     * 
     * display tiles name
     * create new enddate
     */

    handleTitles=(loadedEncounter,date,lastDate)=>{
        return <div className={`flex flex-col w-full z-20`} key={`${UtilService.makeKey(15)}`}>
            {
                (this.props.timelines?.length > 0 && (this.props.group.id !== 7 && this.props.group.id !==8)) ? 
                    this.props.timelines?.map((data,i)=>{
                        return <div className={`flex flex-col w-full`} key={`${UtilService.makeKey(15)}`}>
                            {
                                data?.prescriptions.map((prescribe,p)=>{
                                    var lastIndex = (data.prescriptions.length -1 ) === p
                                     var startDate = UtilService.serializeDate(prescribe.startDate)
                                     var edate = prescribe.endDate != null ? (!lastIndex) ? UtilService.serializeDate(prescribe.endDate) :UtilService.serializeEndDate(UtilService.serializeDate(prescribe.endDate),this.props.viewType): format(new Date(), "yyyy-MM-dd");
                                     edate = UtilService.serializeDate(edate)
                                     var isVisible = UtilService.handleIsInDateRange(startDate,edate,date,this.props.viewType);
                                     var hasTitle = UtilService.hasTitleShow( edate,date)

                                     return (prescribe.startDate !=null && edate != null) ? <div className={`flex w-full h-8`} key={`${UtilService.makeKey(15)}`}>
                                     {
                                         (isVisible) ? <div className={`flex w-full`}>
                                             <HtmlTooltip
                                                 arrow key={`${UtilService.makeKey(15)}`}
                                                 placement="top-start"
                                                 followCursor={true}
                                                 title={
                                                 <React.Fragment>
                                                    <MiniInformation data={prescribe}/>
                                                 </React.Fragment>
                                                 }
                                             >
                                                 <div key={`${UtilService.makeKey(15)}`} onClick={()=>this.handleIndictor(this.props.group,prescribe)} className={`w-full h-8 cursor-pointer text-end justify-end items-center px-1 py-1 overflow-hidden text-maxlines border-b border-black ${(hasTitle) ? 'border-r border-black' :'border-b'}`} style={{background:`${prescribe.color}`}}>
                                                     {(hasTitle) ? prescribe.name : ""}
                                                 </div>
                                             </HtmlTooltip>
                                             </div>
                                         :<div></div>
                                     }
                                 </div>
                                 :null
                                })
                            }
                        </div>
                    })
                :(this.props.group.id === 7) ? 
                this.props.vitals.map((vital,i)=>{
                    return <div className={`flex w-full h-8 justify-end items-center text-end border-b border-black`} key={`${UtilService.makeKey(15)}`}>
                        {
                            (date.toString().startsWith(lastDate)) ? 
                                <div className={`flex w-full grid grid-cols-2 gap-2`} key={`${UtilService.makeKey(15)}`}>
                                    <div className={`flex w-full`} key={`${UtilService.makeKey(15)}`}>
                                        {this.handleVitals(loadedEncounter,vital,date,true)}
                                    </div>
                                    <div className={`col-span-1 flex w-full text-end justify-end items-center  px-1 py-1 overflow-hidden text-maxlines cursor-pointer`} key={`${UtilService.makeKey(15)}`} title={`${vital.name}`}>{vital.name}</div>
                                </div> 
                            :<div className={`flex w-full`} key={`${UtilService.makeKey(15)}`}>
                                {this.handleVitals(loadedEncounter,vital,date,false)}
                            </div>
                        }
                    </div>
                })
                :(this.props.group.id === 8) ? 
                this.props.tests.map((data,i)=>{
                    return <div className={`flex w-full h-8 justify-end items-center text-end border-b border-black`} key={`${UtilService.makeKey(15)}`}>
                        {
                            (date.toString().startsWith(lastDate)) ? 
                                <div className={`flex w-full grid grid-cols-2 gap-2`} key={`${UtilService.makeKey(15)}`}>
                                    <div className={`col-span-1 flex w-full justify-center text-center items-center  px-1 py-1 overflow-hidden text-maxlines `} key={`${UtilService.makeKey(15)}`}>{this.handleLabTest(loadedEncounter,data,date,true)}</div>
                                    <div className={`col-span-1 flex w-full text-end justify-end items-center  px-1 py-1 overflow-hidden text-maxlines `} key={`${UtilService.makeKey(15)}`}>{data.name}</div>
                                </div> 
                            :<div className={`flex w-full`} key={`${UtilService.makeKey(15)}`}>
                                {this.handleLabTest(loadedEncounter,data,date,false)}
                            </div>
                        }
                    </div>
                })
                :null
            }
        </div>
    }
    handleEncounterOverlay=(loadedEncounter,getdate,lastDate)=>{
        
        return <div
            className="absolute z-50 top-0 left-0 bottom-0 w-full h-full" style={{height:`1300px`}} key={`${UtilService.makeKey(15)}`}
        >
        <div className="flex w-full h-full" key={`${UtilService.makeKey(15)}`}>
          <div className="flex absolute flex-row w-full justify-center mx-auto  h-full" key={`${UtilService.makeKey(15)}`}>
            <div className={`flex w-full`}>
                {(getdate.toString().startsWith(lastDate)) ? 
                        <div className={`flex w-full grid grid-cols-2 gap-2`} key={`${UtilService.makeKey(15)}`}>
                            <div className={`col-span-1 flex w-full`} key={`${UtilService.makeKey(15)}`}>
                                
                                {this.handleLoadedEncounter(loadedEncounter,getdate,lastDate,true)}
                            </div>
                            <div className={`col-span-1 flex w-full text-end justify-end items-center overflow-hidden text-maxlines `} key={`${UtilService.makeKey(15)}`}></div>
                        </div> 
                        :<div className={`flex w-full`} key={`${UtilService.makeKey(15)}`}>
                            {this.handleLoadedEncounter(loadedEncounter,getdate,lastDate,false)}
                        </div>
                }
            </div>
          </div>
        </div>
      </div>
    }
    handleLoadedEncounter=(loadedEncounter,getdate,lastDate,isMin)=>{
        return loadedEncounter.hasOwnProperty(getdate)
        ? loadedEncounter[getdate]
                .slice(isMin ? -2 : this.state.limit)
                .map((data, i) => {
                    var startdate = format(new Date(UtilService.serializeDate(data.date)),"yyyy-MM-dd");
                    var currentdate = format(new Date(), "yyyy-MM-dd");
                    return startdate.startsWith(getdate)
                    ? data.encounters.length > 0
                      ? data.encounters
                          .slice(this.state.encounterLimit)
                          .map((encounter, e) => {
                              
                            return (
                                <div className={`flex w-full`} key={`${UtilService.makeKey(15)}`}>
                                     <HtmlTooltip
                                        arrow key={`${UtilService.makeKey(15)}`}
                                        placement="left-start"
                                        title={
                                            <React.Fragment key={`${UtilService.makeKey(15)}`}>
                                                <EncounterInformation data={encounter} key={`${UtilService.makeKey(15)}`} vitals={this.props.vitals} tests={this.props.tests}/>
                                            </React.Fragment>
                                        }
                                    >
                                    <div onClick={()=>this.handleEncounter(encounter)} className={`w-24 z-50 cursor-pointer  border-[1px] border-gray-50 overflow-hidden h-full justify-center items-center ${UtilService.serializeDate(encounter.date).toString().startsWith(currentdate) ? "bg-yellow-300/25": "bg-green-100/25"}`} key={`${UtilService.makeKey(15)}`}>
                                        <div className={`w-full h-full ${UtilService.serializeDate(encounter.date).toString().startsWith(currentdate) ? "bg-yellow-300/30 hover:bg-yellow-500/30": "bg-green-300/30 hover:bg-green-500/30"}`} key={`${UtilService.makeKey(15)}`}></div>
                                    </div>
                                    </HtmlTooltip>
                                </div>
                                
                            );
                          })
                      : null
                    : null;
                })
        :null
    }
    handleVitals=(loadedEncounter,vital,date,isMin)=>{
        return loadedEncounter.hasOwnProperty(date) ? 
            loadedEncounter[date].slice(isMin ? -2 : this.state.limit)
            .map((data, i) => {
                var startdate = format(new Date(UtilService.serializeDate(data.date)),"yyyy-MM-dd");
                return startdate.startsWith(date) ? 
                    data.encounters.length > 0 ? 
                        data.encounters.slice(this.state.encounterLimit)
                        .map((encounter, e) => {
                            return encounter.vitals.length >
                              0 ? (
                              encounter.vitals .slice(-1).map((vtal, v) => {
                                  var vdata = vital.id === 0 ? `${UtilService.serializeDate(encounter.date)}` : vital.id === 1 ? `${vtal.bpd != null ? `${vtal.bps} / ${vtal.bpd}`: "-"}` : vital.id === 2 ? `${ vtal.height !== null ? parseFloat(vtal.height).toFixed(2) : "-" }` : vital.id === 3 ? `${ vtal.temp_method != null ? vtal.temp_method : "-" }` : vital.id == 4 ? `${vtal.respiration != null ? parseFloat(vtal.respiration).toFixed(2) : "-" }` : vital.id === 5 ? `${ vtal.BMI_status != null  ? vtal.BMI_status : "-" }` : vital.id === 6 ? `${ vtal.oxygen_flow_rate != null ? parseFloat(vtal.oxygen_flow_rate).toFixed(2) : "-" }` : vital.id === 7 ? `${vtal.weight !== null ? parseFloat(vtal.weight).toFixed(2): "-" }` : vital.id === 8 ? `${ vtal.temperature ? parseFloat(vtal.temperature).toFixed(2) : "-" }` : vital.id === 9 ? `${ vtal.pulse ? parseFloat(vtal.pulse).toFixed(2) : "-" }` : vital.id === 10 ? `${ vtal.BMI ? parseFloat(vtal.BMI).toFixed(2) : "-" }` : vital.id === 11 ? `${ vtal.oxygen_saturation ? parseFloat(vtal.oxygen_saturation).toFixed(2) : "-" }` : vital.id === 12 ? `${ vtal.inhaled_oxygen_concentration ? parseFloat(vtal.inhaled_oxygen_concentration).toFixed(2) : "-"}` : null;
                                      return (
                                        <div  key={`${UtilService.makeKey(15)}`} className={`w-24 h-8 overflow-hidden items-center  text-maxlines text-sm px-1 text-end ${vital.id == 0 ? " font-sfuidisplayBlack text-xs" : ""} overflow-hidden cursor-pointer font-black `} title={`${vdata}`}>
                                            {vdata}
                                        </div>
                                  );
                                })
                            ) : (
                                <div className="w-24" key={`${UtilService.makeKey(15)}`}></div>
                            );
                        })
                    :null
                :null
            })
        :null
    }
    handleLabTest=(loadedEncounter,lab,getdate,isMin)=>{
        return loadedEncounter.hasOwnProperty(getdate)
        ? loadedEncounter[getdate].slice(isMin ? -2 : this.state.limit).map((data, i) => {
            var startdate = format(new Date(UtilService.serializeDate(data.date)),"yyyy-MM-dd");
            return startdate.startsWith(getdate) ? 
                (data.encounters.length > 0) ? 
                    data.encounters.slice(this.state.encounterLimit).map((encounter, e) => {
                        return encounter.labs.slice(-1).map((testdata, v) => {
                           var labData =lab.id == 1 ? testdata.result_text : lab.id == 2 ? testdata.range : lab.id == 3 ? testdata.result  : testdata.result_status;
                           return (
                            <div key={`${UtilService.makeKey(15)}`}
                              className={`w-24 overflow-hidden text-maxlines text-sm px-1 text-end ${
                                lab.id == 0
                                  ? " font-sfuidisplayBlack"
                                  : ""
                              } overflow-hidden cursor-pointer font-black line-clamp-1`} title={labData}
                            >
                              {labData}
                            </div>
                          );
                        })
                    })
                :null
            :null
        })

        :null
    }
    render() {
        return (
            <div className={`flex flex-col relative`} id={`${this.props.calendar.year}`} key={`${UtilService.makeKey(15)}`} style={{transition: `width 0.5s`}}>
                {
                    (this.props.viewType==YEARS) ?
                        this.handleContainer(this.props.index,this.props.calendar.year,this.props.lastGroup.year)
                    :(this.props.viewType==MONTHS) ? <div className="flex" key={`${UtilService.makeKey(15)}`}>
                        {
                            this.props.calendar.months.map((data,i)=>{
                                var lastGroupData =this.props.lastGroup.months[this.props.lastGroup.months.length-1];
                                return <div className={`flex w-full`} key={`${UtilService.makeKey(15)}`} >{this.handleContainer(this.props.index,data,lastGroupData)}</div>
                            })
                        }
                    </div>
                    :<div className="flex w-full" key={`${UtilService.makeKey(15)}`}>
                    {
                        this.props.calendar.days.map((data,i)=>{
                            var lastGroupData =this.props.lastGroup.days[this.props.lastGroup.days.length-1];
                            return this.handleContainer(this.props.index,data,lastGroupData)
                        })
                    }
                </div>
                }
            </div>
        );
    }
}

export default  withRouter(Tiles);