#!/bin/bash
set -e

cd "$(dirname "$0")/docker/frontcontroller"

service="${1:-nginx}"

case "$service" in
    nginx|apache-fpm|apache-modphp|php-builtin)
        ;;
    apache)
        # Shorthand for apache-fpm
        service="apache-fpm"
        ;;
    php)
        # Shorthand for php-builtin
        service="php-builtin"
        ;;
    *)
        echo "Usage: $0 [nginx|apache-fpm|apache-modphp|php-builtin]" >&2
        echo "  nginx         - nginx + php-fpm (default)" >&2
        echo "  apache-fpm    - Apache + php-fpm" >&2
        echo "  apache-modphp - Apache + mod_php" >&2
        echo "  php-builtin   - PHP built-in server" >&2
        exit 1
        ;;
esac

port=$(docker compose port "$service" 80 2>/dev/null | cut -d: -f2)
if [ -z "$port" ]; then
    echo "Could not determine port. Is the $service service running?" >&2
    echo "Try: docker compose up -d $service" >&2
    exit 1
fi

url="http://localhost:$port"
echo "Opening $url"
open "$url"
