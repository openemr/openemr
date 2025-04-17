#!/bin/bash

# manage-openemr.sh - Script to start or stop OpenEMR Docker environment
# Usage: ./manage-openemr.sh [up|down|restart]

# Set the OpenEMR development path
OPENEMR_DEV_PATH="OpenEMR/docker/development-easy"

# Check if parameter is provided
if [ $# -eq 0 ]; then
    echo "Error: Missing parameter."
    echo "Usage: ./manage-openemr.sh [up|down|restart]"
    exit 1
fi

# Copy docker-compose.yml function
copy_docker_compose() {
    if [ -f "docker-compose.yml" ]; then
        echo "Copying your docker-compose.yml to $OPENEMR_DEV_PATH/"
        cp docker-compose.yml "$OPENEMR_DEV_PATH/"
        if [ $? -eq 0 ]; then
            echo "docker-compose.yml copied successfully."
        else
            echo "Failed to copy docker-compose.yml."
            exit 1
        fi
    else
        echo "Warning: docker-compose.yml not found in current directory."
        echo "Using the default docker-compose.yml in $OPENEMR_DEV_PATH/"
    fi
}

# Process the command
case "$1" in
    up)
        # Copy docker-compose file first
        copy_docker_compose
        
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
        # Copy docker-compose file first
        copy_docker_compose

        echo "Stopping OpenEMR Docker environment..."
        cd "$OPENEMR_DEV_PATH" && docker-compose down
        if [ $? -eq 0 ]; then
            echo "OpenEMR has been stopped."
        else
            echo "Failed to stop OpenEMR Docker environment."
        fi
        ;;
    restart)
        # Copy docker-compose file first
        copy_docker_compose
        
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