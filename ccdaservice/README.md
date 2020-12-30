# About
This module/service will provide the necessary template engine to create a Patient Summary CCD.
CCD's can be generated from the Onsite Patient Portal or the Carecoordination Module.
## Prepare
* If not already completed, you must install/initialize the CCM features by going to the Modules->Manage Modules top menu then click Unregistered tab and install the necessary components.
* Add any appropriate settings like granting Access Control for the appropriate users. Menu item: Modules->Manage Modules->Access Control->General->Care Coordination.
* Ignore any errors that do not throw you back to command prompt when using npm install as some libs need to be compiled and is verbose about it.
## Updating
Whenever there are new versions or updates, be sure to navigate into the ccdaservice directory and run:
- npm i --production

This will ensure the lastest libraries are installed.
## Ubuntu Setup
* Latest version tested is node v14.0

If node is not already installed then do the following:
- cd ~
- apt-get remove --purge nodejs npm // Ensures clean install and will allow chance to cleanup.
- curl -sL https://deb.nodesource.com/setup_14.x | sudo -E bash -
- sudo apt-get install -y nodejs

Navigate to: openemr/ccdaservice and run the following to install requires dependencies.
- sudo npm install --production
## Windows Setup
* Download and install nodejs for your windows version.
  - Latest version tested is node v12.18.2
* Ensure system variable NODE_PATH is set i.e %USERPROFILE%\AppData\Roaming\npm\node_modules.

Navigate to: openemr/ccdaservice and run the following from an elevated PowerShell or CMD.exe (run as Administrator):
- npm install --global --production windows-build-tools
- npm install --production
### Use
* CCDA service must be enabled in OpenEMR's menu Globals->Connectors.
* This service will automatically start on demand when required by OpenEMR.
### Developing
* Note that these scripts run in strict mode so javascript will hold you very much accountable with how objects and variables are handled.
* For now, node modules are run local to service directory so all support dependecies are installed there.
### Tools
* The nodejs ccda service now starts on demand.
#### License
   	    Copyright 2018-2019 sjpadgett@gmail.com
        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
