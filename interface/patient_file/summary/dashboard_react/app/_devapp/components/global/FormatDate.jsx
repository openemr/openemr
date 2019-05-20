import React from 'react';
import Moment from 'react-moment';

class FormatDate extends React.Component {

    constructor(props) {
        super(props);
        this.state = {};
    }


    render() {

        let format = dateFormat;
        if (typeof this.props.showTime !== 'undefined' && this.props.showTime) {
            format += ' HH:mm';
            if (typeof this.props.showSeconds !== 'undefined' && this.props.showSeconds) {
                format += ':ss';
            }
        }

        return <Moment format={format}>
            {this.props.children}
        </Moment>
    }
}


export default FormatDate;