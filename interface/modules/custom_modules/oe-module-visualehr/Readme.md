## Visual EHR
This is a brief readme of how to install the visual dashboard.

This is a reactjs application embeded in a php module, hence this installation is in two steps

1. ReactJs Configuration
STEP 1: 
In the react directory (oe-module-visualehr/react), headover to the package.json file and change the homepage and proxy values to the settings that applies to you. for example
`"homepage": "http://localhost/openemr-7/interface/modules/custom_modules/oe-module-visualehr/public/"`,
`"proxy": "http://localhost/"`,

STEP 2: run `npm i` to install node modules

STEP 3: Save and build the project using `npm run build`

STEP 4: Copy the content of the build folder and paste it into the public directory of the module like so
`oe-module-visualehr/public/`

2. OpenEMR Configuration
STEP 1: Extract the zipped file in /interface/modules/custom_modules/

STEP 2: Head over to OpenEMR, register and install the module.

STEP 3: Log out and login again to see the changes

STEP 4: Access the Visual Dashboard from Modules -> Visual Dashboard