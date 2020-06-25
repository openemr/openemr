# OpenEMR Docker Documentation

## Overview
The OpenEMR community loves dockers. We eat and breath dockers. The OpenEMR dockers can be found on
[dockerhub](https://hub.docker.com/r/openemr/openemr/). There are two main categories of dockers for
OpenEMR, Production Dockers and Development Dockers. Production dockers are meant for production use
with tags such as `5.0.2` and `latest`. Development dockers are meant for development and include the
`flex` series.

## Production Dockers
Production dockers are meant for production use with tags such as `5.0.2` and `latest` and can be found
on [dockerhub](https://hub.docker.com/r/openemr/openemr/). An example docker-compose.yml script can be
found in the `docker` directory at [docker/docker-compose.yml](docker-compose.yml). After modifying the
script for your purposes, it can then be started with `docker-compose up`, which will then take about 5-10
minutes to complete.

## Development Dockers
Development dockers are meant for development and include the `flex` series and can be found on
[dockerhub](https://hub.docker.com/r/openemr/openemr/). It is strongly recommended to not use these dockers
for production purposes unless you know what you are doing. There are 2 OpenEMR development environments,
which are based on these development dockers. The main development environment is the Easy Development Docker
environment, which is documented at [CONTRIBUTING.md#code-contributions-local-development](CONTRIBUTING.md)
and [contrib/util/docker#easy-development-docker-environment](contrib/util/docker/README.md). The other
development environment, which is much more complex, is the Insane Development Docker environment, which is
documented at [contrib/util/docker#insane-development-docker-environment](contrib/util/docker/README.md).
