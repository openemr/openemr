import React, { Component } from "react";
import { rxService } from '../services/Services';
import {UtilService} from '../services/UtilService'
import { UPDATE } from '../utilities/constants';
import { format } from "date-fns";
class MiniInformation extends Component {
  constructor(props) {
    super(props);

    this.state = {
      data: {},
      showDialog:false
    };
  }
  handleToggle = () => {
    this.setState({showDialog:false})
  };
  handleOnClick = () => {
     this.handleToggle()
  
  };
  
  render() {
    return (
      <>
       <div className="w-full m-auto bg-primaryRedBgColor relative border-0 rounded-xl border-black overflow-hidden md:max-w-md ">
                <div className="md:flex">
                  <div className="flex flex-col w-full space-y-4 mx-2 my-2">
                    <div className="flex w-full text-sm">
                      <div className="mr-2">Problem:</div>
                      <div className=" font-sfuidisplayBlack flex-wrap mr-2">
                        {(this.props.data !==undefined) ? this.props.data.diagnosis_show_as : null}
                      </div>
                    </div>
                    <div className="flex w-full text-sm">
                      <div className="mr-2">ICD-10 Coded as :</div>
                      <div className=" font-sfuidisplayBlack flex-wrap mr-2">
                        {(this.props.data !==undefined) ? this.props.data.diagnosis : null}
                      </div>
                    </div>
                    <div className="flex w-full text-sm">
                      <div className="mr-2">Display As:</div>
                      <div className=" font-sfuidisplayBlack mr-2">
                        {(this.props.data) ? this.props.data.name : null}
                      </div>
                    </div>
                    <div className="flex w-full text-sm">
                      <div className="mr-2">Date of onset diagnosis:</div>
                      <div className=" font-sfuidisplayBlack mr-2">
                      {(this.props.data !=undefined) ? format(
                          new Date(UtilService.serializeDate(this.props.data.startDate)),
                          "dd/MM/yyyy"
                        ) : "-"}
                      </div>
                    </div>
                    <div className="flex w-full text-sm">
                      <div className="mr-2">End date:</div>
                      <div className=" font-sfuidisplayBlack mr-2">
                        {(this.props.data !=undefined) ? format(new Date(UtilService.serializeDate(this.props.data.date)), "dd/MM/yyyy") : "-"}
                      </div>
                    </div>
                    <div className="flex w-full text-sm">
                      <div className="mr-2">Last Addressed:</div>
                      <div className=" font-sfuidisplayBlack mr-2">
                        {(this.props.data !=undefined) ? format(
                          new Date(UtilService.serializeDate(this.props.data.modifydate)),
                          "dd/MM/yyyy"
                        ) : null}
                      </div>
                    </div>
                    {/* <div className="flex w-full text-sm">
                      <div className="mr-2">Date of Resolution:</div>
                      <div className=" font-sfuidisplayBlack mr-2">-</div>
                    </div>
                    <div className="flex w-full text-sm">
                      <div className="mr-2">Linked Date:</div>
                      <div className=" font-sfuidisplayBlack text-blue-500 underline">
                        -
                      </div>
                    </div> */}
                    <div className="flex w-full text-sm">
                      <div className="mr-2">Comments:</div>
                      <div className=" font-sfuidisplayBlack">
                        {(this.props.data !==undefined) ? this.props.data.comments != ""
                          ? this.props.data.comments
                          : "-" : null}
                      </div>
                    </div>
                    {/* <div className="flex w-full text-sm">
                      <div className="mr-2">Follow-up Needs:</div>
                      <div className=" font-sfuidisplayBlack">-</div>
                    </div> */}
                    <div className="flex w-full text-sm">
                      <div className="mr-2">Linked Medications:</div>
                      <div className=" font-sfuidisplayBlack">-</div>
                    </div>
                    <div className="flex w-full text-sm">
                      <div className="mr-2">Last Editor:</div>
                      <div className=" font-sfuidisplayBlack">
                        {(this.props.data !==undefined) ? format(
                          new Date(UtilService.serializeDate(this.props.data.modifydate)),
                          "dd/MM/yyyy"
                        ) : "-"}
                      </div>
                    </div>
                    {/* <div className="flex w-full text-sm">
                      <div className="mr-2">Updates to Problem:</div>
                      <div className=" font-sfuidisplayBlack">
                        No Updates to Show
                      </div>
                    </div> */}
                  </div>
                </div>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="h-6 hidden w-6 absolute bottom-2 right-5 cursor-pointer"
                  onClick={this.handleOnClick}
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  strokeWidth={2}
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                  />
                </svg>
              </div>
            
      </>
    );
  }
}

export default MiniInformation;
