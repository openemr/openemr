import * as React from "react";
import PropTypes from "prop-types";

export class LazyLoadModule extends React.Component {
    constructor(props) {
        //console.log(props)
        super(props);
        this.state = {
            module: null,
            props:props,
        };
    }

    componentDidCatch(error) {
        this.setState({ hasError: error });
    }

    async componentDidMount() {
        try {
            const { resolve } = this.props;
            let x= await resolve();
            console.log(x);
            const { default: module } = x;

            console.log(module)
           // debugger;

            this.setState({ module });
        } catch (error) {
            this.setState({ hasError: error });
        }
    }

    render() {
       // console.log(this.state);
        const { module, hasError } = this.state;

        if (hasError) return <div>{hasError.message}</div>;
        if (!module) return <div>Loading module...</div>;
        console.log(module.view);
        if (module.view) return React.createElement(module.view,this.props);

        return <div>Module loaded</div>;
    }
}

LazyLoadModule.propTypes = {
    resolve: PropTypes.func
};
