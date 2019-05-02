import React, {Component} from 'react';
import PropTypes from 'prop-types';
import {Button} from "react-bootstrap";
import {library} from '@fortawesome/fontawesome-svg-core'
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome'
import {faPlus, faMinus} from '@fortawesome/free-solid-svg-icons'

library.add(faPlus, faMinus);

class ToggleButton extends React.Component {
    render() {
        const fontAwesome = this.props.isOpen ? <FontAwesomeIcon icon='minus'/> : <FontAwesomeIcon icon='plus'/>
        return (
            <div>
                <Button
                    onClick={() => this.props.onClick()}
                    aria-controls="example-collapse-text"
                    aria-expanded={this.props.isOpen}
                > {fontAwesome}
                </Button> <label onClick={() => this.props.onClick()}>{this.props.rightText}</label>
            </div>
        )
    }
}

export default ToggleButton;
