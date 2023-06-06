import React, { Component } from 'react';
import { HexColorPicker } from "react-colorful";
import ClickAwayListener from 'react-click-away-listener';

class ColorPicker extends Component{

    constructor(props) {
        super(props);
        this.state={
            selectColor:""
        }
    }
    handleColorPicker=(e)=>{
        this.setState({selectColor:e})
    }
    handleClickAway=()=>{
        this.props.closeColorPicker()
    }
    handleDone=()=>{
        this.props.selectedColor(this.state.selectColor)
    }
    
    render(){
        return (
            <div className="fixed bg-black z-50 bg-opacity-25 h-full w-full top-0 left-0 z-10 overflow-y-auto font-sfuidisplay">
                <div className="grid h-screen place-items-center px-1">
                    <div className="w-full m-auto bg-white overflow-hidden max-w-md ">
                            <ClickAwayListener onClickAway={this.handleClickAway}>
                                <div className="flex flex-col w-full justify-center items-center">
                                    <HexColorPicker style={{width:`500px`}} color={this.state.selectColor} onChange={this.handleColorPicker} />
                                    <div className="uppercase py-1 cursor-pointer w-full text-center font-sfuidisplayBlack" onClick={this.handleDone}>
                                        done
                                    </div>
                                </div>
                            
                            </ClickAwayListener>
                            
                    </div>
                </div>
            </div>
        
        )}
}
export default ColorPicker