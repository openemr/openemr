import React from 'react';
import { storiesOf } from '@storybook/react';
import { doc } from 'storybook-readme';

import README from '../../README.md';

storiesOf('# Introduction', module).add('README', doc(README));
