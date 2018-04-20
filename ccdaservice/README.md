# Prepare

* If not already completed, you must install the CCM features using the Modules->Manage Modules click Unregistered tab.
* The Carecoordination module setting for Mirth IP must be set to localhost. Menu item: Modules->Manage Modules click in Carecoordination Config, the settings tab.
* Ignore any errors that do not throw you back to command prompt when using npm install as some libs need to be compiled and is verbose about it.

## Ubuntu Setup
- cd ~
- apt-get remove --purge nodejs npm // Ensures clean install and will allow chance to cleanup.
- curl -sL https://deb.nodesource.com/setup_9.x | sudo -E bash -
- sudo apt-get install -y nodejs

Navigate to: openemr/ccdaservice and run the following to install requires dependencies.

- sudo npm install --production


## Windows Setup

* Download and install nodejs v9.4 for your windows version.
* Ensure system variable NODE_PATH is set i.e %USERPROFILE%\AppData\Roaming\npm\node_modules

Navigate to: openemr/ccdaservice and run the following:
- npm install --global --production windows-build-tools
- npm install --production
## Use
* CCDA service must be turned on in OpenEMR menu Globals->Connectors.
* The service will automatically start on demand by application.

## Developing
* Note that these scripts run in strict mode so javascript will hold you very much accountable with how objects and variables are handled.

* For now node modules are run local to service directory so all support dependecies are installed there.

## Tools
* The nodejs ccda service now starts on demand.
## License
   		Copyright 2018 sjpadgett@gmail.com
		GNU GPL
