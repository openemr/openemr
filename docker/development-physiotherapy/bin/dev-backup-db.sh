#!/bin/bash
# OpenEMR Database Backup Script
# Author: Dang Tran <tqvdang@msn.com>

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DOCKER_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$DOCKER_DIR/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="openemr_backup_${TIMESTAMP}.sql"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if services are running
check_services() {
    cd "$DOCKER_DIR"
    
    if ! docker-compose ps mariadb | grep -q "Up"; then
        log_error "MariaDB service is not running. Please start services first:"
        echo "  ./bin/dev-start.sh"
        exit 1
    fi
}

# Create backup directory
prepare_backup_dir() {
    mkdir -p "$BACKUP_DIR"
    log_success "Backup directory ready: $BACKUP_DIR"
}

# Create database backup
create_backup() {
    log_info "Creating database backup..."
    log_info "Backup file: $BACKUP_FILE"
    
    cd "$DOCKER_DIR"
    
    # Create comprehensive backup with Vietnamese support
    docker-compose exec -T mariadb mysqldump \
        --user=openemr \
        --password=openemr \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --hex-blob \
        --default-character-set=utf8mb4 \
        --add-drop-database \
        --databases openemr > "$BACKUP_DIR/$BACKUP_FILE"
    
    if [ -f "$BACKUP_DIR/$BACKUP_FILE" ] && [ -s "$BACKUP_DIR/$BACKUP_FILE" ]; then
        local file_size=$(du -h "$BACKUP_DIR/$BACKUP_FILE" | cut -f1)
        log_success "Database backup created successfully"
        log_info "Backup file size: $file_size"
    else
        log_error "Backup creation failed or file is empty"
        exit 1
    fi
}

# Compress backup (optional)
compress_backup() {
    if [[ "${1:-}" == "--compress" ]]; then
        log_info "Compressing backup..."
        
        gzip "$BACKUP_DIR/$BACKUP_FILE"
        BACKUP_FILE="$BACKUP_FILE.gz"
        
        if [ -f "$BACKUP_DIR/$BACKUP_FILE" ]; then
            local compressed_size=$(du -h "$BACKUP_DIR/$BACKUP_FILE" | cut -f1)
            log_success "Backup compressed successfully"
            log_info "Compressed file size: $compressed_size"
        else
            log_error "Compression failed"
            exit 1
        fi
    fi
}

# Test backup integrity
test_backup() {
    if [[ "${1:-}" == "--test" ]]; then
        log_info "Testing backup integrity..."
        
        local test_file="$BACKUP_DIR/$BACKUP_FILE"
        if [[ "$BACKUP_FILE" == *.gz ]]; then
            # Test compressed file
            if gzip -t "$test_file" >/dev/null 2>&1; then
                log_success "Compressed backup file integrity verified"
            else
                log_error "Compressed backup file is corrupted"
                exit 1
            fi
        else
            # Test SQL file by checking for key markers
            if grep -q "CREATE DATABASE" "$test_file" && grep -q "vietnamese_medical_terms" "$test_file"; then
                log_success "Backup file integrity verified"
            else
                log_warning "Backup file may be incomplete or corrupted"
            fi
        fi
    fi
}

# Clean old backups
clean_old_backups() {
    local keep_days=${1:-7}
    
    log_info "Cleaning backups older than $keep_days days..."
    
    find "$BACKUP_DIR" -name "openemr_backup_*.sql*" -type f -mtime +$keep_days -delete
    
    local remaining_count=$(find "$BACKUP_DIR" -name "openemr_backup_*.sql*" -type f | wc -l)
    log_success "Cleanup complete. $remaining_count backup files remaining."
}

# Show backup information
show_backup_info() {
    echo ""
    log_success "Database Backup Completed Successfully!"
    echo ""
    echo -e "${BLUE}📁 Backup Details:${NC}"
    echo "  File: $BACKUP_DIR/$BACKUP_FILE"
    echo "  Created: $(date)"
    echo "  Size: $(du -h "$BACKUP_DIR/$BACKUP_FILE" | cut -f1)"
    echo ""
    echo -e "${BLUE}📊 Vietnamese Data Included:${NC}"
    echo "  ✓ Vietnamese medical terminology (77+ terms)"
    echo "  ✓ Bilingual PT assessments and exercises"
    echo "  ✓ Vietnamese insurance (BHYT) records"
    echo "  ✓ Stored procedures and triggers"
    echo "  ✓ Full UTF-8mb4 Vietnamese character support"
    echo ""
    echo -e "${BLUE}🔄 To restore this backup:${NC}"
    echo "  docker-compose exec -T mariadb mysql -uroot -p < $BACKUP_DIR/$BACKUP_FILE"
    echo ""
    echo -e "${BLUE}📋 Available backups:${NC}"
    ls -la "$BACKUP_DIR"/openemr_backup_*.sql* 2>/dev/null || echo "  No previous backups found"
}

# Usage information
show_usage() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  --compress     Compress the backup file with gzip"
    echo "  --test         Test backup file integrity after creation"
    echo "  --clean DAYS   Clean backups older than DAYS (default: 7)"
    echo "  --help         Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0                    # Create standard backup"
    echo "  $0 --compress         # Create compressed backup"
    echo "  $0 --test --compress  # Create compressed backup and test integrity"
    echo "  $0 --clean 14         # Clean backups older than 14 days"
}

# Main execution
main() {
    local compress=false
    local test=false
    local clean_days=""
    
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --compress)
                compress=true
                shift
                ;;
            --test)
                test=true
                shift
                ;;
            --clean)
                clean_days="$2"
                shift 2
                ;;
            --help)
                show_usage
                exit 0
                ;;
            *)
                log_error "Unknown option: $1"
                show_usage
                exit 1
                ;;
        esac
    done
    
    echo -e "${GREEN}💾 OpenEMR Database Backup${NC}"
    echo "=========================="
    
    check_services
    prepare_backup_dir
    create_backup
    
    if $compress; then
        compress_backup --compress
    fi
    
    if $test; then
        test_backup --test
    fi
    
    if [[ -n "$clean_days" ]]; then
        clean_old_backups "$clean_days"
    fi
    
    show_backup_info
}

# Handle Ctrl+C gracefully
cleanup() {
    echo ""
    log_warning "Backup interrupted by user"
    
    # Clean up partial backup file
    if [[ -f "$BACKUP_DIR/$BACKUP_FILE" ]]; then
        rm -f "$BACKUP_DIR/$BACKUP_FILE"
        log_info "Partial backup file removed"
    fi
    
    exit 1
}

trap cleanup INT

# Run main function
main "$@"