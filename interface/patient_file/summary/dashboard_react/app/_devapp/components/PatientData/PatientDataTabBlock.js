import React from 'react';
import agent from "../../utils/agent";
import ReactPlaceholder from 'react-placeholder';
import "react-placeholder/lib/reactPlaceholder.css";

// https://github.com/buildo/react-placeholder - documentation for react placeholder

class PatientDataTabBlock extends React.Component {
    constructor(props) {
        super(props);
        const groupId = props.groupId;
        this.state = {
            patientData: [],
            isLoaded: false
        }
    }

    render() {
        {
            var isSkipped = -1;
            var lastTitle = '';
            var data = {};
            {
                this.props.groupFields.map((pdata, j) => {
                    let title = pdata.title;
                    if (title.length > 0 ){
                        if( isSkipped > 0 || isSkipped == 0) {
                            isSkipped = 0;
                            lastTitle = '';
                        }
                        data[title] = [];
                        data[title].push(this.props.data[0][pdata.field_id]);
                        lastTitle = title;
                    }else {
                        isSkipped++;
                        data[lastTitle].push(this.props.data[0][pdata.field_id]);
                    }
                })
            }
        }

        return (

                <ReactPlaceholder type='text' showLoadingAnimation={true} rows={7}
                                  ready={this.props.groupFields.length > 0}>
                    <div className="table">
                    {Object.keys(data).map((key, j) => {
                        return (
                            <div className={j % 2 === 0 ? 'cell-row evenClass' : 'cell-row'} key={j}>
                                <div className="cell-1">
                                    <span>{key}</span>:
                                </div>
                                <div  className="cell-2">
                                    { Array.isArray( data[key] ) ? data[key].join(' ') : data[key] }
                                </div>
                            </div>
                        )
                    })}
                    </div>
                </ReactPlaceholder>

        )
    }
}

export default PatientDataTabBlock;