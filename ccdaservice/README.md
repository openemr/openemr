Copyright 2017 sjpadgett@gmail.com
# Pre

* Import to database table background_services: ccdaservice/support/background_services.sql
* This allows for montoring the ccda service availability and if not installed and the ccda service is started by command line, module should still work. Still, install it...
* The Carecoordination module setting for Mirth IP must be set to localhost. Menu item: Modules=>Manage Modules click Carecoordination Config the settings tab.
* First if not already done you must install the features using the Manage Modules Unregistered tab.
* Ignore any errors that do not throw you back to command prompt when using npm as some libs need to be compiled and is verbose about it.

# Ubuntu

apt-get remove --purge nodejs npm // Ensures clean install and will allow chance to cleanup.

curl -sL https://deb.nodesource.com/setup_6.x | sudo -E bash -

sudo apt-get install -y nodejs

* navigate to: openemr/ccdaservice and run the following to install requires dependencies.

sudo npm install

sudo npm i node-linux

sudo node winservice // don't be alarmed by filename as it is written for both os's.

* If the service is not installed in last step the ssmanager started by backgroud services will still start and maintain ccda service.

## Windows

* Download and install node v6.9.5 or 6.1 for your windows version.

* navigate to: openemr/ccdaservice and run the following

npm install

npm i node-window

* The service will automatically be installed when OpenEMR is log into the first time and with both installs, ccda service is monitored by ssmanager started by background services.

## Developing

* Important to note that during developement strict error checking is not in place so after CCM sends xml, it waits to receive back document and if document generation errors for some reason either CCM waits till php timeout or you need to restart apache to clear that socket.

### Tools
