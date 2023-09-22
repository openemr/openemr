import React, { Component } from "react";
import { LazyLoadImage } from "react-lazy-load-image-component";
import { rxService } from "../services/Services";
import { UtilService } from "../services/UtilService";
class SplashScreen extends Component {
  constructor(props) {
    super(props);

    this.state = {
      progressLoaded: 0,
      isLoading: true,
      hasError: false,
    };
  }
  componentDidMount() {
    this.setProgressing(this.props.connected);
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
  setProgressing(connected) {
    setInterval(() => this.setProgressLoaded(connected), 1000);
  }
  setProgressLoaded = (connected) => {
    var progress =
      this.state.progressLoaded < 100 ? this.state.progressLoaded + 10 : 100;
    this.setState({
      progressLoaded: !this.props.hasError
        ? progress
        : this.state.progressLoaded,
    });
  };
  render() {
    return (
      <>
        {this.state.isLoading ? (
          <div className="fixed z-50 flex flex-col w-full h-full right-0 left-0 top-0 bottom-0 bg-white">
            <div className="grid h-screen place-items-center">
              <div className="flex flex-col">
                <div className="w-80 h-24 mx-auto items-center justify-center">
                  <LazyLoadImage src={`/images/Medsov-logo.png`} />
                </div>
                <div
                  className={`h-1 w-full transition-width transition-slowest ease ${
                    this.state.hasError ? "bg-red-500" : " bg-gray-500"
                  }`}
                  style={{ width: `${this.props.progressLoaded}%` }}
                ></div>
                {this.state.hasError ? (
                  <div className="text-red-600 my-2">
                    Connection lost ... Check your internet connectivity
                  </div>
                ) : (
                  <div className="font-sfuidisplayBlack text-4xl my-4 mx-auto text-primaryColor">
                    Visual EHR
                  </div>
                )}
                {!UtilService.getPid() && (
                  <p className="text-lg text-white mx-auto bg-primaryColor/80 px-3 rounded-lg">
                    No Patient has been selected
                  </p>
                )}
              </div>
            </div>
          </div>
        ) : null}
      </>
    );
  }
}

export default SplashScreen;
