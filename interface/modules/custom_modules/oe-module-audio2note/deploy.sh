#!/bin/bash

#
# @package   OpenEMR
# @link      http://www.open-emr.org
# @author    Sun PC Solutions LLC
# @copyright Copyright (c) 2025 Sun PC Solutions LLC
# @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
#

CONTAINER_NAME="openemr-openemr-1"
# Target base path in container (OpenEMR webroot)
TARGET_BASE_PATH="/var/www/localhost/htdocs/openemr"
# Source path relative to the current working directory (/home/sun/cline/project)
SOURCE_PATH="openemrAudio2Note/openemr"

# Ensure we are in the project root
echo "Changing to project root: /home/sun/cline/project"
cd /home/sun/cline/project || { echo "Failed to cd to project root. Exiting."; exit 1; }

echo "Copying module and form files to container..."
# Copy the module directory into the container's custom_modules directory
# Remove existing module directory in container to ensure a clean copy and prevent duplicates
# The lines for removing and recreating the module directory have been removed.
# docker cp will now handle creating the directory if it doesn't exist,
# and overwriting files if it does, preserving existing files like secret.key.

# Copy specific top-level module files
echo "Copying top-level module files..."
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/composer.json" "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/"
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/openemr.bootstrap.php" "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/"
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/Module.php" "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/"
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/moduleConfig.php" "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/"
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/ModuleManagerListener.php" "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/"
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/ajax_check_status.php" "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/"
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/config.php" "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/"
echo "Removing existing cron_runner.php in container before copy..."
docker exec "$CONTAINER_NAME" rm -f "$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/cron_runner.php"
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/cron_runner.php" "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/"

# Copy the entire src/ directory
echo "Removing existing src/ directory in container before copy..."
docker exec "$CONTAINER_NAME" rm -rf "$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/src/"
docker exec "$CONTAINER_NAME" mkdir -p "$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/src/"
echo "Copying src/ directory..."
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/src/." "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/src/"

# Copy the sql/ directory
echo "Copying sql/ directory..."
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/sql/." "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/sql/"

# Copy the templates/ directory
echo "Copying templates/ directory..."
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/templates/." "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/templates/"

# Copy the config/ directory (for secret.key)
echo "Copying config/ directory..."
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/config/." "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/config/"

# Copy the vendor/ directory
echo "Copying vendor/ directory..."
docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/vendor/." "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note/vendor/"

# Copy the audio_to_note form directory into the container's forms directory
docker cp "$SOURCE_PATH/interface/forms/audio_to_note/." "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/forms/audio_to_note/"

# Copy the history_physical form directory into the container's forms directory
docker cp "$SOURCE_PATH/interface/forms/history_physical/." "$CONTAINER_NAME:$TARGET_BASE_PATH/interface/forms/history_physical/"


echo "Setting permissions in container..."
# Set permissions for the copied module directory
MODULE_PATH="$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note"
if docker exec "$CONTAINER_NAME" [ -d "$MODULE_PATH" ]; then
    echo "Setting module permissions for $MODULE_PATH..."
    docker exec "$CONTAINER_NAME" chown -R apache:root "$MODULE_PATH"
    docker exec "$CONTAINER_NAME" find "$MODULE_PATH" -type d -exec chmod 755 {} \;
    docker exec "$CONTAINER_NAME" find "$MODULE_PATH" -type f -exec chmod 644 {} \;

    # Ensure the config directory exists and has correct permissions
    CONFIG_DIR="$MODULE_PATH/config"
    echo "Ensuring config directory $CONFIG_DIR exists and has permissions..."
    docker exec "$CONTAINER_NAME" mkdir -p "$CONFIG_DIR"
    docker exec "$CONTAINER_NAME" chmod 750 "$CONFIG_DIR"
    docker exec "$CONTAINER_NAME" chown apache:root "$CONFIG_DIR" # Ensure ownership is correct

    # Set restrictive permissions for the secret.key file if it exists
    SECRET_KEY_PATH="$CONFIG_DIR/secret.key"
    if docker exec "$CONTAINER_NAME" [ -f "$SECRET_KEY_PATH" ]; then
        echo "Setting permissions for secret key file $SECRET_KEY_PATH..."
        docker exec "$CONTAINER_NAME" chmod 600 "$SECRET_KEY_PATH"
        docker exec "$CONTAINER_NAME" chown apache:root "$SECRET_KEY_PATH"
    else
        echo "Note: secret.key not found in container yet. It will be created by the module."
    fi
else
    echo "Warning: Module path $MODULE_PATH not found in container after copy."
fi

# Set permissions for the copied audio_to_note form directory
AUDIO_NOTE_FORM_PATH="$TARGET_BASE_PATH/interface/forms/audio_to_note"
if docker exec "$CONTAINER_NAME" [ -d "$AUDIO_NOTE_FORM_PATH" ]; then
    echo "Setting form permissions for $AUDIO_NOTE_FORM_PATH..."
    docker exec "$CONTAINER_NAME" chown -R apache:root "$AUDIO_NOTE_FORM_PATH"
    docker exec "$CONTAINER_NAME" find "$AUDIO_NOTE_FORM_PATH" -type d -exec chmod 755 {} \;
    docker exec "$CONTAINER_NAME" find "$AUDIO_NOTE_FORM_PATH" -type f -exec chmod 644 {} \;
else
    echo "Warning: Form path $AUDIO_NOTE_FORM_PATH not found in container after copy."
fi

# Set permissions for the copied history_physical form directory
HP_FORM_PATH="$TARGET_BASE_PATH/interface/forms/history_physical"
if docker exec "$CONTAINER_NAME" [ -d "$HP_FORM_PATH" ]; then
    echo "Setting form permissions for $HP_FORM_PATH..."
    docker exec "$CONTAINER_NAME" chown -R apache:root "$HP_FORM_PATH"
    docker exec "$CONTAINER_NAME" find "$HP_FORM_PATH" -type d -exec chmod 755 {} \;
    docker exec "$CONTAINER_NAME" find "$HP_FORM_PATH" -type f -exec chmod 644 {} \;
else
    echo "Warning: Form path $HP_FORM_PATH not found in container after copy."
fi

echo "Deployment script finished."
