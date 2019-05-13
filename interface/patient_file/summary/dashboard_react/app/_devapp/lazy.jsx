import * as React from "react";
import PropTypes from "prop-types";

/**
 * Load dynamic components
 * The name and import function of component come with the props variable
 */
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

    /**
     * Import the component using prop - resolve()
     * Set the current component in the state
     * @returns {Promise.<void>}
     */
    async componentDidMount() {
        try {
            const { resolve } = this.props;

            const { default: module } = await resolve();

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
        // create the new component
        if (module.view) return React.createElement(module.view, this.props);

        return <div>Module loaded</div>;
    }
}

LazyLoadModule.propTypes = {
    resolve: PropTypes.func
};
