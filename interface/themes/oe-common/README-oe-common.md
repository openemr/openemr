### Purpose

Contains scss that is shared between all themes  
These rules generally apply to either individual pages or are oe- rules to be used anywhere in the application  
Here because they don't fit the category of **'core'** component  
Can be used flexibly by importing individual scss files or by importing a single scss file called ```all-common-import.scss``` that has all the individual scss files  
**REMEMBER!!** for ```all-common-import.scss``` to do what it says, each time a scss file is added to this directory an ```@ import <file>``` line has to be added to ```all-common-import.scss```  