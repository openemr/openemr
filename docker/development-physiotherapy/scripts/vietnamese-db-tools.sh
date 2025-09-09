#!/bin/bash

# Vietnamese Database Management Tools
# Enhanced database operations for Vietnamese physiotherapy data
# Author: Dang Tran <tqvdang@msn.com>

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DOCKER_DIR="$(dirname "$SCRIPT_DIR")"
PROJECT_NAME="openemr-physiotherapy"

# Database connection settings
DB_HOST="localhost"
DB_PORT="3306"
DB_NAME="openemr"
DB_USER="openemr"
DB_PASSWORD="openemr123!"
DB_ROOT_PASSWORD="rootpass123!"

# Helper functions
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

check_prerequisites() {
    log_info "Checking prerequisites..."
    
    if ! command -v docker-compose &> /dev/null; then
        log_error "docker-compose is not installed or not in PATH"
        exit 1
    fi
    
    if ! docker-compose -f "$DOCKER_DIR/docker-compose.yml" ps mariadb | grep -q "Up"; then
        log_error "MariaDB container is not running. Please start with: ./scripts/start-dev.sh"
        exit 1
    fi
    
    log_success "Prerequisites check passed"
}

# Test Vietnamese character support
test_vietnamese_support() {
    log_info "Testing Vietnamese character support..."
    
    local test_query="SELECT vietnamese_text FROM vietnamese_test WHERE vietnamese_text LIKE '%Vật lý trị liệu%' LIMIT 1;"
    
    if docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "$test_query" &> /dev/null; then
        log_success "Vietnamese character support is working correctly"
        
        # Display sample Vietnamese data
        log_info "Sample Vietnamese physiotherapy terms:"
        docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
            SELECT english_term as 'English', vietnamese_term as 'Vietnamese', category as 'Category' 
            FROM vietnamese_medical_terms 
            WHERE category = 'general' 
            LIMIT 5;" --table
    else
        log_error "Vietnamese character support test failed"
        exit 1
    fi
}

# Check database schema integrity
check_schema_integrity() {
    log_info "Checking database schema integrity..."
    
    local tables=(
        "vietnamese_medical_terms"
        "pt_assessments_bilingual"
        "vietnamese_insurance_info"
        "pt_exercise_prescriptions"
        "pt_outcome_measures"
        "pt_treatment_sessions"
    )
    
    for table in "${tables[@]}"; do
        local count=$(docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "SELECT COUNT(*) FROM $table;" -s -N 2>/dev/null || echo "0")
        
        if [ "$count" -gt 0 ]; then
            log_success "Table $table: $count records"
        else
            log_warning "Table $table: No records found"
        fi
    done
}

# Vietnamese data search function
search_vietnamese_terms() {
    local search_term="$1"
    local language="${2:-both}"
    
    log_info "Searching for Vietnamese medical terms: '$search_term' (language: $language)"
    
    local query=""
    case $language in
        "vi"|"vietnamese")
            query="SELECT english_term as 'English', vietnamese_term as 'Tiếng Việt', category as 'Loại' 
                   FROM vietnamese_medical_terms 
                   WHERE vietnamese_term LIKE '%$search_term%' 
                      OR synonyms_vi LIKE '%$search_term%'
                   ORDER BY vietnamese_term;"
            ;;
        "en"|"english")
            query="SELECT english_term as 'English', vietnamese_term as 'Tiếng Việt', category as 'Category' 
                   FROM vietnamese_medical_terms 
                   WHERE english_term LIKE '%$search_term%' 
                      OR synonyms_en LIKE '%$search_term%'
                   ORDER BY english_term;"
            ;;
        *)
            query="SELECT english_term as 'English', vietnamese_term as 'Tiếng Việt', category as 'Category' 
                   FROM vietnamese_medical_terms 
                   WHERE english_term LIKE '%$search_term%' 
                      OR vietnamese_term LIKE '%$search_term%'
                      OR synonyms_en LIKE '%$search_term%'
                      OR synonyms_vi LIKE '%$search_term%'
                   ORDER BY category, english_term;"
            ;;
    esac
    
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "$query" --table
}

# Patient bilingual data summary
show_patient_summary() {
    local patient_id="${1:-1}"
    
    log_info "Showing bilingual patient summary for patient ID: $patient_id"
    
    # Basic patient info
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
        SELECT 
            pa.patient_id as 'Patient ID',
            pa.assessment_date as 'Assessment Date',
            pa.chief_complaint_en as 'Chief Complaint (EN)',
            pa.chief_complaint_vi as 'Chief Complaint (VI)',
            pa.pain_level as 'Pain Level',
            pa.language_preference as 'Preferred Language',
            pa.status as 'Status'
        FROM pt_assessments_bilingual pa 
        WHERE pa.patient_id = $patient_id 
        ORDER BY pa.assessment_date DESC 
        LIMIT 1;" --table
    
    # Insurance info
    log_info "Insurance information:"
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
        SELECT 
            bhyt_card_number as 'BHYT Card',
            insurance_provider as 'Provider',
            coverage_type as 'Coverage Type',
            CONCAT(coverage_percentage, '%') as 'Coverage %',
            valid_from as 'Valid From',
            valid_to as 'Valid To'
        FROM vietnamese_insurance_info 
        WHERE patient_id = $patient_id AND is_active = 1;" --table
    
    # Active exercises
    log_info "Active exercises:"
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
        SELECT 
            exercise_name_en as 'Exercise (EN)',
            exercise_name_vi as 'Exercise (VI)',
            exercise_category as 'Category',
            CONCAT(sets_prescribed, ' x ', reps_prescribed) as 'Sets x Reps',
            frequency_per_day as 'Per Day',
            difficulty_level as 'Level',
            patient_compliance as 'Compliance'
        FROM pt_exercise_prescriptions 
        WHERE patient_id = $patient_id AND status = 'active'
        ORDER BY prescribed_date DESC;" --table
}

# Database performance analysis
analyze_performance() {
    log_info "Analyzing database performance for Vietnamese data..."
    
    # Check character set and collation
    log_info "Character set and collation settings:"
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
        SELECT 
            TABLE_NAME as 'Table',
            TABLE_COLLATION as 'Collation',
            TABLE_ROWS as 'Rows',
            ROUND(DATA_LENGTH/1024/1024,2) as 'Data MB',
            ROUND(INDEX_LENGTH/1024/1024,2) as 'Index MB'
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = '$DB_NAME' 
        AND (TABLE_NAME LIKE 'pt_%' OR TABLE_NAME LIKE 'vietnamese_%')
        ORDER BY DATA_LENGTH DESC;" --table
    
    # Check full-text indexes
    log_info "Full-text search indexes:"
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
        SHOW INDEX FROM vietnamese_medical_terms WHERE Index_type = 'FULLTEXT';" --table
}

# Export Vietnamese physiotherapy data
export_vietnamese_data() {
    local output_file="${1:-vietnamese_pt_data_$(date +%Y%m%d_%H%M%S).sql}"
    
    log_info "Exporting Vietnamese physiotherapy data to: $output_file"
    
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysqldump \
        -u"$DB_USER" -p"$DB_PASSWORD" \
        --default-character-set=utf8mb4 \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --hex-blob \
        "$DB_NAME" \
        vietnamese_medical_terms \
        pt_assessments_bilingual \
        vietnamese_insurance_info \
        pt_exercise_prescriptions \
        pt_outcome_measures \
        pt_treatment_sessions > "$DOCKER_DIR/backups/$output_file"
    
    log_success "Vietnamese physiotherapy data exported to: backups/$output_file"
}

# Validate data integrity
validate_data_integrity() {
    log_info "Validating Vietnamese data integrity..."
    
    # Check for orphaned records
    log_info "Checking for orphaned exercise prescriptions..."
    local orphaned_exercises=$(docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
        SELECT COUNT(*) 
        FROM pt_exercise_prescriptions pep 
        LEFT JOIN pt_assessments_bilingual pab ON pep.assessment_id = pab.id 
        WHERE pab.id IS NULL;" -s -N)
    
    if [ "$orphaned_exercises" -gt 0 ]; then
        log_warning "Found $orphaned_exercises orphaned exercise prescriptions"
    else
        log_success "No orphaned exercise prescriptions found"
    fi
    
    # Check Vietnamese character encoding
    log_info "Validating Vietnamese character encoding..."
    local encoding_issues=$(docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
        SELECT COUNT(*) 
        FROM vietnamese_medical_terms 
        WHERE vietnamese_term REGEXP '[^\x00-\x7F\u00A0-\uFFFF]';" -s -N)
    
    if [ "$encoding_issues" -gt 0 ]; then
        log_warning "Found $encoding_issues potential character encoding issues"
    else
        log_success "Vietnamese character encoding validation passed"
    fi
}

# Main menu function
show_menu() {
    echo ""
    echo -e "${BLUE}=== Vietnamese Physiotherapy Database Tools ===${NC}"
    echo "1) Test Vietnamese character support"
    echo "2) Check database schema integrity"
    echo "3) Search Vietnamese medical terms"
    echo "4) Show patient bilingual summary"
    echo "5) Analyze database performance"
    echo "6) Export Vietnamese physiotherapy data"
    echo "7) Validate data integrity"
    echo "8) Exit"
    echo ""
}

# Main execution
main() {
    case "${1:-menu}" in
        "test-vietnamese"|"1")
            check_prerequisites
            test_vietnamese_support
            ;;
        "check-schema"|"2")
            check_prerequisites
            check_schema_integrity
            ;;
        "search"|"3")
            check_prerequisites
            local term="${2:-"đau"}"
            local lang="${3:-"both"}"
            search_vietnamese_terms "$term" "$lang"
            ;;
        "patient-summary"|"4")
            check_prerequisites
            local patient_id="${2:-1}"
            show_patient_summary "$patient_id"
            ;;
        "analyze"|"5")
            check_prerequisites
            analyze_performance
            ;;
        "export"|"6")
            check_prerequisites
            local filename="${2:-}"
            export_vietnamese_data "$filename"
            ;;
        "validate"|"7")
            check_prerequisites
            validate_data_integrity
            ;;
        "menu"|*)
            check_prerequisites
            while true; do
                show_menu
                read -p "Choose an option (1-8): " choice
                case $choice in
                    1) test_vietnamese_support ;;
                    2) check_schema_integrity ;;
                    3) 
                        read -p "Enter search term: " term
                        read -p "Language (vi/en/both): " lang
                        search_vietnamese_terms "$term" "$lang"
                        ;;
                    4) 
                        read -p "Enter patient ID (default: 1): " pid
                        show_patient_summary "${pid:-1}"
                        ;;
                    5) analyze_performance ;;
                    6) 
                        read -p "Output filename (optional): " fname
                        export_vietnamese_data "$fname"
                        ;;
                    7) validate_data_integrity ;;
                    8) 
                        log_info "Exiting Vietnamese Database Tools"
                        exit 0
                        ;;
                    *) 
                        log_error "Invalid option. Please choose 1-8."
                        ;;
                esac
                echo ""
                read -p "Press Enter to continue..."
            done
            ;;
    esac
}

# Run main function with all arguments
main "$@"