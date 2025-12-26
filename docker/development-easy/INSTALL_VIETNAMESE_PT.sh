#!/bin/bash

# Vietnamese Physiotherapy Module - Database Installation Script
# For development-easy environment
# Run this from: /home/dang/dev/openemr/docker/development-easy

set -e  # Exit on error

echo "=================================================="
echo "Vietnamese PT Module - Database Installation"
echo "=================================================="
echo ""

# Configuration
MYSQL_CONTAINER="development-easy-mysql-1"
MYSQL_USER="openemr"
MYSQL_PASS="openemr"
MYSQL_DB="openemr"
SQL_DIR="../development-physiotherapy"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to check if container is running
check_container() {
    if ! docker ps --format '{{.Names}}' | grep -q "^${MYSQL_CONTAINER}$"; then
        echo -e "${RED}Error: MySQL container '${MYSQL_CONTAINER}' is not running${NC}"
        echo "Please start the development-easy environment first:"
        echo "  cd /home/dang/dev/openemr/docker/development-easy"
        echo "  docker compose up -d"
        exit 1
    fi
    echo -e "${GREEN}✓ MySQL container is running${NC}"
}

# Function to execute SQL file
execute_sql() {
    local sql_file=$1
    local description=$2

    echo ""
    echo "-----------------------------------"
    echo "Installing: $description"
    echo "File: $sql_file"
    echo "-----------------------------------"

    if [ ! -f "$sql_file" ]; then
        echo -e "${RED}Error: SQL file not found: $sql_file${NC}"
        return 1
    fi

    # Execute SQL
    if docker exec -i $MYSQL_CONTAINER mariadb -u $MYSQL_USER -p$MYSQL_PASS $MYSQL_DB < "$sql_file" 2>&1; then
        echo -e "${GREEN}✓ Successfully installed: $description${NC}"
        return 0
    else
        echo -e "${RED}✗ Failed to install: $description${NC}"
        return 1
    fi
}

# Function to verify installation
verify_installation() {
    echo ""
    echo "=================================================="
    echo "Verifying Installation"
    echo "=================================================="

    # Check Vietnamese test table
    echo ""
    echo "1. Checking Vietnamese test table..."
    if docker exec $MYSQL_CONTAINER mariadb -u $MYSQL_USER -p$MYSQL_PASS -D $MYSQL_DB \
        -e "SELECT COUNT(*) as count FROM vietnamese_test" 2>/dev/null | grep -q "[0-9]"; then
        echo -e "${GREEN}✓ Vietnamese test table exists${NC}"
    else
        echo -e "${RED}✗ Vietnamese test table not found${NC}"
    fi

    # Check medical terms
    echo ""
    echo "2. Checking medical terms..."
    TERM_COUNT=$(docker exec $MYSQL_CONTAINER mariadb -u $MYSQL_USER -p$MYSQL_PASS -D $MYSQL_DB \
        -e "SELECT COUNT(*) as count FROM vietnamese_medical_terms" 2>/dev/null | tail -1 | tr -d '\r')

    if [ -n "$TERM_COUNT" ] && [ "$TERM_COUNT" -gt "0" ]; then
        echo -e "${GREEN}✓ Found $TERM_COUNT medical terms${NC}"
    else
        echo -e "${RED}✗ No medical terms found${NC}"
    fi

    # Check PT tables
    echo ""
    echo "3. Checking PT tables..."
    PT_TABLES=$(docker exec $MYSQL_CONTAINER mariadb -u $MYSQL_USER -p$MYSQL_PASS -D $MYSQL_DB \
        -e "SHOW TABLES LIKE 'pt_%_bilingual'" 2>/dev/null | grep "pt_" | wc -l)

    if [ "$PT_TABLES" -gt "0" ]; then
        echo -e "${GREEN}✓ Found $PT_TABLES PT bilingual tables${NC}"
        docker exec $MYSQL_CONTAINER mariadb -u $MYSQL_USER -p$MYSQL_PASS -D $MYSQL_DB \
            -e "SHOW TABLES LIKE 'pt_%_bilingual'" 2>/dev/null | grep "pt_"
    else
        echo -e "${RED}✗ No PT tables found${NC}"
    fi

    # Check form registrations
    echo ""
    echo "4. Checking form registrations..."
    FORM_COUNT=$(docker exec $MYSQL_CONTAINER mariadb -u $MYSQL_USER -p$MYSQL_PASS -D $MYSQL_DB \
        -e "SELECT COUNT(*) as count FROM registry WHERE directory LIKE 'vietnamese_pt_%'" 2>/dev/null | tail -1 | tr -d '\r')

    if [ -n "$FORM_COUNT" ] && [ "$FORM_COUNT" -gt "0" ]; then
        echo -e "${GREEN}✓ Found $FORM_COUNT registered forms${NC}"
        docker exec $MYSQL_CONTAINER mariadb -u $MYSQL_USER -p$MYSQL_PASS -D $MYSQL_DB \
            -e "SELECT name, directory FROM registry WHERE directory LIKE 'vietnamese_pt_%'" 2>/dev/null
    else
        echo -e "${RED}✗ No forms registered${NC}"
    fi
}

# Function to test API endpoint
test_api() {
    echo ""
    echo "=================================================="
    echo "Testing API Endpoint"
    echo "=================================================="
    echo ""
    echo "Testing: http://localhost:8300/apis/default/vietnamese-pt/medical-terms"
    echo ""

    # Try to access API (may return 401 without auth, which is OK)
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8300/apis/default/vietnamese-pt/medical-terms" 2>/dev/null)

    if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "401" ]; then
        echo -e "${GREEN}✓ API endpoint is accessible (HTTP $HTTP_CODE)${NC}"
        echo "Note: HTTP 401 is expected without authentication"
    elif [ "$HTTP_CODE" == "500" ]; then
        echo -e "${RED}✗ API endpoint returns HTTP 500 (database may still have issues)${NC}"
    else
        echo -e "${YELLOW}⚠ API endpoint returned HTTP $HTTP_CODE${NC}"
    fi
}

# Main installation process
main() {
    echo ""
    echo "Starting Vietnamese PT Module installation..."
    echo ""

    # Check prerequisites
    check_container

    # Backup warning
    echo ""
    echo -e "${YELLOW}WARNING: This will modify the OpenEMR database${NC}"
    echo "It is recommended to backup the database first."
    echo ""
    read -p "Continue with installation? (y/N) " -n 1 -r
    echo ""

    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Installation cancelled."
        exit 0
    fi

    # Install Vietnamese setup
    execute_sql \
        "$SQL_DIR/configs/mariadb/init/00-vietnamese-setup.sql" \
        "Vietnamese character support and test table"

    # Install medical terminology
    execute_sql \
        "$SQL_DIR/configs/mariadb/init/01-vietnamese-medical-terminology.sql" \
        "Vietnamese medical terminology (52+ terms)"

    # Install routes and ACL
    execute_sql \
        "$SQL_DIR/sql/vietnamese_pt_routes_and_acl.sql" \
        "PT tables, form registration, and ACL configuration"

    # Verify installation
    verify_installation

    # Test API
    test_api

    # Final summary
    echo ""
    echo "=================================================="
    echo "Installation Complete!"
    echo "=================================================="
    echo ""
    echo "Next Steps:"
    echo ""
    echo "1. Login to OpenEMR:"
    echo "   URL: http://localhost:8300"
    echo "   Username: admin"
    echo "   Password: pass"
    echo ""
    echo "2. Navigate to Patient Finder and select a patient"
    echo ""
    echo "3. View the patient summary page"
    echo "   - You should see the 'Vietnamese Physiotherapy' widget"
    echo ""
    echo "4. Test adding forms:"
    echo "   - Go to Encounter → Forms"
    echo "   - Look for Vietnamese PT forms"
    echo "   - Add a new assessment, exercise, treatment plan, or outcome"
    echo ""
    echo "5. Verify API endpoints are working"
    echo ""
    echo -e "${GREEN}Installation completed successfully!${NC}"
    echo ""
}

# Run main installation
main
