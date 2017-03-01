Copyright 2017 sjpadgett@gmail.com

# ccdaservice
// root user ubuntu install stuff

msiexec.exe /i node-v6.10.00-x64.msi INSTALLDIR="C:\Tools\NodeJS" /quiet
apt-get remove --purge nodejs npm

curl -sL https://deb.nodesource.com/setup_6.x | sudo -E bash -

sudo apt-get install -y nodejs

//non root install 

sudo apt-key adv --keyserver keyserver.ubuntu.com --recv 68576280 

sudo apt-add-repository "deb https://deb.nodesource.com/node_7.x $(lsb_release -sc) main"

sudo apt-get update sudo apt-get install nodejs

navigate to: openemr/services/ccdaservice
run: npm install

And for windows create and install service by running 'node winservice' if you want
 Or
Whenever you log in to OpenEmr, background service auto starts service in ubuntu and will install service in windows case.

## Usage



## Developing



### Tools
