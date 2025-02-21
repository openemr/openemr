import React from "react";
import {useLocation,useNavigate,useParams,useHistory,useOutletContext} from "react-router-dom";

  
  function withRouter(Component) {
    function ComponentWithRouterProp(props) {

      let location = useLocation();
      let navigate = useNavigate();
      let params = useParams();
      let context = useOutletContext();
      return (
        <Component
          {...props}
          navigate={navigate}
          location={location}
          params={ params }
          context={context}
        />
      );
    }
  
    return ComponentWithRouterProp;
  }
  export default withRouter