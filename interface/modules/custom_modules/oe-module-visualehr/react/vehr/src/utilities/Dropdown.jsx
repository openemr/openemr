
import React, { Component } from 'react';
import Popper from "popper.js";
import ClickAwayListener from 'react-click-away-listener';
import { LazyLoadImage } from 'react-lazy-load-image-component';
import 'react-lazy-load-image-component/src/effects/blur.css';

class DropDown extends Component{

  constructor(props) {
    super(props);

    this.popoverDropdownRef = React.createRef();
    this.btnDropdownRef = React.createRef();
    
    this.state = {
        dropdownPopoverShow:false,
        selectedList : {id:0,name: null,icon: null,data:null},
        selectedMultiple:[],
        reset:false,
        intervalId:0,
        bgColor:'bg-white',
        input :{
          search:''
        }
      }
      this.handleChange = this.handleChange.bind(this);
  }
  componentDidUpdate=(prevProps, prevState)=>{
    if(this.props.reset && (this.props.reset != prevProps.reset)){
      this.setState({reset:true},this.handleDefault)
    }else{
      if(prevProps.selectedData !=undefined && prevProps.selectedData!=null){
        if(prevProps.selectedData.length >0 && this.state.selectedMultiple.length==0){
          this.setState({selectedMultiple:prevProps.selectedData})
          this.props.handleChange(prevProps.selectedData)
        } 
      }
    }
       
}
  componentDidMount(){
    setTimeout(this.handleProcess.bind(this), 1000)
  }
  handleProcess=()=>{
    this.handleDefault()
  }
  handleDefault=()=>{
    if(this.props.defaultValue != undefined && this.props.defaultValue != null && this.props.data != undefined){
      
      var data  = this.props.data.find(
        (x) => (x.id === this.props.defaultValue || x.name === this.props.defaultValue)
      )
      if(data != undefined){
        let selectList = this.state.selectedList
       // console.log(data)
        selectList['id'] = data?.id
        selectList['name'] = data?.name
        selectList['icon'] = data?.icon
        selectList['data'] = data?.data 
        this.setState({selectList,reset:false})
        this.props.handleChange(selectList)
      }
    }
  }

  handleClickAway=()=>{
    this.closeDropdownPopover()
  }
  handleChange = (event)=> {
    let input = this.state.input;
    input[event.target.name] = event.target.value;
    this.setState({
      input
    });
    var data = event.target.value;
}
  openDropdownPopover=(e)=>{
    new Popper(this.btnDropdownRef.current, this.popoverDropdownRef.current, {
        placement: "bottom-start"
      });
    this.setState({dropdownPopoverShow:true})
  }
  removeItem = (object, key, value)=> {
    if (value == undefined)
        return;

    for (var i in object) {
        if (object[i][key] == value) {
            object.splice(i, 1);
        }
    }
    return object
  };
  handleSearch=(collection,data)=>{
    return collection.find(obj => {
      return obj.id === data;
    });
  }
  handleSelectMessage=(data)=>{
     return data.map(object => object.name).join(', ')
  }
  handleSelectedItem=(data)=>{
    console.log(data)
    var is_multiple = !(this.props.is_multiple == undefined || this.props.is_multiple ==false)

    if(is_multiple){
        let {selectedMultiple} = this.state
        var datafound = this.state.selectedMultiple.find(obj => {
          return obj.id === data.id;
        });
        if(!datafound){
          selectedMultiple.push(data)
          this.setState({selectedMultiple})
    }else{
      selectedMultiple = this.removeItem(this.state.selectedMultiple,"id",data.id)
      this.setState({selectedMultiple})
    }

    }else{
      this.setState({selectedList:data})
    }
    
    if(this.props.handleDropdownChange !=undefined){
      this.props.handleDropdownChange((is_multiple) ? this.state.selectedMultiple :data ,this.props.type)
    }else if(this.props.handleChange != undefined){
        this.props.handleChange((is_multiple) ? this.state.selectedMultiple : data)
    }
    if(!is_multiple){
      this.closeDropdownPopover()
    }else{
      this.props.handleChange(this.state.selectedMultiple)
    }
      
  }
  closeDropdownPopover=(e)=>{
    this.setState({dropdownPopoverShow:false})
  }
  render(){
    return (
      <>
       
        <ClickAwayListener onClickAway={this.handleClickAway}>
            <div className="flex flex-col w-full">
            <div className={`${this.props.className} cursor-pointer`} type="button" ref={this.btnDropdownRef} onClick={() => { this.state.dropdownPopoverShow ? this.closeDropdownPopover() : this.openDropdownPopover()}} >
                           <div className="flex flex-col w-full">
                             
                              <div className="flex w-full justify-between">
                                  <div className={`flex mr-2 dark:text-white w-full ${this.props.selectedRef}`}>
                                    {
                                      (this.props.is_multiple !=undefined && this.props.is_multiple && this.state.selectedMultiple.length > 0) ? this.handleSelectMessage(this.state.selectedMultiple) 
                                      : (this.state.selectedList.name != null) ? <div className="flex w-full">{ (this.state.selectedList.icon != null ) ? <LazyLoadImage className="object-fill w-4 h-4 mr-2 items-center" alt={`image`} src={`${this.state.selectedList.icon}`} />:""}{this.state.selectedList.name}</div>
                                      : (this.props.placeholder !=undefined) ? this.props.placeholder : null
                                    }
                                
                                  </div>
                                  <div>
                                      {
                                          (!this.state.dropdownPopoverShow) ? 
                                          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 dark:text-white" viewBox="0 0 20 20" fill="currentColor">
                                              <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                                          </svg>
                                          :
                                          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 dark:text-white" viewBox="0 0 20 20" fill="currentColor">
                                              <path fillRule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clipRule="evenodd" />
                                          </svg>
                                      }
                                  </div>
                              </div>
                           </div>
                           
                          
            </div>
                {
                    (this.props.data !== undefined && this.props.data?.length >0) ? 
                        <div>
                          <div ref={this.popoverDropdownRef}
                                className={
                                      (this.state.dropdownPopoverShow ? "inline-block " : "hidden ") +
                                      (this.props.dropdownClass)+
                                      "absolute z-50 text-base w-64 float-left py-2 bg-white dark:bg-gray-700 dark:text-white list-none text-left rounded shadow-lg mt-1"
                                  }
                                  style={{ minWidth: "12rem" }}
                              >
                              
                              <div className="flex w-full flex-col max-h-96 overflow-auto bg-white dark:bg-gray-700 dark:text-white">
                              {
                                  this.props.data?.filter(name => (name.name !==undefined || name.name !=="") ? name.name+"".toLowerCase().includes(this.state.input.search.toLowerCase()) : false).map((data,i)=>{
                                    return <div key={`${i}`}
                                          className={`flex w-full text-sm py-2 px-4 font-normal dark:text-white cursor-pointer block w-full whitespace-no-wrap hover:bg-gray-100 dark:hover:bg-gray-500 ${this.props.dropdownListClass} ${ (this.props.is_multiple != undefined && this.props.is_multiple) ? (this.handleSearch(this.state.selectedMultiple,data.id)) ? 'font-sfuidisplayBlack' :'font-normal' : (this.state.selectedList.id == data.id) ? 'font-sfuidisplayBlack' : 'font-normal'} `}
                                          onClick={e => this.handleSelectedItem(data)}
                                      >
                                        <div className="mr-2">
                                            {
                                              (data.icon != null ) ? <LazyLoadImage className="object-fill w-4 h-4" alt={`image`} src={`${data.icon}`} />:""
                                            }
                                        </div>
                                          {data.name}
                                      </div>
                                  })
                              }
                              </div>
                          </div>
                        </div>
                        :null
                        }
                      
          
            </div>
           
        </ClickAwayListener>
            
      </>
      
    );
  }
  
}

export default DropDown;

