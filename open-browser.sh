#!/bin/bash
set -e

cd "$(dirname "$0")/docker/frontcontroller"

port=$(docker compose port http 80 | cut -d: -f2)
if [ -z "$port" ]; then
    echo "Could not determine port. Is the http service running?" >&2
    exit 1
fi

url="http://localhost:$port"
echo "Opening $url"
open "$url"
