import React, { Component } from 'react';
import isWeekend from 'date-fns/isWeekend';
import TextField from '@mui/material/TextField';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFns';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { StaticDatePicker } from '@mui/x-date-pickers/StaticDatePicker';
import moment from 'moment';

class DatePickerLandscape extends Component {
    constructor(props) {
        super(props);

        this.state={
            value :new Date()
        }
    }
    
    handleChange=(newValue)=>{
        let start = moment(newValue).format('YYYY-MM-DD');
        this.setState({value:start})
    }
    handelAccept=()=>{
        this.props.selectedDate(this.state.value);
    }
    handleClose=()=>{
        this.props.selectedDate("");
    }
    render(){
        return (
        <div className="fixed bg-black bg-opacity-25 h-full w-full top-0 left-0 z-10 overflow-y-auto font-sfuidisplay">
            <div className="grid h-screen place-items-center px-1">
                <div className="w-full m-auto bg-white overflow-hidden md:max-w-3xl ">
                <LocalizationProvider dateAdapter={AdapterDateFns}>
                    <StaticDatePicker
                        orientation="portrait"
                        openTo="day"
                        format="DD-MM-YYYY"
                        disableFuture={true}
                        onAccept={this.handelAccept}
                        onClose={this.handleClose}
                        value={this.state.value}
                        shouldDisableDate={false}
                        onChange={this.handleChange}
                        renderInput={(params) => <TextField {...params} />}
                    />
                    </LocalizationProvider>  
                </div>
            </div>
        </div>
    
    );
    }
  
}
export default DatePickerLandscape;
