#!/bin/bash
# Final verification script for Vietnamese PT installation

echo "=========================================="
echo "Vietnamese PT Module - Final Verification"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

PASS=0
FAIL=0

check() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ PASS${NC}: $1"
        ((PASS++))
    else
        echo -e "${RED}✗ FAIL${NC}: $1"
        ((FAIL++))
    fi
}

echo "1. Checking Docker Services..."
docker compose ps | grep -q "healthy"
check "Docker services running"

echo ""
echo "2. Checking Database Tables..."
TABLES=$(docker compose exec -T mysql mariadb -uroot -proot openemr -e "SHOW TABLES LIKE 'pt_%'" 2>/dev/null | grep -c "pt_")
if [ "$TABLES" -ge 7 ]; then
    echo -e "${GREEN}✓ PASS${NC}: Found $TABLES PT tables"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Found $TABLES PT tables (expected 7+)"
    ((FAIL++))
fi

VN_TABLES=$(docker compose exec -T mysql mariadb -uroot -proot openemr -e "SHOW TABLES LIKE 'vietnamese_%'" 2>/dev/null | grep -c "vietnamese_")
if [ "$VN_TABLES" -ge 3 ]; then
    echo -e "${GREEN}✓ PASS${NC}: Found $VN_TABLES Vietnamese tables"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Found $VN_TABLES Vietnamese tables (expected 3+)"
    ((FAIL++))
fi

echo ""
echo "3. Checking Sample Data..."
TERMS=$(docker compose exec -T mysql mariadb -uroot -proot openemr -e "SELECT COUNT(*) FROM vietnamese_medical_terms" 2>/dev/null | tail -1 | tr -d '\r')
if [ "$TERMS" -ge 40 ]; then
    echo -e "${GREEN}✓ PASS${NC}: Found $TERMS medical terms"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Found $TERMS medical terms (expected 40)"
    ((FAIL++))
fi

ASSESSMENTS=$(docker compose exec -T mysql mariadb -uroot -proot openemr -e "SELECT COUNT(*) FROM pt_assessments_bilingual" 2>/dev/null | tail -1 | tr -d '\r')
if [ "$ASSESSMENTS" -ge 5 ]; then
    echo -e "${GREEN}✓ PASS${NC}: Found $ASSESSMENTS PT assessments"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Found $ASSESSMENTS PT assessments (expected 5)"
    ((FAIL++))
fi

EXERCISES=$(docker compose exec -T mysql mariadb -uroot -proot openemr -e "SELECT COUNT(*) FROM pt_exercise_prescriptions" 2>/dev/null | tail -1 | tr -d '\r')
if [ "$EXERCISES" -ge 6 ]; then
    echo -e "${GREEN}✓ PASS${NC}: Found $EXERCISES exercise prescriptions"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Found $EXERCISES exercise prescriptions (expected 6)"
    ((FAIL++))
fi

echo ""
echo "4. Checking Form Registration..."
FORMS=$(docker compose exec -T mysql mariadb -uroot -proot openemr -e "SELECT COUNT(*) FROM registry WHERE directory LIKE 'vietnamese_pt%'" 2>/dev/null | tail -1 | tr -d '\r')
if [ "$FORMS" -ge 4 ]; then
    echo -e "${GREEN}✓ PASS${NC}: Found $FORMS registered forms"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Found $FORMS registered forms (expected 4)"
    ((FAIL++))
fi

echo ""
echo "5. Checking Code Files..."
SERVICES=$(find /home/dang/dev/openemr/src/Services/VietnamesePT/ -name "*.php" 2>/dev/null | wc -l)
if [ "$SERVICES" -ge 8 ]; then
    echo -e "${GREEN}✓ PASS${NC}: Found $SERVICES service files"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Found $SERVICES service files (expected 8)"
    ((FAIL++))
fi

CONTROLLERS=$(find /home/dang/dev/openemr/src/RestControllers/VietnamesePT/ -name "*.php" 2>/dev/null | wc -l)
if [ "$CONTROLLERS" -ge 8 ]; then
    echo -e "${GREEN}✓ PASS${NC}: Found $CONTROLLERS controller files"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Found $CONTROLLERS controller files (expected 8)"
    ((FAIL++))
fi

FORM_DIRS=$(find /home/dang/dev/openemr/interface/forms/ -type d -name "vietnamese_pt_*" 2>/dev/null | wc -l)
if [ "$FORM_DIRS" -ge 4 ]; then
    echo -e "${GREEN}✓ PASS${NC}: Found $FORM_DIRS form directories"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Found $FORM_DIRS form directories (expected 4)"
    ((FAIL++))
fi

if [ -f "/home/dang/dev/openemr/library/custom/vietnamese_pt_widget.php" ]; then
    echo -e "${GREEN}✓ PASS${NC}: Widget file exists"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Widget file not found"
    ((FAIL++))
fi

echo ""
echo "6. Checking Vietnamese Character Support..."
VN_TEXT=$(docker compose exec -T mysql mariadb -uroot -proot openemr -e "SELECT vietnamese_text FROM vietnamese_test LIMIT 1" 2>/dev/null | tail -1)
if echo "$VN_TEXT" | grep -q "Vật lý"; then
    echo -e "${GREEN}✓ PASS${NC}: Vietnamese characters supported"
    ((PASS++))
else
    echo -e "${YELLOW}⚠ WARNING${NC}: Vietnamese characters may not display correctly"
    ((FAIL++))
fi

echo ""
echo "7. Checking API Routes..."
ROUTES=$(grep -c "vietnamese-pt" /home/dang/dev/openemr/apis/routes/_rest_routes_standard.inc.php 2>/dev/null)
if [ "$ROUTES" -ge 40 ]; then
    echo -e "${GREEN}✓ PASS${NC}: Found $ROUTES API route references"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: Found $ROUTES API route references (expected 40+)"
    ((FAIL++))
fi

echo ""
echo "8. Checking OpenEMR Accessibility..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8300" 2>/dev/null)
if [ "$HTTP_CODE" == "200" ]; then
    echo -e "${GREEN}✓ PASS${NC}: OpenEMR accessible (HTTP $HTTP_CODE)"
    ((PASS++))
else
    echo -e "${RED}✗ FAIL${NC}: OpenEMR not accessible (HTTP $HTTP_CODE)"
    ((FAIL++))
fi

echo ""
echo "=========================================="
echo "FINAL RESULTS"
echo "=========================================="
echo ""
echo "Tests Passed: ${GREEN}$PASS${NC}"
echo "Tests Failed: ${RED}$FAIL${NC}"
TOTAL=$((PASS + FAIL))
PERCENT=$((PASS * 100 / TOTAL))
echo "Success Rate: $PERCENT%"
echo ""

if [ "$FAIL" -eq 0 ]; then
    echo -e "${GREEN}✓ ALL TESTS PASSED${NC}"
    echo ""
    echo "Installation Status: COMPLETE"
    echo "Ready for UI testing!"
    echo ""
    echo "Next Steps:"
    echo "1. Login to http://localhost:8300 (admin/pass)"
    echo "2. Navigate to patient summary"
    echo "3. Look for Vietnamese Physiotherapy widget"
    echo "4. Test forms and Vietnamese text entry"
else
    echo -e "${YELLOW}⚠ SOME TESTS FAILED${NC}"
    echo ""
    echo "Installation Status: INCOMPLETE"
    echo "Please review failed tests above."
fi

echo ""
echo "Detailed Documentation:"
echo "  - Quick Start: /home/dang/dev/openemr/docker/development-easy/QUICK_START.md"
echo "  - Full Report: /home/dang/dev/openemr/docker/development-easy/FINAL_INSTALLATION_SUMMARY.md"
echo ""
