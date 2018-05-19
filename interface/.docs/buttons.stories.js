import React from 'react';
import { storiesOf } from '@storybook/react';
import { action } from '@storybook/addon-actions';

const stories = storiesOf('Buttons', module);

stories.add('with text', () => (
    <button className={"btn btn-save"} onClick={action('clicked')}>Hello there</button>
));

stories.add('with some emoji', () => (
    <button onClick={action('clicked')}><span role="img" aria-label="so cool">😀 😎 👍 💯</span></button>
));
