import React from 'react';
import Moment from 'react-moment';

class FormatDate extends React.Component {

    constructor(props) {
        super(props);
        this.state = {};
    }


    render() {

        let format = dateFormat;
        return <Moment format={format}>
            {this.props.children}
        </Moment>
    }
}


export default FormatDate;