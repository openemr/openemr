#!/bin/bash

# OpenEMR Database Backup Script
# Creates compressed backups with Vietnamese UTF-8mb4 support
# Author: Dang Tran <tqvdang@msn.com>

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}💾 OpenEMR Database Backup Utility${NC}"
echo -e "${BLUE}===================================${NC}"

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

# Create backup directory if it doesn't exist
mkdir -p backups

# Generate backup filename with timestamp
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILENAME="openemr_backup_${TIMESTAMP}.sql.gz"
BACKUP_PATH="backups/${BACKUP_FILENAME}"

# Backup type selection
echo -e "${YELLOW}🗃️  Select backup type:${NC}"
echo -e "1. Complete database backup (all tables and data)"
echo -e "2. Structure only backup (schema without data)"
echo -e "3. Data only backup (data without schema)"
echo -e "4. Custom backup (select specific tables)"

read -p "Choose backup type [1-4]: " backup_type

case $backup_type in
    1)
        DUMP_OPTIONS="--single-transaction --routines --triggers --complete-insert"
        BACKUP_DESC="Complete database"
        ;;
    2)
        DUMP_OPTIONS="--no-data --routines --triggers"
        BACKUP_DESC="Structure only"
        BACKUP_FILENAME="openemr_structure_${TIMESTAMP}.sql.gz"
        BACKUP_PATH="backups/${BACKUP_FILENAME}"
        ;;
    3)
        DUMP_OPTIONS="--no-create-info --complete-insert"
        BACKUP_DESC="Data only"
        BACKUP_FILENAME="openemr_data_${TIMESTAMP}.sql.gz"
        BACKUP_PATH="backups/${BACKUP_FILENAME}"
        ;;
    4)
        echo -e "${YELLOW}📋 Available tables:${NC}"
        docker-compose exec -T mariadb mysql -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "SHOW TABLES;"
        echo -e "\n${YELLOW}Enter table names separated by spaces:${NC}"
        read -p "Tables: " tables
        DUMP_OPTIONS="--single-transaction --complete-insert"
        BACKUP_DESC="Custom tables: ${tables}"
        BACKUP_FILENAME="openemr_custom_${TIMESTAMP}.sql.gz"
        BACKUP_PATH="backups/${BACKUP_FILENAME}"
        ;;
    *)
        echo -e "${RED}❌ Invalid option. Exiting.${NC}"
        exit 1
        ;;
esac

echo -e "\n${YELLOW}🚀 Creating ${BACKUP_DESC} backup...${NC}"
echo -e "${BLUE}Database: ${DB_NAME}${NC}"
echo -e "${BLUE}Output: ${BACKUP_PATH}${NC}"

# Perform the backup
if [ "$backup_type" = "4" ]; then
    # Custom table backup
    docker-compose exec -T mariadb mysqldump \
        --user=${DB_USER} \
        --password=${DB_PASSWORD} \
        --default-character-set=utf8mb4 \
        --set-charset \
        --add-drop-table \
        ${DUMP_OPTIONS} \
        ${DB_NAME} ${tables} | gzip > "${BACKUP_PATH}"
else
    # Standard backup
    docker-compose exec -T mariadb mysqldump \
        --user=${DB_USER} \
        --password=${DB_PASSWORD} \
        --default-character-set=utf8mb4 \
        --set-charset \
        --add-drop-table \
        ${DUMP_OPTIONS} \
        ${DB_NAME} | gzip > "${BACKUP_PATH}"
fi

# Check if backup was successful
if [ $? -eq 0 ] && [ -f "${BACKUP_PATH}" ]; then
    BACKUP_SIZE=$(du -h "${BACKUP_PATH}" | cut -f1)
    echo -e "${GREEN}✅ Backup completed successfully!${NC}"
    echo -e "${GREEN}📁 File: ${BACKUP_PATH}${NC}"
    echo -e "${GREEN}📏 Size: ${BACKUP_SIZE}${NC}"
    
    # Create backup metadata
    METADATA_FILE="${BACKUP_PATH}.info"
    cat > "${METADATA_FILE}" << EOF
OpenEMR Database Backup Information
===================================
Backup File: ${BACKUP_FILENAME}
Backup Type: ${BACKUP_DESC}
Database: ${DB_NAME}
Created: $(date)
MySQL Version: $(docker-compose exec -T mariadb mysql --version)
Character Set: utf8mb4_vietnamese_ci
Size: ${BACKUP_SIZE}

Restore Command:
./scripts/restore-db.sh ${BACKUP_FILENAME}
EOF
    
    echo -e "${BLUE}📝 Metadata saved: ${METADATA_FILE}${NC}"
    
    # Backup retention management
    echo -e "\n${YELLOW}🗂️  Backup retention management:${NC}"
    BACKUP_COUNT=$(ls -1 backups/openemr_*.sql.gz 2>/dev/null | wc -l || echo "0")
    echo -e "${BLUE}Current backups: ${BACKUP_COUNT}${NC}"
    
    if [ "$BACKUP_COUNT" -gt 10 ]; then
        echo -e "${YELLOW}⚠️  You have ${BACKUP_COUNT} backups. Consider cleaning up old ones.${NC}"
        read -p "Delete backups older than 30 days? (y/n): " cleanup_choice
        if [ "$cleanup_choice" = "y" ] || [ "$cleanup_choice" = "Y" ]; then
            echo -e "${YELLOW}🧹 Cleaning up old backups...${NC}"
            find backups -name "openemr_*.sql.gz" -mtime +30 -delete
            find backups -name "openemr_*.sql.gz.info" -mtime +30 -delete
            NEW_COUNT=$(ls -1 backups/openemr_*.sql.gz 2>/dev/null | wc -l || echo "0")
            echo -e "${GREEN}✅ Cleanup completed. Remaining backups: ${NEW_COUNT}${NC}"
        fi
    fi
    
    echo -e "\n${GREEN}🎉 Database backup process completed!${NC}"
    
else
    echo -e "${RED}❌ Backup failed!${NC}"
    echo -e "${RED}Check Docker logs: docker-compose logs mariadb${NC}"
    exit 1
fi

# Display helpful information
echo -e "\n${YELLOW}💡 Useful Information:${NC}"
echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "🔄 Restore this backup:"
echo -e "   ./scripts/restore-db.sh ${BACKUP_FILENAME}"
echo -e ""
echo -e "📂 View all backups:"
echo -e "   ls -la backups/"
echo -e ""
echo -e "🗑️  Remove old backups:"
echo -e "   find backups -name '*.sql.gz' -mtime +30 -delete"
echo -e ""
echo -e "📊 Verify backup integrity:"
echo -e "   zcat backups/${BACKUP_FILENAME} | head -20"
echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"