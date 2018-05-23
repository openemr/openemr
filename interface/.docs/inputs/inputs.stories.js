import React from 'react';
import { storiesOf } from '@storybook/react';

const stories = storiesOf('Inputs', module);

stories.add('text', () => (
    <input class="form-control" type="text" />
));

stories.add('entry', () => (
    <input class="form-control" type="entry" />
));

stories.add('password', () => (
    <input class="form-control" type="password" />
));

stories.add('search', () => (
    <input class="form-control" type="search" />
));
