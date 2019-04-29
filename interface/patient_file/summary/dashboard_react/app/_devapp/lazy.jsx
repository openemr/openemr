import * as React from "react";
import PropTypes from "prop-types";

export class LazyLoadModule extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            module: null,
            prop:props
        };
    }

    componentDidCatch(error) {
        this.setState({ hasError: error });
    }

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
        const { module, hasError } = this.state;

        if (hasError) return <div>{hasError.message}</div>;
        if (!module) return <div>Loading module...</div>;

        if (module.view) return React.createElement(module.view,this.props);

        return <div>Module loaded</div>;
    }
}

LazyLoadModule.propTypes = {
    resolve: PropTypes.func
};
