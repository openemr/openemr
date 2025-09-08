#!/bin/bash

# OpenEMR Database Restore Script
# Restores compressed backups with Vietnamese UTF-8mb4 support
# Author: Dang Tran <tqvdang@msn.com>

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔄 OpenEMR Database Restore Utility${NC}"
echo -e "${BLUE}====================================${NC}"

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo -e "${RED}❌ .env file not found. Please create it from .env.example${NC}"
    exit 1
fi

# Load environment variables
set -a
source .env
set +a

# Check if MariaDB container is running
if ! docker-compose ps mariadb | grep -q "Up"; then
    echo -e "${RED}❌ MariaDB container is not running. Please start it first with:${NC}"
    echo -e "${YELLOW}   ./scripts/start-dev.sh${NC}"
    exit 1
fi

# Handle backup file parameter
BACKUP_FILE=""
if [ -z "$1" ]; then
    echo -e "${YELLOW}📂 Available backup files:${NC}"
    if [ -d "backups" ] && [ "$(ls -A backups/*.sql.gz 2>/dev/null)" ]; then
        ls -la backups/*.sql.gz | awk '{print "   " $9 " (" $5 " bytes, " $6 " " $7 " " $8 ")"}'
        echo -e "\n${YELLOW}Enter backup filename (with or without path):${NC}"
        read -p "Backup file: " BACKUP_FILE
        
        # If user provided just filename, prepend the path
        if [[ "$BACKUP_FILE" != backups/* ]]; then
            BACKUP_FILE="backups/${BACKUP_FILE}"
        fi
    else
        echo -e "${RED}❌ No backup files found in backups/${NC}"
        echo -e "${YELLOW}💡 Create a backup first with: ./scripts/backup-db.sh${NC}"
        exit 1
    fi
else
    BACKUP_FILE="$1"
    # If user provided just filename, prepend the path
    if [[ "$BACKUP_FILE" != /* ]] && [[ "$BACKUP_FILE" != backups/* ]]; then
        BACKUP_FILE="backups/${BACKUP_FILE}"
    fi
fi

# Verify backup file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}❌ Backup file not found: $BACKUP_FILE${NC}"
    exit 1
fi

# Show backup file information
echo -e "\n${BLUE}📋 Backup Information:${NC}"
echo -e "${BLUE}File: $BACKUP_FILE${NC}"
BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
echo -e "${BLUE}Size: $BACKUP_SIZE${NC}"
BACKUP_DATE=$(stat -f "%Sm" -t "%Y-%m-%d %H:%M:%S" "$BACKUP_FILE" 2>/dev/null || stat -c "%y" "$BACKUP_FILE" 2>/dev/null | cut -d'.' -f1)
echo -e "${BLUE}Created: $BACKUP_DATE${NC}"

# Check if metadata file exists
METADATA_FILE="${BACKUP_FILE}.info"
if [ -f "$METADATA_FILE" ]; then
    echo -e "\n${YELLOW}📝 Backup Metadata:${NC}"
    cat "$METADATA_FILE" | grep -E "(Backup Type|Database|MySQL Version)" | sed 's/^/   /'
fi

# Verify backup file integrity
echo -e "\n${YELLOW}🔍 Verifying backup file integrity...${NC}"
if ! zcat "$BACKUP_FILE" | head -5 | grep -q "MySQL dump"; then
    echo -e "${RED}❌ Backup file appears to be corrupted or invalid${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Backup file integrity verified${NC}"

# Safety warning
echo -e "\n${RED}⚠️  WARNING: This will replace the current database!${NC}"
echo -e "${RED}Current database '${DB_NAME}' will be dropped and recreated.${NC}"

# Ask for confirmation
read -p "Are you sure you want to continue? Type 'YES' to confirm: " confirm
if [ "$confirm" != "YES" ]; then
    echo -e "${YELLOW}❌ Restore cancelled by user.${NC}"
    exit 0
fi

# Offer to create a pre-restore backup
read -p "Create a backup of current database before restore? (y/n): " backup_current
if [ "$backup_current" = "y" ] || [ "$backup_current" = "Y" ]; then
    echo -e "${YELLOW}💾 Creating pre-restore backup...${NC}"
    if [ -f "./scripts/backup-db.sh" ]; then
        PRE_RESTORE_BACKUP="openemr_pre_restore_$(date +"%Y%m%d_%H%M%S").sql.gz"
        echo "Creating pre-restore backup as: $PRE_RESTORE_BACKUP"
        docker-compose exec -T mariadb mysqldump \
            --user=${DB_USER} \
            --password=${DB_PASSWORD} \
            --default-character-set=utf8mb4 \
            --single-transaction \
            --routines \
            --triggers \
            ${DB_NAME} | gzip > "backups/${PRE_RESTORE_BACKUP}"
        echo -e "${GREEN}✅ Pre-restore backup created: backups/${PRE_RESTORE_BACKUP}${NC}"
    else
        echo -e "${YELLOW}⚠️  Backup script not found, continuing with restore...${NC}"
    fi
fi

# Begin restore process
echo -e "\n${YELLOW}🚀 Starting database restore...${NC}"

# Step 1: Drop and recreate database
echo -e "${YELLOW}1/4 Recreating database...${NC}"
docker-compose exec -T mariadb mysql \
    --user=root \
    --password=${DB_ROOT_PASSWORD} \
    -e "DROP DATABASE IF EXISTS \`${DB_NAME}\`; 
        CREATE DATABASE \`${DB_NAME}\` 
        CHARACTER SET utf8mb4 
        COLLATE utf8mb4_vietnamese_ci;"

echo -e "${GREEN}✅ Database recreated with Vietnamese UTF-8mb4 support${NC}"

# Step 2: Restore the database
echo -e "${YELLOW}2/4 Restoring data from backup...${NC}"
zcat "$BACKUP_FILE" | docker-compose exec -T mariadb mysql \
    --user=${DB_USER} \
    --password=${DB_PASSWORD} \
    --default-character-set=utf8mb4 \
    ${DB_NAME}

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Database restore completed successfully${NC}"
else
    echo -e "${RED}❌ Database restore failed!${NC}"
    echo -e "${RED}Check the backup file and try again.${NC}"
    exit 1
fi

# Step 3: Verify restoration
echo -e "${YELLOW}3/4 Verifying restoration...${NC}"

# Check if tables were restored
TABLE_COUNT=$(docker-compose exec -T mariadb mysql \
    --user=${DB_USER} \
    --password=${DB_PASSWORD} \
    ${DB_NAME} \
    -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';" \
    -s -N)

if [ "$TABLE_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✅ Restored $TABLE_COUNT tables${NC}"
else
    echo -e "${RED}❌ No tables found after restore${NC}"
    exit 1
fi

# Verify Vietnamese character support
echo -e "${YELLOW}4/4 Verifying Vietnamese character support...${NC}"
CHARSET_CHECK=$(docker-compose exec -T mariadb mysql \
    --user=${DB_USER} \
    --password=${DB_PASSWORD} \
    ${DB_NAME} \
    -e "SELECT DEFAULT_CHARACTER_SET_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='${DB_NAME}';" \
    -s -N)

if [ "$CHARSET_CHECK" = "utf8mb4" ]; then
    echo -e "${GREEN}✅ Vietnamese UTF-8mb4 character set verified${NC}"
else
    echo -e "${YELLOW}⚠️  Character set: $CHARSET_CHECK (expected: utf8mb4)${NC}"
fi

# Test Vietnamese characters if test table exists
VIETNAMESE_TEST=$(docker-compose exec -T mariadb mysql \
    --user=${DB_USER} \
    --password=${DB_PASSWORD} \
    ${DB_NAME} \
    -e "SELECT COUNT(*) FROM vietnamese_test WHERE vietnamese_text LIKE '%Vật lý trị liệu%';" \
    -s -N 2>/dev/null || echo "0")

if [ "$VIETNAMESE_TEST" -gt 0 ]; then
    echo -e "${GREEN}✅ Vietnamese character data verified${NC}"
else
    echo -e "${YELLOW}⚠️  Vietnamese test data not found (may not be in this backup)${NC}"
fi

# Display restoration summary
echo -e "\n${GREEN}🎉 Database restoration completed successfully!${NC}"
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}Database: ${DB_NAME}${NC}"
echo -e "${GREEN}Tables restored: ${TABLE_COUNT}${NC}"
echo -e "${GREEN}Character set: ${CHARSET_CHECK}${NC}"
echo -e "${GREEN}Backup source: $(basename "$BACKUP_FILE")${NC}"

# Show helpful next steps
echo -e "\n${YELLOW}💡 Next Steps:${NC}"
echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "🌐 Access phpMyAdmin: http://localhost:${PMA_PORT}"
echo -e "🔍 Access Adminer: http://localhost:${ADMINER_PORT:-8082}"
echo -e "📊 View database logs: docker-compose logs mariadb"
echo -e "🔄 Create new backup: ./scripts/backup-db.sh"
echo -e "🏥 Start your local OpenEMR application"
echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo -e "\n${BLUE}📝 Restore log saved to: docker/logs/restore_$(date +"%Y%m%d_%H%M%S").log${NC}"