import { BrowserRouter } from "react-router-dom";
import React, { Component, StrictMode } from "react";
import { Route, Routes } from "react-router-dom";
import ReactDOM from "react-dom/client";
import "./index.css";
import Home from "./views/HomePage";
import withRouter from "./utilities/WithRouter";

const root = ReactDOM.createRoot(document.getElementById("root"));
class HomePage extends Component {
  render() {
    return (
      <>
        <Routes>
          <Route path="*" exact={false} element={<Home />} />
        </Routes>
      </>
    );
  }
}
export default withRouter(HomePage);
root.render(
  <BrowserRouter>
    <StrictMode>
      <HomePage />
    </StrictMode>
  </BrowserRouter>
);
