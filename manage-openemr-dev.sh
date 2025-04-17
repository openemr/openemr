#!/bin/bash

# manage-openemr.sh - Script to start or stop OpenEMR Docker environment
# Usage: ./manage-openemr.sh [up|down|downv|restart]

# Set the OpenEMR development path
OPENEMR_DEV_PATH="docker/development-easy"

# Check if parameter is provided
if [ $# -eq 0 ]; then
    echo "Error: Missing parameter."
    echo "Usage: ./manage-openemr.sh [up|down|downv|restart]"
    exit 1
fi

# Process the command
case "$1" in
    up)       
        echo "Starting OpenEMR Docker environment..."
        cd "$OPENEMR_DEV_PATH" && docker-compose up -d
        if [ $? -eq 0 ]; then
            echo "OpenEMR is now starting."
            echo "You can access it here when it's ready: http://localhost:8300 or https://localhost:9300"
        else
            echo "Failed to start OpenEMR Docker environment."
        fi
        ;;
    down)
        echo "Stopping OpenEMR Docker environment..."
        cd "$OPENEMR_DEV_PATH" && docker-compose down
        if [ $? -eq 0 ]; then
            echo "OpenEMR has been stopped."
        else
            echo "Failed to stop OpenEMR Docker environment."
        fi
        ;;
    downv)
        echo "Stopping OpenEMR Docker environment fully..."
        cd "$OPENEMR_DEV_PATH" && docker-compose down -v
        if [ $? -eq 0 ]; then
            echo "OpenEMR has been stopped."
        else
            echo "Failed to stop OpenEMR Docker environment."
        fi
        ;;
    restart)
        echo "Restarting OpenEMR Docker environment..."
        # Stop first
        cd "$OPENEMR_DEV_PATH" && docker-compose down
        if [ $? -ne 0 ]; then
            echo "Failed to stop OpenEMR Docker environment."
            exit 1
        fi
        
        # Then start
        docker-compose up -d
        if [ $? -eq 0 ]; then
            echo "OpenEMR is now restarting."
            echo "You can access it here when it's ready: http://localhost:8300 or https://localhost:9300"
        else
            echo "Failed to start OpenEMR Docker environment."
        fi
        ;;
    *)
        echo "Invalid parameter: $1"
        echo "Usage: ./manage-openemr.sh [up|down|restart]"
        exit 1
        ;;
esac

# Return to original directory
cd - > /dev/null

exit 0