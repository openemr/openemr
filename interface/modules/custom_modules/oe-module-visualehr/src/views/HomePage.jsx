import React from "react";
import { fetchService } from "../services/RxServices";
import { rxService } from "../services/Services";
import { CREATE } from "../utilities/constants";
import withRouter from "../utilities/WithRouter";
import Timeline from "./Timeline";
import { Fab, Action } from "react-tiny-fab";
import "react-tiny-fab/dist/styles.css";
import Create from "./create/Index";
import SplashScreen from "./SplashScreen";

class HomePage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      toogleEncounter: false,
      isLoading: true,
      createNewDate: true,
    };
  }
  componentDidMount() {
    fetchService.fetchSiberData();
    fetchService.getPatient(1);

    this.subscription = rxService.getSubscription().subscribe((data) => {
      let _self = this;
      if (data.data !== undefined) {
        if (!data.data.error) {
          if (data.data.issues !== undefined) {
            setTimeout(function () {
              _self.setState({ isLoading: false });
            }, 600);
          }
        } else {
        }
      }
    });
  }
  componentWillUnmount() {
    this.subscription.unsubscribe();
  }
  handleNewData = () => {
    rxService.send(CREATE);
  };
  handleEncounter = () => {
    this.setState({ toogleEncounter: !this.state.toogleEncounter });
  };
  callEncounter = () => {
    rxService.sendEncounter(this.state.toogleEncounter);
  };
  render() {
    return (
      <>
        {this.state.createNewDate ? <Create /> : null}
        <SplashScreen
          hasError={this.state.hasError}
          progressLoaded={this.state.progressLoaded}
        />
        <div className="overflow-hidden ">
          <section className=" dark:bg-gray-800 w-screen flex  flex-col-reverse sm:flex-row min-h-0 min-w-0  font-sfuidisplay">
            <div className="flex w-full">
              <div className=" w-full lg:flex-1 px-3 min-h-0 min-w-0 flex flex-col lg:flex-row">
                <div className="w-full  min-h-0 min-w-0 ">
                  <Timeline toogleEncounter={this.state.toogleEncounter} />
                </div>
              </div>
            </div>
          </section>
        </div>
        {!this.state.isLoading ? (
          <>
            <Fab
              mainButtonStyles={{ fill: `white`, backgroundColor: `#ce3d47` }}
              icon={`=`}
              alwaysShowTitle={true}
            >
              <Action
                style={{ fill: "white", backgroundColor: "#ce3d47" }}
                text={
                  this.state.toogleEncounter
                    ? "Hide Encounter"
                    : "Show Encounter"
                }
                onClick={this.handleEncounter}
              >
                {!this.state.toogleEncounter ? (
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="h-5 w-5"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path
                      fillRule="evenodd"
                      d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                      clipRule="evenodd"
                    />
                  </svg>
                ) : (
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="h-5 w-5"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path
                      fillRule="evenodd"
                      d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                      clipRule="evenodd"
                    />
                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                  </svg>
                )}
              </Action>
              <Action
                style={{ fill: "white", backgroundColor: "#ce3d47" }}
                text="Create New Data"
                onClick={this.handleNewData}
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="h-5 w-5"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fillRule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                    clipRule="evenodd"
                  />
                </svg>
              </Action>
            </Fab>
          </>
        ) : null}
      </>
    );
  }
}

export default withRouter(HomePage);
