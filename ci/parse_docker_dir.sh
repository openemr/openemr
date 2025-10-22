#!/usr/bin/env bash

set -xeuo pipefail

readonly -A WEBSERVER_OPENEMR_DIRS=(
  ['apache']=/var/www/localhost/htdocs/openemr
  ['nginx']=/usr/share/nginx/html/openemr
)

##
# Parse the docker_dir and other inputs
# to build configurations for tests in GitHub Actions
parse() {
  local docker_dir="${1}"
  local node_version=22
  local database
  local db
  local webserver
  local php
  local selenium_template
  local webserver_template
  local database_template
  local mailpit_template

  # Parse docker directory name
  mysql_image=$(yq '.services.mysql.image' "ci/${docker_dir}/docker-compose.yml")
  IFS=: read -r database db <<< "${mysql_image}"
  IFS=_ read -r webserver php _ <<< "${docker_dir}"

  # Format PHP version
  printf -v php '%d.%d' "${php::1}" "${php:1}"

  # Collect docker-compose.yml templates
  selenium_template=$(yq '.x-includes.selenium-template' "ci/${docker_dir}/docker-compose.yml")
  webserver_template=$(yq '.x-includes.webserver-template' "ci/${docker_dir}/docker-compose.yml")
  database_template=$(yq '.x-includes.database-template' "ci/${docker_dir}/docker-compose.yml")
  mailpit_template=$(yq '.x-includes.mailpit-template' "ci/${docker_dir}/docker-compose.yml")

  # Check if docker_dir ends with "_no-e2e"
  [[ ${docker_dir} = *_no-e2e ]] && e2e_enabled=false || e2e_enabled=true

  # Determine OpenEMR directory based on webserver
  openemr_dir="${WEBSERVER_OPENEMR_DIRS[${webserver}]}"
  if [[ -z ${openemr_dir} ]]; then
    echo "Unknown webserver: ${webserver}" >&2
    return 1
  fi

  # Save cache only on first occurrence of each version to avoid matrix collisions
  # Track seen versions in global arrays
  if [[ ! -v SEEN_PHP_VERSIONS[@] ]]; then
    declare -gA SEEN_PHP_VERSIONS
  fi
  if [[ ! -v SEEN_NODE_VERSIONS[@] ]]; then
    declare -gA SEEN_NODE_VERSIONS
  fi

  # Check if this is the first occurrence of this PHP version
  if [[ -z ${SEEN_PHP_VERSIONS[${php}]:-} ]]; then
    save_composer_cache=true
    SEEN_PHP_VERSIONS[${php}]=1
  else
    save_composer_cache=false
  fi

  # Check if this is the first occurrence of this Node version
  if [[ -z ${SEEN_NODE_VERSIONS[${node_version}]:-} ]]; then
    save_node_cache=true
    SEEN_NODE_VERSIONS[${node_version}]=1
  else
    save_node_cache=false
  fi

  # Compose file path (first entry needs to be in ci/ if it has a subdirectory then it breaks things)
  compose_file="ci/${webserver_template}:ci/${database_template}:ci/${selenium_template}:ci/${mailpit_template}:ci/${docker_dir}/docker-compose.yml"

  jq -cn \
    --arg compose_file "${compose_file}" \
    --arg docker_dir "${docker_dir}" \
    --arg openemr_dir "${openemr_dir}" \
    --arg database "${database}" \
    --arg database_template "${database_template}" \
    --arg db "${db}" \
    --arg e2e_enabled "${e2e_enabled}" \
    --arg mailpit_template "${mailpit_template}" \
    --arg node_version "${node_version}" \
    --arg php "${php}" \
    --arg save_composer_cache "${save_composer_cache}" \
    --arg save_node_cache "${save_node_cache}" \
    --arg selenium_template "${selenium_template}" \
    --arg webserver "${webserver}" \
    --arg webserver_template "${webserver_template}" \
    '{
      env: {
        COMPOSE_FILE: $compose_file,
        DOCKER_DIR: $docker_dir,
        OPENEMR_DIR: $openemr_dir
      },
      output: {
        database: $database,
        database_template: $database_template,
        db: $db,
        docker_dir: $docker_dir,
        e2e_enabled: $e2e_enabled,
        mailpit_template: $mailpit_template,
        node_version: $node_version,
        openemr_dir: $openemr_dir,
        php: $php,
        save_composer_cache: $save_composer_cache,
        save_node_cache: $save_node_cache,
        selenium_template: $selenium_template,
        webserver: $webserver,
        webserver_template: $webserver_template
      }
    }'
}

main() {
  local arg
  if (( $# == 0 )); then
    echo 'Need at least one docker dir to parse' >&2
    exit 1
  fi
  for arg; do
    parse "${arg}"
    shift
  done | jq -sc
}

main "$@"
