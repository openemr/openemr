# OpenEMR Themes Developer Guide

## Theme Files

Theme files can be found in the `oe-styles` folder and `colors` folder. The main ones are in `oe-styles`. These SASS files should only be edited if there is a theme-specific color change or theme-specific configuration setting. **DO NOT use these files for global theme changes as they are not meant for that!**

## Changing Default Bootstrap Values

Let's say you want to change a certain color in Bootstrap. Navigate to `default-variables.scss` and change the value there. If you are changing a certain setting, note that SASS reads from top to bottom and putting your code at the bottom of the file is always the safest bet.

## Editing Anything Regarding Site Navigation Buttons

Site navigation right now is split into two files, `tabs_style_compact.scss` and  `tabs_style_full.scss`. The difference is one has more padding than the other does. Use only these files to edit the navigation.