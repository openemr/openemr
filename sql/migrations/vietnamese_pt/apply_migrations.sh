#!/bin/bash
################################################################################
# Vietnamese PT Migration Application Script
#
# Author: Dang Tran <tqvdang@msn.com>
# Version: 1.0.0
# Description: Automates application and rollback of Vietnamese PT migrations
#
# Usage:
#   ./apply_migrations.sh              # Apply all pending migrations
#   ./apply_migrations.sh 001          # Apply specific migration
#   ./apply_migrations.sh rollback 001 # Rollback specific migration
#   ./apply_migrations.sh status       # Show migration status
################################################################################

set -e  # Exit on error

# Configuration
DB_NAME="${DB_NAME:-openemr}"
DB_USER="${DB_USER:-openemr}"
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Migration order (important - must be in dependency order)
MIGRATIONS=(
    "000_migration_schema"
    "001_add_indexes"
    "002_add_foreign_keys"
)

################################################################################
# Utility Functions
################################################################################

print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

################################################################################
# Database Functions
################################################################################

# Execute SQL file
execute_sql_file() {
    local sql_file="$1"
    local description="$2"

    if [ ! -f "$sql_file" ]; then
        print_error "SQL file not found: $sql_file"
        return 1
    fi

    print_info "Executing: $description"

    # Prompt for password if not set
    if [ -z "$DB_PASS" ]; then
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p "$DB_NAME" < "$sql_file"
    else
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$sql_file"
    fi

    if [ $? -eq 0 ]; then
        print_success "$description completed"
        return 0
    else
        print_error "$description failed"
        return 1
    fi
}

# Execute SQL query
execute_sql_query() {
    local query="$1"

    if [ -z "$DB_PASS" ]; then
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p "$DB_NAME" -e "$query"
    else
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$query"
    fi
}

# Check if migration is applied
is_migration_applied() {
    local migration_id="$1"

    # First check if migration table exists
    local table_exists
    table_exists=$(execute_sql_query "SHOW TABLES LIKE 'vietnamese_pt_migrations'" 2>/dev/null | grep -c "vietnamese_pt_migrations" || echo "0")

    if [ "$table_exists" = "0" ]; then
        echo "0"
        return
    fi

    # Check migration status
    local count
    count=$(execute_sql_query "SELECT COUNT(*) as cnt FROM vietnamese_pt_migrations WHERE migration_id = '$migration_id' AND status = 'applied'" 2>/dev/null | tail -n 1 || echo "0")
    echo "$count"
}

################################################################################
# Migration Functions
################################################################################

# Apply single migration
apply_migration() {
    local migration_id="$1"
    local sql_file="${SCRIPT_DIR}/${migration_id}.sql"

    print_header "Applying Migration: $migration_id"

    # Check if already applied
    local applied
    applied=$(is_migration_applied "$migration_id")

    if [ "$applied" != "0" ]; then
        print_warning "Migration $migration_id already applied, skipping"
        return 0
    fi

    # Record start time
    local start_time
    start_time=$(date +%s%3N)

    # Execute migration
    if execute_sql_file "$sql_file" "Migration $migration_id"; then
        # Calculate execution time
        local end_time
        end_time=$(date +%s%3N)
        local execution_time=$((end_time - start_time))

        print_success "Migration $migration_id applied successfully in ${execution_time}ms"
        return 0
    else
        print_error "Migration $migration_id failed"
        return 1
    fi
}

# Rollback single migration
rollback_migration() {
    local migration_id="$1"
    local rollback_file="${SCRIPT_DIR}/${migration_id}_rollback.sql"

    print_header "Rolling Back Migration: $migration_id"

    # Check if migration tracking exists (skip for 000)
    if [ "$migration_id" != "000_migration_schema" ]; then
        local applied
        applied=$(is_migration_applied "$migration_id")

        if [ "$applied" = "0" ]; then
            print_warning "Migration $migration_id not applied, skipping rollback"
            return 0
        fi
    fi

    # Check if rollback file exists
    if [ ! -f "$rollback_file" ]; then
        print_warning "No rollback file for $migration_id, skipping"
        return 0
    fi

    # Execute rollback
    if execute_sql_file "$rollback_file" "Rollback $migration_id"; then
        print_success "Migration $migration_id rolled back successfully"
        return 0
    else
        print_error "Rollback $migration_id failed"
        return 1
    fi
}

# Apply all pending migrations
apply_all_migrations() {
    print_header "Vietnamese PT Database Migrations"

    local failed=0

    for migration_id in "${MIGRATIONS[@]}"; do
        if ! apply_migration "$migration_id"; then
            failed=1
            print_error "Migration failed, stopping"
            break
        fi
        echo ""
    done

    if [ $failed -eq 0 ]; then
        print_success "All migrations applied successfully!"
        show_migration_status
        return 0
    else
        print_error "Migration process failed"
        return 1
    fi
}

# Show migration status
show_migration_status() {
    print_header "Migration Status"

    # Check if migration table exists
    local table_exists
    table_exists=$(execute_sql_query "SHOW TABLES LIKE 'vietnamese_pt_migrations'" 2>/dev/null | grep -c "vietnamese_pt_migrations" || echo "0")

    if [ "$table_exists" = "0" ]; then
        print_warning "Migration tracking not initialized (run migration 000 first)"
        echo ""
        echo "Available migrations:"
        for migration_id in "${MIGRATIONS[@]}"; do
            echo "  - $migration_id"
        done
        return
    fi

    # Show migration history
    execute_sql_query "
        SELECT
            migration_id,
            status,
            applied_at,
            CONCAT(ROUND(execution_time_ms / 1000, 2), 's') as execution_time
        FROM vietnamese_pt_migrations
        ORDER BY applied_at DESC
    " 2>/dev/null || print_error "Failed to retrieve migration status"

    echo ""

    # Show pending migrations
    echo "Pending migrations:"
    local has_pending=0
    for migration_id in "${MIGRATIONS[@]}"; do
        local applied
        applied=$(is_migration_applied "$migration_id")
        if [ "$applied" = "0" ]; then
            echo "  - $migration_id"
            has_pending=1
        fi
    done

    if [ $has_pending -eq 0 ]; then
        print_success "All migrations applied"
    fi
}

# Create database backup
create_backup() {
    local backup_dir="${SCRIPT_DIR}/../../backups"
    local backup_file="openemr_pt_backup_$(date +%Y%m%d_%H%M%S).sql"

    print_header "Creating Database Backup"

    mkdir -p "$backup_dir"

    print_info "Backing up PT tables to: $backup_file"

    if [ -z "$DB_PASS" ]; then
        mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p "$DB_NAME" \
            pt_assessments_bilingual \
            pt_exercise_prescriptions \
            pt_outcome_measures \
            pt_treatment_plans \
            pt_treatment_sessions \
            pt_assessment_templates \
            vietnamese_medical_terms \
            vietnamese_insurance_info \
            vietnamese_test \
            > "${backup_dir}/${backup_file}"
    else
        mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
            pt_assessments_bilingual \
            pt_exercise_prescriptions \
            pt_outcome_measures \
            pt_treatment_plans \
            pt_treatment_sessions \
            pt_assessment_templates \
            vietnamese_medical_terms \
            vietnamese_insurance_info \
            vietnamese_test \
            > "${backup_dir}/${backup_file}"
    fi

    if [ $? -eq 0 ]; then
        print_success "Backup created: ${backup_dir}/${backup_file}"
        return 0
    else
        print_error "Backup failed"
        return 1
    fi
}

################################################################################
# Main Script
################################################################################

show_help() {
    cat <<EOF
Vietnamese PT Migration Script

Usage:
    $0                      Apply all pending migrations
    $0 [migration_id]       Apply specific migration
    $0 rollback [id]        Rollback specific migration
    $0 status               Show migration status
    $0 backup               Create database backup
    $0 help                 Show this help

Environment Variables:
    DB_NAME     Database name (default: openemr)
    DB_USER     Database user (default: openemr)
    DB_PASS     Database password (will prompt if not set)
    DB_HOST     Database host (default: localhost)
    DB_PORT     Database port (default: 3306)

Examples:
    # Apply all migrations
    ./apply_migrations.sh

    # Apply specific migration
    ./apply_migrations.sh 001_add_indexes

    # Rollback migration
    ./apply_migrations.sh rollback 001_add_indexes

    # Check status
    ./apply_migrations.sh status

    # Create backup before migration
    ./apply_migrations.sh backup
    ./apply_migrations.sh

Available Migrations:
EOF
    for migration_id in "${MIGRATIONS[@]}"; do
        echo "    - $migration_id"
    done
}

# Main execution
main() {
    local command="${1:-apply_all}"

    case "$command" in
        help|-h|--help)
            show_help
            ;;
        status)
            show_migration_status
            ;;
        backup)
            create_backup
            ;;
        rollback)
            if [ -z "$2" ]; then
                print_error "Please specify migration ID to rollback"
                echo "Usage: $0 rollback [migration_id]"
                exit 1
            fi
            rollback_migration "$2"
            ;;
        apply_all)
            # Offer to create backup
            read -p "Create backup before applying migrations? (y/N) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                create_backup || exit 1
                echo ""
            fi

            apply_all_migrations
            ;;
        *)
            # Assume it's a migration ID
            apply_migration "$command"
            ;;
    esac
}

# Run main with all arguments
main "$@"
