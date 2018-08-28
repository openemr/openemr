import React from 'react';
import { storiesOf } from '@storybook/react';
import { doc } from 'storybook-readme';

import README from '../../interface/README.md';

storiesOf('# Introduction', module).add('README', doc(README));
