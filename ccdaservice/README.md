# Prepare

* Import to database, table background_services: ccdaservice/support/background_services.sql
* This allows for montoring the ccda service availability and if not installed and the ccda service is started by command line, module should still work. Still, install it...
* If not already completed, you must install the CCM features using the Modules=>Manage Modules Unregistered tab.
* The Carecoordination module setting for Mirth IP must be set to localhost. Menu item: Modules=>Manage Modules click in Carecoordination Config the settings tab.
* Ignore any errors that do not throw you back to command prompt when using npm install as some libs need to be compiled and is verbose about it.

## Ubuntu

- apt-get remove --purge nodejs npm // Ensures clean install and will allow chance to cleanup.

- curl -sL https://deb.nodesource.com/setup_6.x | sudo -E bash -

- sudo apt-get install -y nodejs

Navigate to: openemr/ccdaservice and run the following to install requires dependencies.

- sudo npm install

- sudo npm i node-linux

-- sudo node winservice // For now, you may skip this step because ssmanager will run service as a process on Ubuntu. If started though, it is used.

* If the service is not installed in last step the ssmanager, started by backgroud services, will still start and maintain ccda service.
* For portal this is not necessarily true.

## Windows
* Download and install node v6.9.5 or 6.1 for your windows version.

Navigate to: openemr/ccdaservice and run the following

- npm install

- npm i node-windows

* The service will automatically be installed when OpenEMR is log into the first time and with both installs, ccda service is monitored by ssmanager started by background services.

## Developing
* Note that these scripts run in strict mode so javascript will hold you very much accountable with how objects and variables are handled.
* Important to note that during developement error checking is relaxed, so after CCM sends xml, it waits to receive back document and if document generation errors for some reason either CCM waits till php timeout or you need to restart apache to clear that socket. 99% of the time this is not an issue however, currently CCM doesn't do any supervision on socket communication thus sends and waits. I hate timers but may be solution here.
* For now node modules are run local to service directory so all support dependecies are installed there. I still have not decided best way to implement node into project.

## Tools
* node winservice is service install/start both Windows and Ubuntu
* node unservice is service uninstall

## License
Copyright 2017 sjpadgett@gmail.com
GNU GPL