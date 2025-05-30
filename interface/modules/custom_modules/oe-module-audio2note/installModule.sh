#!/bin/bash

#
# @package   OpenEMR
# @link      http://www.open-emr.org
# @author    Sun PC Solutions LLC
# @copyright Copyright (c) 2025 Sun PC Solutions LLC
# @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
#

# Get the directory where the script is located
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)

# Source path relative to the script's directory
SOURCE_PATH="$SCRIPT_DIR/openemr"

echo "--- OpenEMR Audio to Note Module Installation ---"
echo ""

# --- Installation Type ---
INSTALL_TYPE=""
while [[ "$INSTALL_TYPE" != "docker" && "$INSTALL_TYPE" != "host" ]]; do
  read -p "Is your OpenEMR instance installed in Docker or directly on the host? (Enter '1' for Docker or '2' for Host): " USER_INPUT
  case "$USER_INPUT" in
    1) INSTALL_TYPE="docker" ;;
    2) INSTALL_TYPE="host" ;;
    *)
      echo "Invalid input. Please enter '1' or '2'."
      INSTALL_TYPE="" # Reset INSTALL_TYPE to ensure loop continues
      ;;
  esac
done

# --- Variables based on installation type ---
CONTAINER_NAME=""
TARGET_BASE_PATH=""
DB_NAME="openemr" # Default, can be overridden for host
DB_USER="root"    # Default, can be overridden for host
DB_PASS="root"    # Default, can be overridden for host

if [ "$INSTALL_TYPE" == "docker" ]; then
  read -p "Please enter the Docker container name for your OpenEMR instance: " CONTAINER_NAME
  if [ -z "$CONTAINER_NAME" ]; then
    echo "Error: Docker container name cannot be empty. Exiting."
    exit 1
  fi
  TARGET_BASE_PATH="/var/www/localhost/htdocs/openemr" # Standard for OpenEMR Docker
  echo "Using Docker container: $CONTAINER_NAME"
  echo "Target base path in container: $TARGET_BASE_PATH"

elif [ "$INSTALL_TYPE" == "host" ]; then
  read -p "Please enter the full path to your OpenEMR webroot directory (e.g., /var/www/html/openemr): " TARGET_BASE_PATH
  if [ -z "$TARGET_BASE_PATH" ]; then
    echo "Error: OpenEMR webroot directory cannot be empty. Exiting."
    exit 1
  fi
  if [ ! -d "$TARGET_BASE_PATH" ]; then
    echo "Error: The specified webroot directory '$TARGET_BASE_PATH' does not exist. Exiting."
    exit 1
  fi
  echo "Using host installation path: $TARGET_BASE_PATH"

  echo ""
  echo "Please provide MySQL database credentials for OpenEMR:"
  read -p "Enter OpenEMR database name (default: openemr): " HOST_DB_NAME
  DB_NAME=${HOST_DB_NAME:-$DB_NAME}

  read -p "Enter MySQL username for OpenEMR (default: root): " HOST_DB_USER
  DB_USER=${HOST_DB_USER:-$DB_USER}

  read -s -p "Enter MySQL password for OpenEMR: " HOST_DB_PASS
  echo "" # Newline after password input
  DB_PASS=${HOST_DB_PASS:-$DB_PASS} # Use entered password, or default if empty (though password should not be empty ideally)
  if [ -z "$DB_PASS" ] && [ -n "$HOST_DB_PASS" ]; then # if HOST_DB_PASS was set (even to empty) but DB_PASS is now empty
      DB_PASS="$HOST_DB_PASS" # ensure it's set if user explicitly entered empty
  elif [ -z "$DB_PASS" ] && [ -z "$HOST_DB_PASS" ] && [ "$INSTALL_TYPE" == "host" ]; then
      echo "Warning: MySQL password is empty. This is not recommended for host installations."
  fi
fi

echo ""
echo "--- Starting File Copy & Permissions ---"

MODULE_DEST_PATH="$TARGET_BASE_PATH/interface/modules/custom_modules/openemrAudio2Note"
FORM_AUDIO_DEST_PATH="$TARGET_BASE_PATH/interface/forms/audio_to_note"
FORM_HP_DEST_PATH="$TARGET_BASE_PATH/interface/forms/history_physical"

if [ "$INSTALL_TYPE" == "docker" ]; then
  echo "Copying module and form files to container $CONTAINER_NAME..."
  docker cp "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/." "$CONTAINER_NAME:$MODULE_DEST_PATH/"
  docker cp "$SOURCE_PATH/interface/forms/audio_to_note/." "$CONTAINER_NAME:$FORM_AUDIO_DEST_PATH/"
  docker cp "$SOURCE_PATH/interface/forms/history_physical/." "$CONTAINER_NAME:$FORM_HP_DEST_PATH/"

  echo "Setting permissions in container $CONTAINER_NAME..."
  if docker exec "$CONTAINER_NAME" [ -d "$MODULE_DEST_PATH" ]; then
    echo "Setting module permissions for $MODULE_DEST_PATH..."
    docker exec "$CONTAINER_NAME" chown -R apache:root "$MODULE_DEST_PATH"
    docker exec "$CONTAINER_NAME" find "$MODULE_DEST_PATH" -type d -exec chmod 755 {} \;
    docker exec "$CONTAINER_NAME" find "$MODULE_DEST_PATH" -type f -exec chmod 644 {} \;
  else
    echo "Warning: Module path $MODULE_DEST_PATH not found in container after copy."
  fi

  if docker exec "$CONTAINER_NAME" [ -d "$FORM_AUDIO_DEST_PATH" ]; then
    echo "Setting audio_to_note form permissions for $FORM_AUDIO_DEST_PATH..."
    docker exec "$CONTAINER_NAME" chown -R apache:root "$FORM_AUDIO_DEST_PATH"
    docker exec "$CONTAINER_NAME" find "$FORM_AUDIO_DEST_PATH" -type d -exec chmod 755 {} \;
    docker exec "$CONTAINER_NAME" find "$FORM_AUDIO_DEST_PATH" -type f -exec chmod 644 {} \;
  else
    echo "Warning: audio_to_note form path $FORM_AUDIO_DEST_PATH not found in container after copy."
  fi

  if docker exec "$CONTAINER_NAME" [ -d "$FORM_HP_DEST_PATH" ]; then
    echo "Setting history_physical form permissions for $FORM_HP_DEST_PATH..."
    docker exec "$CONTAINER_NAME" chown -R apache:root "$FORM_HP_DEST_PATH"
    docker exec "$CONTAINER_NAME" find "$FORM_HP_DEST_PATH" -type d -exec chmod 755 {} \;
    docker exec "$CONTAINER_NAME" find "$FORM_HP_DEST_PATH" -type f -exec chmod 644 {} \;
  else
    echo "Warning: history_physical form path $FORM_HP_DEST_PATH not found in container after copy."
  fi
 
elif [ "$INSTALL_TYPE" == "host" ]; then
  echo "Copying module and form files to host path $TARGET_BASE_PATH..."
  echo "Note: You might need to run this script with sudo or ensure you have write permissions to $TARGET_BASE_PATH and its subdirectories."

  # Create destination directories if they don't exist
  mkdir -p "$MODULE_DEST_PATH"
  mkdir -p "$FORM_AUDIO_DEST_PATH"
  mkdir -p "$FORM_HP_DEST_PATH"

  cp -R "$SOURCE_PATH/interface/modules/custom_modules/openemrAudio2Note/." "$MODULE_DEST_PATH/"
  cp -R "$SOURCE_PATH/interface/forms/audio_to_note/." "$FORM_AUDIO_DEST_PATH/"
  cp -R "$SOURCE_PATH/interface/forms/history_physical/." "$FORM_HP_DEST_PATH/"

  echo "Setting permissions on host..."
  echo "Attempting to set ownership and permissions. This may require sudo."
  echo "The typical web server user is 'www-data' or 'apache'. Adjust if needed."
  WEB_USER="apache" # Common default, adjust if your system uses 'www-data' or other
  # Try to determine common web server user
  if id "www-data" &> /dev/null; then
      WEB_USER="www-data"
  elif id "apache" &> /dev/null; then
      WEB_USER="apache"
  fi
  echo "Using web server user: $WEB_USER (Please verify this is correct for your system)"


  SUDO_CMD=""
  if [ "$(id -u)" != "0" ]; then # if not root
      if command -v sudo &> /dev/null; then
          echo "Sudo detected. Will attempt to use sudo for permission changes."
          SUDO_CMD="sudo"
      else
          echo "Warning: sudo not found, and script is not run as root. Permission changes might fail."
          echo "Please ensure the following directories and their contents are readable/writable by your web server user ($WEB_USER):"
          echo "  - $MODULE_DEST_PATH"
          echo "  - $FORM_AUDIO_DEST_PATH"
          echo "  - $FORM_HP_DEST_PATH"
      fi
  fi


  if [ -d "$MODULE_DEST_PATH" ]; then
    echo "Setting module permissions for $MODULE_DEST_PATH..."
    $SUDO_CMD chown -R "$WEB_USER":"$WEB_USER" "$MODULE_DEST_PATH" # Often web user is also group
    $SUDO_CMD find "$MODULE_DEST_PATH" -type d -exec chmod 755 {} \;
    $SUDO_CMD find "$MODULE_DEST_PATH" -type f -exec chmod 644 {} \;
  else
    echo "Warning: Module path $MODULE_DEST_PATH not found on host after copy."
  fi

  if [ -d "$FORM_AUDIO_DEST_PATH" ]; then
    echo "Setting audio_to_note form permissions for $FORM_AUDIO_DEST_PATH..."
    $SUDO_CMD chown -R "$WEB_USER":"$WEB_USER" "$FORM_AUDIO_DEST_PATH"
    $SUDO_CMD find "$FORM_AUDIO_DEST_PATH" -type d -exec chmod 755 {} \;
    $SUDO_CMD find "$FORM_AUDIO_DEST_PATH" -type f -exec chmod 644 {} \;
  else
    echo "Warning: audio_to_note form path $FORM_AUDIO_DEST_PATH not found on host after copy."
  fi

  if [ -d "$FORM_HP_DEST_PATH" ]; then
    echo "Setting history_physical form permissions for $FORM_HP_DEST_PATH..."
    $SUDO_CMD chown -R "$WEB_USER":"$WEB_USER" "$FORM_HP_DEST_PATH"
    $SUDO_CMD find "$FORM_HP_DEST_PATH" -type d -exec chmod 755 {} \;
    $SUDO_CMD find "$FORM_HP_DEST_PATH" -type f -exec chmod 644 {} \;
  else
    echo "Warning: history_physical form path $FORM_HP_DEST_PATH not found on host after copy."
  fi
  if [ -n "$SUDO_CMD" ]; then
      echo "If you encountered permission errors above, please re-run the script with sudo or manually set the permissions."
  fi
fi

echo "--- File Copy & Permissions Complete ---"
echo ""

echo "--- Running Database Schema Installation and Update ---"
SQL_COMMAND="
CREATE TABLE IF NOT EXISTS \`form_audio_to_note\` (
  \`id\` bigint(20) NOT NULL AUTO_INCREMENT,
  \`date\` datetime DEFAULT NULL,
  \`pid\` bigint(20) DEFAULT NULL,
  \`encounter\` bigint(20) DEFAULT NULL,
  \`user\` bigint(20) DEFAULT NULL,
  \`groupname\` varchar(255) DEFAULT NULL,
  \`authorized\` tinyint(4) DEFAULT NULL,
  \`activity\` tinyint(4) DEFAULT NULL,
  \`audio_filename\` varchar(255) DEFAULT NULL,
  \`transcription_params\` JSON DEFAULT NULL,
  \`transcription_service_response\` JSON DEFAULT NULL,
  \`status\` varchar(50) DEFAULT 'pending_upload',
  \`note_type\` varchar(50) DEFAULT 'soap',
  \`transcription_job_id\` varchar(255) DEFAULT NULL,
  \`linked_forms_id\` bigint(20) DEFAULT NULL,
  \`soap_note_id\` bigint(20) DEFAULT NULL,
  \`history_physical_form_id\` bigint(20) DEFAULT NULL,
  \`raw_transcript\` MEDIUMTEXT DEFAULT NULL,
  \`error_message\` TEXT DEFAULT NULL,
  PRIMARY KEY (\`id\`),
  KEY \`pid\` (\`pid\`),
  KEY \`encounter\` (\`encounter\`),
  KEY \`status\` (\`status\`),
  KEY \`transcription_job_id\` (\`transcription_job_id\`),
  KEY \`linked_forms_id\` (\`linked_forms_id\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

# ALTER TABLE \`form_audio_to_note\` ADD COLUMN IF NOT EXISTS \`encounter\` bigint(20) DEFAULT NULL;
# ALTER TABLE \`form_audio_to_note\` ADD COLUMN IF NOT EXISTS \`note_type\` VARCHAR(50) DEFAULT 'soap';
# ALTER TABLE \`form_audio_to_note\` ADD COLUMN IF NOT EXISTS \`status\` VARCHAR(50) DEFAULT 'pending_upload'; # Adjusted default
# ALTER TABLE \`form_audio_to_note\` ADD COLUMN IF NOT EXISTS \`transcription_job_id\` VARCHAR(255) NULL DEFAULT NULL;
# ALTER TABLE \`form_audio_to_note\` ADD COLUMN IF NOT EXISTS \`soap_note_id\` bigint(20) DEFAULT NULL;
# ALTER TABLE \`form_audio_to_note\` ADD COLUMN IF NOT EXISTS \`history_physical_form_id\` bigint(20) DEFAULT NULL;
# ALTER TABLE \`form_audio_to_note\` ADD COLUMN IF NOT EXISTS \`raw_transcript\` MEDIUMTEXT DEFAULT NULL;
# ALTER TABLE \`form_audio_to_note\` ADD COLUMN IF NOT EXISTS \`linked_forms_id\` bigint(20) DEFAULT NULL;
# The index for linked_forms_id is now in the CREATE TABLE statement.
# If an older version of the table exists without the index, this explicit ADD INDEX might still be useful.
# However, for a clean install, it's redundant. For robustness during upgrades, it can be kept.
ALTER TABLE \`form_audio_to_note\` ADD INDEX IF NOT EXISTS \`linked_forms_id\` (\`linked_forms_id\`);

CREATE TABLE IF NOT EXISTS \`audio2note_config\` (
 \`id\` int(11) NOT NULL AUTO_INCREMENT,
 \`openemr_internal_random_uuid\` varchar(36) DEFAULT NULL,
 \`effective_instance_identifier\` varchar(64) DEFAULT NULL,
 \`encrypted_license_key\` text DEFAULT NULL,
 \`encrypted_license_consumer_key\` text DEFAULT NULL,
 \`encrypted_license_consumer_secret\` text DEFAULT NULL,
 \`encrypted_dlm_activation_token\` text DEFAULT NULL,
 \`license_status\` varchar(50) DEFAULT 'inactive',
 \`license_expires_at\` datetime DEFAULT NULL,
 \`last_validation_timestamp\` datetime DEFAULT NULL,
 \`created_at\` datetime DEFAULT NULL,
 \`updated_at\` datetime DEFAULT NULL,
 PRIMARY KEY (\`id\`),
 UNIQUE KEY \`openemr_internal_random_uuid\` (\`openemr_internal_random_uuid\`),
 KEY \`effective_instance_identifier\` (\`effective_instance_identifier\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

# Ensure a default row exists for configuration, if not already present by Setup.php
INSERT IGNORE INTO \`audio2note_config\` (\`id\`, \`created_at\`, \`updated_at\`) VALUES (1, NOW(), NOW());

INSERT IGNORE INTO \`background_services\` (\`name\`, \`title\`, \`active\`, \`running\`, \`next_run\`, \`execute_interval\`, \`function\`, \`require_once\`, \`sort_order\`) VALUES ('AudioToNote_Polling', 'Audio To Note Transcription Polling', 1, 0, NOW(), 5, 'runAudioToNotePolling', '/interface/modules/custom_modules/openemrAudio2Note/cron_runner.php', 110);
"

if [ "$INSTALL_TYPE" == "docker" ]; then
  echo "Executing SQL commands in container $CONTAINER_NAME..."
  docker exec "$CONTAINER_NAME" mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$SQL_COMMAND"
  if [ $? -ne 0 ]; then
      echo "Error: SQL command execution in Docker container failed. Please check the output above and MySQL logs in the container."
      echo "The SQL_COMMAND block was:"
      echo "$SQL_COMMAND"
  fi
elif [ "$INSTALL_TYPE" == "host" ]; then
  echo "Executing SQL commands on host..."
  echo "Attempting to connect to MySQL as user '$DB_USER' for database '$DB_NAME'."
  echo "If this fails, ensure MySQL client is installed and credentials are correct."
  mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$SQL_COMMAND"
  if [ $? -ne 0 ]; then
      echo "Error: Database command execution failed. Please check your MySQL credentials and ensure the database '$DB_NAME' exists and user '$DB_USER' has permissions."
  fi
fi
echo "--- Database Schema Installation and Update Complete ---"
echo ""

echo "--- API Client Registration ---"
if [ "$INSTALL_TYPE" == "docker" ]; then
  echo "Automating API Client Registration in container $CONTAINER_NAME..."
  # The client_id and client_secret will be output by this command. These need to be used by the backendAudioProcess workflow.
  docker exec "$CONTAINER_NAME" curl -X POST -k -H 'Content-Type: application/json' -i http://localhost/oauth2/default/registration --data '{"application_type": "private","redirect_uris": ["https://www.sunpcsolutions.org/callback"],"client_name": "audio_processing_service_writer","token_endpoint_auth_method": "client_secret_post","scope": "openid api:oemr user/soap_note.read user/soap_note.write"}'
  echo "Note: The above curl command attempts to register the API client. Ensure OpenEMR is fully started."
  echo "The client_id and client_secret will be output by this command if successful. These need to be used by the audio processing service workflow."
elif [ "$INSTALL_TYPE" == "host" ]; then
  echo "API Client Registration for host installations:"
  echo "Please manually register the 'audio_processing_service_writer' API client via the OpenEMR Admin UI."
  echo "Go to: Administration -> System -> API Clients."
  echo "Use the following details (or adapt as needed):"
  echo "  Application Type: private"
  echo "  Redirect URIs: https://www.sunpcsolutions.org/callback (or your audio processing service callback if different)"
  echo "  Client Name: audio_processing_service_writer"
  echo "  Token Endpoint Auth Method: client_secret_post"
  echo "  Scope: openid api:oemr user/soap_note.read user/soap_note.write"
  echo "Alternatively, you can adapt the curl command found in this script to your environment if you run it directly on the web server or can reach the OpenEMR instance."
fi
echo "--- API Client Registration Complete ---"
echo ""

echo "--- Manual Installation & Enabling Steps Required ---"
echo "Please log in to OpenEMR as administrator and complete the following steps via the web interface:"
echo "1. Go to Administration -> config -> connectors -> Enable OAuth2 Password Grant = On for Users Role"
echo "2. Go to Administration -> System -> API Clients and ensure the 'audio_processing_service_writer' client is present and enabled."
echo "   (This client is for potential future OpenEMR internal API usage, not just for the audio processing service to write notes)."
echo "3. Go to Administration -> Modules -> Manage Modules and install/enable the 'OpenEMR Audio to Note' module."
echo "4. Go to Administration -> Forms and register and install the database for the 'Audio to Note' form."
echo "5. Go to Administration -> Forms and register and install the database for the 'History and Physical Note' form."
echo "6. Enable both forms."
echo ""
echo "--- OpenEMR Audio to Note Module Installation Script Finished ---"
