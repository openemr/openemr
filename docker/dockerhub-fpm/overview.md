# openemr/dev-php-fpm

for development only: php-fpm dockers that are used with nginx in the
docker development environment.

Rebuilt weekly from [.github/workflows/weekly-build-php-fpm-dockers.yml](https://github.com/openemr/openemr/blob/master/.github/workflows/weekly-build-php-fpm-dockers.yml).
Consumed by [docker/development-insane](https://github.com/openemr/openemr/blob/master/docker/development-insane/README.md)
and the `ci/nginx_*` compose stacks. See the insane README for endpoint
mappings and usage.

## Supported tags

__SUPPORTED_TAGS__

Each `X.Y` tag ships with the `phpredis` extension baked in. To use
redis-backed sessions on top of a given tag, bind-mount a `php.ini` that
sets `session.save_handler = redis` and
`session.save_path = "tcp://redis:6379"`. See the
[insane compose file](https://github.com/openemr/openemr/blob/master/docker/development-insane/docker-compose.yml)
for a working example.
