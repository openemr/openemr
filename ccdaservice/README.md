# About
This module/service will provide the necessary template engine to create a Patient Summary CCD.
CCD's can be generated from the Onsite Patient Portal or the Carecoordination Module.

Beside installing the C-CDA service, also included in this installation are two new services.
- oe-schematron-service for validating QRDA and CDA type documents. Runs on port 6662.
- oe-cqm-service is our CQM calculator service. Runs on port 6660
## Prepare
* If not already completed, you must install/initialize the CCM features by going to the `Modules->Manage Modules` top menu then click Unregistered tab and install the necessary components.
* Add any appropriate settings like granting Access Control for the appropriate users. Menu item: `Modules->Manage Modules->Access Control->General->Care Coordination`.
* Ensure all appropriate fields are populated in `Modules->Manage Modules Settings` tab such as Author, Primary Care Provider and so forth.
## Updating
#### It's important to note when updating or re-installing to stop any existing node processes before implementing any changes to the service.
Whenever there are new versions or updates, be sure to navigate into the ccdaservice directory and run:
- `npm i --omit=dev`
- `npm ci --omit=dev`

To ensure the lastest libraries are installed, node version changes or the package lock file is for a different build version then it is necessary to run `node install` to update dependencies to locked versions. Next, ensure the installation is renewed by running `node ci` (clean install) will ensure package dependencies are in sync and the node_modules directory is deleted and rebuilt.


## Ubuntu Setup
* Latest version tested is node v20.10.0

If node is not already installed then do the following(Research the required installation for your environment):
- `cd ~`
- `apt-get remove --purge nodejs npm` // Ensures clean install and will allow chance to clean up.
- `curl -sL https://deb.nodesource.com/setup_20.x | sudo -E bash -`
- `sudo apt-get install -y nodejs`

Navigate to: openemr/ccdaservice and run the following to install requires dependencies.
- `sudo npm install --omit=dev`
## Windows Setup
* Download and install nodejs for your Windows version.
  - Latest version tested is node v20.10.0
* Ensure system variable NODE_PATH is set i.e. `%USERPROFILE%\AppData\Roaming\npm\node_modules`.

Navigate to: openemr/ccdaservice and run the following from an elevated PowerShell or CMD.exe (run as Administrator):
- `npm install --global --omit=dev windows-build-tools` (Recommended) Though compiling libxmljs is no longer required, if for some reason your environment doesn't have download  compiled libxmljs2 binary available then npm will try to compile.
- `npm install --omit=dev`
### Use
* CCDA service must be enabled in OpenEMR's menu Admin->Config->Connectors->Enable C-CDA Service.
* This service will automatically start on demand when required by OpenEMR.
### Developing
* Note that these scripts run in strict mode so javascript will hold you very much accountable with how objects and variables are handled.
* For now, node modules are run local to service directory so all support dependecies are installed there.
### Tools
* The nodejs ccda service now starts on demand.
#### License
   	    Copyright 2018-2023 sjpadgett@gmail.com
        https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
