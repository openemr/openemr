import React from 'react';
import { rxService } from '../services/Services';
import {UtilService} from '../services/UtilService'
import { TimelineService } from '../services/TiimelineService';
import { YEARS,MONTHS,DAYS,ZOOMING_IN,ZOOMING_OUT, } from '../utilities/constants';
import EncounterInformation from './EncounterInformation'
import withRouter from '../utilities/WithRouter'

import Tile from './Tiles'

class Timeline extends React.Component {

    constructor(props) {
        super(props);
        this.container = React.createRef();
        this.parentContainer = React.createRef();
        this.sidebar = React.createRef();
        this.state = {
            timelines: [],
            encounters:[],
            calendars: [],
            zoomData: [],
            hasCtrlPressed:false,
            isZoomingStart: false,
            isZooming:false,
            zoomInitial:0,
            scrollTimer: -1,
            defaultWindowSize: window.clientWidth,
            scrollInProgress: false,
            vitals:[],
            viewType: YEARS,
            tests:[],
            collapsed:[],
            sideWidth:"",
            containerHeight:0,
            tileWidth: UtilService.zoomMinWidth(1),
            containerWidth:0,
            timelineGroup:[],
        };
        this._timeout = null;
        this.handleScroll = this.handleScroll.bind(this);
    }
    
    componentWillUnmount() {
        this.subscription.unsubscribe();
        this.zooming.current.removeEventListener('wheel', this.wheel, { passive: false });
        window.removeEventListener('scroll', this.handleScroll);
    }
    componentDidMount(){
        
        window.addEventListener("resize",this.handleOnWindowResize)
        window.addEventListener('scroll', this.handleScroll);
        this.subscription = rxService.getSubscription().subscribe(data => {

            if(data.data !== undefined){
                if (!data.data.error) {
                    if(data.data.vitals !== undefined){
                        this.setState({timelineGroup: data.data.sidebarlist,tests:data.data.tests,vitals:data.data.vitals})
                    
                    }
                    if(data.data.issues !== undefined){
                        var data = TimelineService.validator(data.data)
                        this.setState({calendars:data.calendars,encounters:data.encouter,timelines:data.timelines},this.handleZooming)
                    }
                }
            }
            
        });
    }
    handleScroll(event) {
        let _self = this
        if(this._timeout){
         clearTimeout(this._timeout);
        }
        this._timeout = setTimeout(() => {
          this._timeout = null;
          _self.setState({
            scrollInProgress:false
          });
        },1000);
        if(!_self.state.scrollInProgress) {
            _self.setState({
            scrollInProgress:true
          });
        }
     }
    handlePinchToZoom=(pinch)=>{
        var set_width = pinch !== ZOOMING_IN ? this.container.current.clientWidth + UtilService.zoomSpeed : this.container.current.clientWidth - UtilService.zoomSpeed;
        set_width = (set_width <=this.state.containerWidth) ? this.state.containerWidth : set_width
        this.container.current.style.setProperty("width",`${set_width}px` );

        var set_tile_width = pinch !== ZOOMING_IN ? this.state.tileWidth + UtilService.zoomSpeed : this.state.tileWidth - UtilService.zoomSpeed;
        set_tile_width = (set_tile_width <=UtilService.tilesWidth(this.state.calendars.length)) ? UtilService.tilesWidth(this.state.calendars.length) : set_tile_width
        this.setState({tileWidth:set_tile_width})
        
        console.log("zooming handler is activated=",set_width,(this.state.containerWidth + 500))
        if(set_width <= (this.state.containerWidth+300)){
            if(this.state.viewType != YEARS){
                this.setState({viewType:YEARS})
            }
        }else{
            if(set_width <= (this.state.containerWidth + 600)){
                if(this.state.viewType != MONTHS){
                    this.setState({viewType:MONTHS})
                }
            }else{
                if(this.state.viewType != DAYS){
                    this.setState({viewType:DAYS})
                }
            }
        }
    }
    negative=(number)=> {
        return !Object.is(Math.abs(number), +number);
    }
    handleZooming=()=>{
        var _self=this
        this.handleContainerHeight()
        var {scrollInProgress} = _self.state.scrollInProgress

        this.container.current.addEventListener('wheel', function(e){
            console.log("on wheel detected = ",e)
            if (e.ctrlKey || e.metaKey) e.preventDefault();
            var position = e.deltaY;
            let { zoomData } = _self.state;
            
            var datafound = _self.state.zoomData.find((obj) => {
                return obj === parseInt(position);
              });
              if (!datafound) {
                zoomData.push(parseInt(position));
              }
              if(!_self.state.scrollInProgress){
                  if (zoomData.length === 4) {
                    var pinch = zoomData[3] >0 ? ZOOMING_IN: ZOOMING_OUT;
                    if(e.deltaX==0){
                        scrollInProgress=true
                        _self.handlePinchToZoom(pinch);
                    }
                
                    if (_self.state.scrollTimer !== -1){
                        clearTimeout(_self.state.scrollTimer);
                    }
                        
                    _self.state.scrollTimer = window.setTimeout(function () {
                        zoomData.length=0
                        scrollInProgress=false
                    }, 900);
                }
              }else{
                zoomData.length=0
              }
              
        }, { passive: false });
        this.container.current.addEventListener( "touchstart",function (e) {
              var x = e.touches.length;
              if (x == 2 && e.cancelable) {
                e.preventDefault();
                if (!_self.state.isZoomingStart) {
                  var positionx = parseInt(e.touches[0].clientX);
                  _self.setState({ isZoomingStart: true, zoomInitial: positionx });
                }
              }
            },
            false
        );
        this.container.current.addEventListener("touchmove", function (e) {
              var x = e.touches.length;
              let { zoomData } = _self.state;
      
              if (x == 2 && e.cancelable) {
                e.preventDefault();
      
                if (!_self.state.isZooming) {
                  var dist = parseInt(
                    Math.hypot(
                      e.touches[0].pageX - e.touches[1].pageX,
                      e.touches[0].pageY - e.touches[1].pageY
                    )
                  );
                  var datafound = _self.state.zoomData.find((obj) => {
                    return obj === parseInt(dist);
                  });
                  if (!datafound) {
                    zoomData.push(parseInt(dist));
                  }
                  if (zoomData.length == 2) {
                    var pinch = zoomData[0] > zoomData[1] ? ZOOMING_OUT : ZOOMING_IN;
                    _self.handlePinchToZoom(pinch);
                  }
                  _self.setState({ isZooming: zoomData.length > 1, zoomData });
                }
              }
            },
            false
        );
        this.container.current.addEventListener(
            "touchend",
            function (e) {
              _self.setState({
                zoomInitial: 0,
                isZooming: false,
                isZoomingStart: false,
                zoomData: [],
              });
            },
            false
          );
        
    }
    handleContainerHeight=()=>{
        let _self = this
        if(this.state.timelines?.length > 0){
            this.sidebar.current.style.setProperty("height",`${this.container.current.clientHeight}px` );
            if(this.state.containerWidth==0){
                this.setState({containerWidth:(UtilService.containerWidth(this.state.calendars.length)+224),tileWidth:UtilService.tilesWidth(this.state.calendars.length)},this.handleContainerWidth)
                setTimeout(function() {
                    _self.parentContainer.current.scrollLeft =  _self.parentContainer.current.scrollWidth;;
                 }, 500);
            }
            this.setState({containerHeight:this.container.current.clientHeight})
        }
    }
    handleContainerWidth=()=>{
        this.container.current.style.setProperty("width",`${(this.state.containerWidth)}px` );
    }
    handleToggleView=(hasTimeline,id)=>{
        let collapsed = this.state.collapsed
        if(hasTimeline){
           collapsed[id]=(collapsed[id]===false) ? true : false
           this.setState({collapsed},this.handleContainerHeight) 
        }
    }
    handleToggleSideBar=()=>{
        var barstate = (this.state.sideWidth=="") ? 1 :""
        var width = this.state.containerWidth + (barstate ? -154 : 154)
        this.setState({sideWidth: barstate,containerWidth:width})
    }
    componentWillUnmount() {
        this.subscription.unsubscribe();
    }

    handleOnWindowResize=(e)=>{
        this.setState({defaultWindowSize:e.windowWidth})
    }
    
    render() {
        return (
            <>
              
              <div className={`flex flex-row w-full`} style={{transition: `width 0.5s`}}>
                <div className={`flex w-full min-h-0 min-w-0 overflow-auto justify-center items-center`} ref={this.parentContainer}>
                
                <div className="flex flex-col flex-1  w-full">
                    <div className={`absolute lg:w-56 z-40 left-0 bg-secondaryColor border-r-2 border-white`} style={{width: `${UtilService.containerMargin(this.state.sideWidth !="",this.state.defaultWindowSize)}px`}} ref={this.sidebar}>
                        <div className={`flex w-full h-screen`}>
                            <div className={`absolute right-0 top-0 bottom-0 w-12 my-auto h-56 justify-end items-end`}>
                                <div onClick={this.handleToggleSideBar} className={`absolute right-0 flex w-5 h-24 z-40 border border-black rounded-full justify-center items-center`}>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="w-6 h-6">
                                        <path fillRule="evenodd" d="M7.72 12.53a.75.75 0 010-1.06l7.5-7.5a.75.75 0 111.06 1.06L9.31 12l6.97 6.97a.75.75 0 11-1.06 1.06l-7.5-7.5z" clipRule="evenodd" />
                                    </svg>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <div ref={this.container} className={`flex flex-col w-full transition-width`} >
                        {
                        (this.state.timelineGroup.length >0 && this.state.timelines.length > 0) ? 
                            this.state.timelineGroup.map((group,groupIndex)=>{
                                var timelines=[]
                                timelines = this.state.timelines.find((obj) => {
                                    return obj.timeline_id === group.id
                                });
                                let collapsed = this.state.collapsed
                                if(collapsed[group.id]===undefined){
                                    collapsed[group.id] = timelines?.data.length > 0
                                }

                                return <div className={`flex  w-full`} key={`${UtilService.makeKey(15)}`}>
                                    <div className={`absolute left-2 z-40 overflow-hidden`} style={{width:`${(this.state.sideWidth !="")? "50px":"192px"}`}}>
                                        {(groupIndex==0) ? <div className={`h-8 w-full`}></div> : null}
                                        <div onClick={()=>this.handleToggleView((timelines?.data.length > 0),group.id)} className={`left-4 flex cursor-pointer rounded justify-center items-center text-white h-14 ${(groupIndex==0) ? 'mt-0':''} bg-red-500`}>
                                            <div className={`flex w-full ${(this.state.sideWidth !="") ? 'justify-start px-1' : 'justify-between px-4'} items-center`}>
                                                <div className={`overflow-hidden`}style={{width:`${(this.state.sideWidth !="")? "1px":"192px"}`}}>{group.name}</div>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className={`w-6 h-6 ${(this.state.collapsed[group.id]) ? 'rotate-0': 'rotate-90'}  ${(this.state.sideWidth !="") ? 'ml-2' :'ml-6'}`}>
                                                    <path fillRule="evenodd" d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z" clipRule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div className={`flex w-full z-20 transition-width`} style={{marginLeft:`${UtilService.containerMargin(this.state.sideWidth !="",this.state.defaultWindowSize)}px`}}>
                                        {
                                            this.state.calendars.map((data,i)=>{
                                                var lastGroupData = this.state.calendars[this.state.calendars.length-1];
                                                return <Tile key={`${UtilService.makeKey(15)}`} tileWidth={this.state.tileWidth} toogleEncounter={this.props.toogleEncounter} index={`${groupIndex}`} handleZooming={this.handleZooming} lastGroup={lastGroupData} group={group} collapsed={this.state.collapsed[group.id]} vitals={this.state.vitals} tests={this.state.tests} viewType={`${this.state.viewType}`} calendar={data} encounters={this.state.encounters} containerHeight={this.state.containerHeight} timelines={timelines?.data}/>
                                            })
                                        }
                                    </div>
                                </div>
                            })
                        :null
                    }
                    </div>
                    
                </div>
                
                </div>
                
            </div>
        
            </>
          );
    }
}

export default  withRouter(Timeline);