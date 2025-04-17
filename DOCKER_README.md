# OpenEMR Docker Documentation

## Overview
The OpenEMR community loves Docker. We eat and breathe Docker. The OpenEMR dockers and detailed documentation
can be found on [dockerhub](https://hub.docker.com/r/openemr/openemr/). There are two main categories of
dockers for OpenEMR, Production Dockers and Development Dockers. Production dockers are meant for production
use with tags such as `7.0.0` whereby the `latest` tag identifies the most recent version Production
docker. Development dockers are mainly meant for development and/or testing and include the `flex`
series which are highly flexible development dockers that are used to create the standard OpenEMR development
environments. There is also a development docker that is built nightly from the current development codebase
with tags `dev` and `next` that is less flexible than the `flex` series and is mainly used for testing.

## Production Dockers
Production dockers are meant for production use with tags such as `7.0.0` whereby the `latest` tag identifies
the most recent version Production docker and can be found on [dockerhub](https://hub.docker.com/r/openemr/openemr/).
Several example docker-compose.yml scripts are discussed below.

### Production example
An example docker-compose.yml script can be found at
[docker/production/docker-compose.yml](docker/production/docker-compose.yml). After modifying the
script for your purposes, it can then be started with `docker-compose up`, which will then take about 5-10
minutes to complete.

### Production example for Raspberry Pi
An example docker-compose.yml script for Raspberry Pi can be found at
[docker/production-arm/docker-compose.yml](docker/production-arm/docker-compose.yml). After modifying the
script for your purposes, it can then be started with `docker-compose up`, which will then take about 5-10
minutes to complete.

## Development Dockers
Development dockers are meant for development and testing and can be found on
[dockerhub](https://hub.docker.com/r/openemr/openemr/).

### `Flex` Series Development Dockers and Development Environments
The `flex` series development dockers are highly flexible development dockers that are used to create the
standard OpenEMR development environments, and can be found on
[dockerhub](https://hub.docker.com/r/openemr/openemr/). It is strongly recommended to not use these dockers
for production purposes unless you know what you are doing. There are 2 OpenEMR development environments,
which are based on these development dockers. The main development environment is the Easy Development Docker
environment, which is documented at [CONTRIBUTING.md](CONTRIBUTING.md#code-contributions-local-development);
note this environment can also be run on Raspberry Pi. The other development environment, which is much more
complex, is the Insane Development Docker environment, which is documented at
[docker/development-insane/README.md](docker/development-insane/README.md#insane-development-docker-environment).

### Nightly build Development Docker
There is also a development docker that is built nightly from the current development codebase with tags `dev`
and `next` that is less flexible than the `flex` series and is mainly used for testing, and can be found on
[dockerhub](https://hub.docker.com/r/openemr/openemr/).
