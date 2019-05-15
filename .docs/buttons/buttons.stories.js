import React from 'react';
import { storiesOf, addDecorator } from '@storybook/react';
import { withReadme } from 'storybook-readme';
import { action } from '@storybook/addon-actions';
import README from './README.md';

const stories = storiesOf('Buttons', module);
stories.addDecorator(withReadme(README));

stories.add('with text', () => (
    <button className={"btn btn-save"} onClick={action('clicked')}>Hello there</button>
));

stories.add('with some emoji', () => (
    <button onClick={action('clicked')}><span role="img" aria-label="so cool">😀 😎 👍 💯</span></button>
));
