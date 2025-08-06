import React from "react";
import DropDown from "../../utilities/Dropdown";
import ICD10Card from "./ICD10Card";
import ClickAwayListener from "react-click-away-listener";
import { fetchService } from "../../services/RxServices";
import { UtilService } from '../../services/UtilService';
import { Resizable } from "react-resizable";

class ICD10Diagnoses extends React.Component {
  constructor(props) {
    super(props);
    this.textInput = React.createRef();
    this.state = {
      show_entries: [
        { id: 10, name: "10 Entries" },
        { id: 15, name: "15 Entries" },
        { id: 20, name: "20 Entries" },
        { id: 25, name: "25 Entries" },
        { id: 50, name: "50 Entries" },
        { id: 100, name: "100 Entries" },
        { id: 150, name: "150 Entries" },
      ],
      entriesPerPage: 10,
      icd_10_queries: [],
      icd_10_selected: [],
      isSearching: false,
      width: 200,
      height: 200,
      input: {
        search_icd_10: "",
      },
    };

    this.handleChange = this.handleChange.bind(this);
    this.handleToggle = this.handleToggle.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
    this.handleEntryChange = this.handleEntryChange.bind(this);
  }
  // On top layout
  onResize = (event, { element, size, handle }) => {
    this.setState({ width: size.width, height: size.height });
  };
  handleChange = (event) => {
    let input = this.state.input;
    input[event.target.name] = event.target.value;
    this.setState({ input });
    if (event.target.value !== "") {
      this.searchForICD(event.target.value);
    }
  };
  componentDidMount() {
    this.setState({ icd_10_selected: this.props.icd_10_selected_diagnoses });
  }
  handleToggle = () => {
    this.props.handleToggle();
  };
  handleICDSelected = (data) => {
    let { icd_10_selected } = this.state;
    var index = icd_10_selected.indexOf(data);
    
    if(this.props.timeline.id ===1){
      icd_10_selected.length=0
    }
    if (!icd_10_selected.includes(data)) {
      icd_10_selected.push(data);
    } else {
      icd_10_selected.splice(index, 1);
    }

    if(this.props.timeline.id !==1){
      this.setState({ icd_10_selected });
    }else{
      this.props.handleICD10(this.state.icd_10_selected);
    }
    
  };
  handleSubmit = () => {
    this.props.handleICD10(this.state.icd_10_selected);
  };
  handleEntryChange = (data) => {
    this.setState({ entriesPerPage: data.id });
  };
  searchForICD(search) {
      this.setState({ isSearching: true });
      fetchService.fetchICD10Lists(search).subscribe({
        next: result => {
          this.setState({
            icd_10_queries: result,
            isSearching: false,
          });
        },
        complete: () =>{}
    })
  }
  render() {
    return (
      <>
        <div className="fixed bg-black bg-opacity-25 h-full w-full top-0 left-0 z-10 overflow-y-auto font-sfuidisplay">
          <div className="grid h-screen place-items-center px-1">
            <Resizable
              height={this.state.height}
              width={this.state.width}
              onResize={this.onResize}
            >
              <div className="w-full md:max-w-3xl m-auto bg-primaryRedBgColor border-4 rounded-3xl border-black overflow-hidden ">
                <ClickAwayListener onClickAway={this.handleToggle}>
                  <div className="md:flex">
                    <div className="w-full">
                      <div className="px-4 flex items-center justify-between text-black">
                        <div className="flex w-full my-2 ">
                          <div className="flex w-full mx-10 items-center justify-center font-sfuidisplayBlack">
                            <div className="text-xl lg:text-lg flex w-full justify-center items-center">
                              <div className="bg-gray-200 px-10 py-1 border-4 rounded-xl border-primaryRedColor">
                                ICD-10 Diagnosis Section
                              </div>
                            </div>
                          </div>
                        </div>
                        <div className="items-center">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            onClick={this.handleSubmit}
                            className="h-10 w-10 cursor-pointer"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            strokeWidth={2}
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                          </svg>
                        </div>
                      </div>
                      <form method="post" className="flex flex-col w-full my-1">
                        <div className="flex flex-col w-full">
                          <div className="flex flex-col w-full">
                            <div className=" mx-10">
                              <div className="flex w-full bg-gray-200 grid grid-cols-5 cursor-pointer border border-black gap-4 rounded-xl items-center">
                                <div className="col-span-4 flex flex-row justify-between w-full text-center italic">
                                  <div className="flex w-full items-center justify-center">
                                    <div className="relative w-full group">
                                      <input
                                        type="text"
                                        name="search_icd_10"
                                        onChange={this.handleChange}
                                        value={this.state.input.name_display}
                                        className="block py-1 px-0 w-full text-sm text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-white focus:outline-none focus:ring-0 focus:border-blue-600 peer placeholder-black text-center italic"
                                        placeholder="ICD-10 Search - Type to Search, or Add from Dropdown"
                                        required
                                      />
                                    </div>
                                  </div>
                                </div>
                                <div className="flex w-full items-end justify-end">
                                  {this.state.isSearching ? (
                                    <svg
                                      role="status"
                                      className="inline h-5 w-5 mr-1 animate-spin"
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
                                  ) : (
                                    <svg
                                      xmlns="http://www.w3.org/2000/svg"
                                      className="h-5 w-5 mr-1"
                                      viewBox="0 0 20 20"
                                      fill="currentColor"
                                    >
                                      <path
                                        fillRule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clipRule="evenodd"
                                      />
                                    </svg>
                                  )}
                                </div>
                              </div>

                              <div className="flex w-full grid grid-cols-1 md:grid-cols-2 my-2">
                                <div className="flex items-center mb-4 lg:mb-0">
                                  <div className="font-black font-sfuidisplayBlack mr-5">
                                    Show
                                  </div>
                                  <div className="items-center">
                                    {this.state.show_entries.length > 0 ? (
                                      <DropDown
                                        data={this.state.show_entries}
                                        defaultValue={`${this.state.show_entries[0].name}`}
                                        handleChange={this.handleEntryChange}
                                        dropdownClass={`bg-white text-black`}
                                        placeholder={`Entries`}
                                        dropdownListClass={`flex w-full text-black hover:bg-gray-200`}
                                        className={`flex w-full uppercase text-black bg-primaryRedColor font-bold text-sm px-6 border border-black rounded-xl py-1 outline-none focus:outline-none`}
                                      />
                                    ) : null}
                                  </div>
                                </div>
                                <div className="flex w-full">
                                  {this.props.icd_10_diagnoses.length > 0 ? (
                                    <DropDown
                                      data={this.props.icd_10_diagnoses}
                                      dropdownClass={`bg-white text-black`}
                                      placeholder={`Current ICD-10 Diagnoses`}
                                      dropdownListClass={`flex w-full text-black hover:bg-gray-200`}
                                      className={`flex w-full text-black bg-primaryRedColor font-bold text-sm px-6 border border-black rounded-xl py-1 outline-none focus:outline-none`}
                                    />
                                  ) : null}
                                </div>
                              </div>

                              <div className="flex flex-row w-full flex-wrap">
                                {this.state.icd_10_selected.length > 0
                                  ? this.state.icd_10_selected.map(
                                      (data, i) => {
                                        return (
                                          <div className="flex flex-row pl-2 pr-1 mr-1 mb-2 text-sm items-center py-1 rounded-2xl bg-primaryRedColor text-white cursor-pointer" key={`${UtilService.makeKey(15)}`}>
                                            <div key={`${UtilService.makeKey(15)}`}>{data}</div>
                                            <div key={`${UtilService.makeKey(15)}`}
                                              className="p-2 rounded-full bg-red-500 ml-2"
                                              onClick={() =>
                                                this.handleICDSelected(data)
                                              }
                                            >
                                              <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-2 w-2"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                                key={`${UtilService.makeKey(15)}`}>
                                                <path
                                                  fillRule="evenodd"
                                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                  clipRule="evenodd"
                                                />
                                              </svg>
                                            </div>
                                          </div>
                                        );
                                      }
                                    )
                                  : null}
                              </div>
                              <div className="flex font-sfuidisplayBlack w-full py-1 bg-primaryRedColor grid grid-cols-2 mt-3 mb-2 rounded-lg">
                                <div>ICD-10 Code</div>
                                <div>Description</div>
                              </div>
                              <div className="flex flex-col max-h-72 overflow-auto w-full mb-10">
                                {this.state.icd_10_queries.length > 0 &&
                                this.state.input.search_icd_10 != "" ? (
                                  this.state.icd_10_queries
                                    .slice(0, this.state.entriesPerPage)
                                    .map((data, d) => {
                                      var icd = `ICD10:${data.formatted_dx_code}`;
                                      return (
                                        <ICD10Card
                                          isProblem={this.props.isProblem}
                                          disabled={
                                            this.state.icd_10_selected.length <=
                                            0
                                              ? true
                                              : false
                                          }
                                          data={data}
                                          key={`${UtilService.makeKey(15)}`}
                                          handleICDSelected={
                                            this.handleICDSelected
                                          }
                                          icd={icd}
                                          icd_10_selected={
                                            this.state.icd_10_selected
                                          }
                                        />
                                      );
                                    })
                                ) : (
                                  <div className="flex h-64 w-full text-center justify-center items-center">
                                    <div>No data to show</div>
                                    <div></div>
                                  </div>
                                )}
                              </div>
                            </div>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </ClickAwayListener>
              </div>
              </Resizable>
          </div>
        </div>
      </>
    );
  }
}
export default ICD10Diagnoses;
