import React from 'react';
import themeOptions from './themeOptions';

export let options = {};
for (let i = 0; i < themeOptions.length; i++) {
    options[themeOptions[i]] = themeOptions[i];
}

export const label = 'Theme';
export const defaultValue = 'style_light';

export class ThemeProvider extends React.Component {
    render() {
        return(
            <div>
                <link rel="stylesheet" type="text/css" href={"./public/themes/" + this.props.value + ".css"} />
                {this.props.children}
            </div>
        )
    }
};
