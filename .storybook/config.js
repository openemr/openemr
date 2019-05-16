import React from 'react';
import { configure, addDecorator } from '@storybook/react';
import { withKnobs, select } from '@storybook/addon-knobs';
import { ThemeProvider, defaultValue, label, options } from './ThemeProvider';

addDecorator(story => (
    <ThemeProvider value={select(label, options, defaultValue)}>
        {story()}
    </ThemeProvider>
));
addDecorator(withKnobs);

const req = require.context("../.docs", true, /\.stories\.js$/);

function loadStories() {
    req.keys().forEach(filename => req(filename));
}

configure(loadStories, module);
