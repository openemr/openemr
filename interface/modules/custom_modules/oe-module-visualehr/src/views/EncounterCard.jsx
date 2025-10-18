import React, { Component } from "react";

class EncounterCard extends Component {
  constructor(props) {
    super(props);

    this.state = {
      data: {},
      collapsed:[],
      index:0
    };
  }
  handleToggle=(index)=>{
    let collapsed = this.state.collapsed
    collapsed[index] = (collapsed[index]) ? false :true
    this.setState({collapsed})
  }
  render() {
    return (
      <>
        <div className={`flex  grid grid-cols-3 gap-3 mt-10 font-sfuidisplay`}>
                                    <div className={`flex w-full`}>
                                      <div className={`flex flex-col w-full border border-black overflow-hidden rounded-lg`}>
                                          <div className={`flex flex-col w-full px-3 pb-2`}>
                                                <div className={`flex w-full h-4 bg-primaryColor rounded-b-md justify-center relative items-center text-center uppercase text-white px-2 py-3 text-xs font-sfuidisplayBlack`} onClick={()=>this.handleToggle(0)}>
                                                  <span>Subjective</span>
                                                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className={`w-4 h-4 absolute right-1 top-0.5 ${(this.state.collapsed[0]) ? '-rotate-90': 'rotate-90'}`}>
                                                    <path fillRule="evenodd" d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z" clipRule="evenodd" />
                                                  </svg>
                                                </div>
                                                {
                                                  (!this.state.collapsed[0]) ? 
                                                    <div className={`flex flex-col max-h-72 overflow-auto w-full mb-10`}></div> 
                                                  :null
                                                }
                                                
                                          </div>
                                          
                                          <div className={`flex flex-col w-full px-3 pb-2`}>
                                          <div className={`flex w-full h-4 bg-primaryColor rounded-b-md justify-center relative items-center text-center uppercase text-white px-2 py-3 text-xs font-sfuidisplayBlack`} onClick={()=>this.handleToggle(1)}>
                                                  <span>Objective</span>
                                                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className={`w-4 h-4 absolute right-1 top-0.5 ${(this.state.collapsed[1]) ? '-rotate-90': 'rotate-90'}`}>
                                                    <path fillRule="evenodd" d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z" clipRule="evenodd" />
                                                  </svg>
                                                </div>
                                              {(!this.state.collapsed[1]) ? 
                                                    <div className={`flex flex-col max-h-72 overflow-auto w-full mb-10`}></div> 
                                              :null } 
                                          </div>

                                         
                                      </div>
                                    </div>
                                   
                                    <div className={`flex w-full`}>
                                      <div className={`flex flex-col w-full border border-black overflow-hidden rounded-lg`}>
                                          <div className={`flex flex-col w-full px-3 pb-2`}>
                                                <div className={`flex w-full h-4 bg-primaryColor rounded-b-md justify-center relative items-center text-center uppercase text-white px-2 py-3 text-xs font-sfuidisplayBlack`} onClick={()=>this.handleToggle(2)}>
                                                  <span>Assesment And Plan</span>
                                                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className={`w-4 h-4 absolute right-1 top-0.5 ${(this.state.collapsed[2]) ? '-rotate-90': 'rotate-90'}`}>
                                                    <path fillRule="evenodd" d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z" clipRule="evenodd" />
                                                  </svg>
                                                </div>
                                                {(!this.state.collapsed[2]) ? 
                                                    <div className={`flex w-full max-h-72`}></div> 
                                                  :null}
                                          </div>
                                          
                                      </div>
                                    </div>
                                    <div className={`flex w-full`}>
                                      <div className={`flex flex-col w-full border border-black overflow-hidden rounded-lg`}>
                                      <div className={`flex flex-col w-full px-3 pb-2`}>
                                      <div className={`flex w-full h-4 bg-primaryColor rounded-b-md justify-center relative items-center text-center uppercase text-white px-2 py-3 text-sm font-sfuidisplayBlack`} onClick={()=>this.handleToggle(3)}>
                                                  <span>Orders</span>
                                                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className={`w-4 h-4 absolute right-1 top-0.5 ${(this.state.collapsed[3]) ? '-rotate-90': 'rotate-90'}`}>
                                                    <path fillRule="evenodd" d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z" clipRule="evenodd" />
                                                  </svg>
                                                </div>
                                                {(!this.state.collapsed[3]) ? 
                                                    <div className={`flex flex-col max-h-72 overflow-auto w-full mb-10`}>
                                                      <div className={`h-full w-full`}></div>
                                                    </div> 
                                                  :null}
                                          </div>
                                      </div>
                                    </div>
                                </div>
      </>
    );
  }
}

export default EncounterCard;
