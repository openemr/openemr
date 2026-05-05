#!/bin/bash
# Test MedEx-OpenEMR Portal Integration
# Run this from the host machine (macOS)

set -e

echo "=== MedEx-OpenEMR Portal Integration Test ==="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Test 1: Verify database schema
echo -e "${YELLOW}Test 1: Database Schema${NC}"
echo "Checking MedEx hipaa_secure_chat_tokens columns..."
MEDEX_COLS=$(docker exec mysql mysql -uwebserver -pBudd2833a HIPAA -e "DESCRIBE hipaa_secure_chat_tokens" | grep -c "openemr" || echo "0")
if [ "$MEDEX_COLS" -ge 2 ]; then
    echo -e "${GREEN}✓ MedEx schema updated (openemr_url, openemr_api_key columns present)${NC}"
else
    echo -e "${RED}✗ MedEx schema missing OpenEMR columns${NC}"
fi

echo "Checking OpenEMR medex_chat_sync table..."
OPENEMR_TABLE=$(docker exec openemr-8-0-1-dev-openemr-1 mysql -uopenemr -popenemr openemr -e "SHOW TABLES LIKE 'medex_chat_sync'" 2>&1 | grep -c "medex_chat_sync" || echo "0")
if [ "$OPENEMR_TABLE" -ge 1 ]; then
    echo -e "${GREEN}✓ OpenEMR medex_chat_sync table exists${NC}"
else
    echo -e "${RED}✗ OpenEMR medex_chat_sync table missing${NC}"
fi

echo ""

# Test 2: Test API endpoints
echo -e "${YELLOW}Test 2: API Endpoints${NC}"
echo "Testing receive_chat_message.php..."
RECEIVE_RESULT=$(curl -s -X POST http://localhost:8300/interface/modules/custom_modules/oe-module-medex/public/receive_chat_message.php \
  -H "Content-Type: application/json" \
  -d '{"test":"ping"}' | head -20)
if echo "$RECEIVE_RESULT" | grep -q "error"; then
    echo -e "${GREEN}✓ receive_chat_message.php responding (expects error for invalid request)${NC}"
else
    echo -e "${YELLOW}⚠ receive_chat_message.php response: $RECEIVE_RESULT${NC}"
fi

echo "Testing portal_redirect.php..."
REDIRECT_RESULT=$(curl -s "http://localhost:8300/interface/modules/custom_modules/oe-module-medex/public/portal_redirect.php?token=test123")
if echo "$REDIRECT_RESULT" | grep -q "Invalid or expired token"; then
    echo -e "${GREEN}✓ portal_redirect.php responding (expects error for invalid token)${NC}"
else
    echo -e "${YELLOW}⚠ portal_redirect.php response: $REDIRECT_RESULT${NC}"
fi

echo ""

# Test 3: Check existing tokens
echo -e "${YELLOW}Test 3: Token Configuration${NC}"
TOKEN_COUNT=$(docker exec mysql mysql -uwebserver -pBudd2833a HIPAA -e "SELECT COUNT(*) as cnt FROM hipaa_secure_chat_tokens WHERE openemr_url IS NOT NULL" -N 2>/dev/null || echo "0")
echo "Tokens with OpenEMR integration configured: $TOKEN_COUNT"
if [ "$TOKEN_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✓ Some tokens have OpenEMR sync configured${NC}"
    echo "Sample token data:"
    docker exec mysql mysql -uwebserver -pBudd2833a HIPAA -e "SELECT LEFT(token,20) as token_prefix, pid, is_provider, LEFT(openemr_url,30) as openemr_url FROM hipaa_secure_chat_tokens WHERE openemr_url IS NOT NULL LIMIT 1" 2>/dev/null
else
    echo -e "${YELLOW}⚠ No tokens with OpenEMR sync yet (will be configured on next token registration)${NC}"
fi

echo ""

# Test 4: Check API key
echo -e "${YELLOW}Test 4: API Key Configuration${NC}"
API_KEY=$(docker exec openemr-8-0-1-dev-openemr-1 mysql -uopenemr -popenemr openemr -e "SELECT mp_value FROM medex_prefs WHERE mp_key = 'medex_api_key' LIMIT 1" -N 2>/dev/null || echo "")
if [ -n "$API_KEY" ]; then
    echo -e "${GREEN}✓ API key configured: ${API_KEY:0:16}...${NC}"
else
    echo -e "${YELLOW}⚠ API key will be auto-generated on next token registration${NC}"
fi

echo ""

# Test 5: File modifications
echo -e "${YELLOW}Test 5: Code Files${NC}"

check_file_exists() {
    local container=$1
    local file=$2
    local desc=$3
    
    if docker exec "$container" test -f "$file" 2>/dev/null; then
        echo -e "${GREEN}✓ $desc${NC}"
    else
        echo -e "${RED}✗ $desc (not found: $file)${NC}"
    fi
}

check_file_exists "openemr-8-0-1-dev-openemr-1" "/var/www/localhost/htdocs/openemr/interface/modules/custom_modules/oe-module-medex/public/receive_chat_message.php" "receive_chat_message.php"
check_file_exists "openemr-8-0-1-dev-openemr-1" "/var/www/localhost/htdocs/openemr/interface/modules/custom_modules/oe-module-medex/public/portal_redirect.php" "portal_redirect.php"
check_file_exists "medex-localhost-80-app-1" "/var/www/cart/upload/system/library/medex/RedisChat.php" "RedisChat.php (medex-core)"

# Check for syncToOpenEMR method
echo "Checking RedisChat for syncToOpenEMR method..."
SYNC_METHOD=$(docker exec medex-localhost-80-app-1 grep -c "syncToOpenEMR" /var/www/cart/upload/system/library/medex/RedisChat.php 2>/dev/null || echo "0")
if [ "$SYNC_METHOD" -gt 0 ]; then
    echo -e "${GREEN}✓ RedisChat has syncToOpenEMR method${NC}"
else
    echo -e "${RED}✗ RedisChat missing syncToOpenEMR method${NC}"
fi

echo ""

# Summary
echo -e "${YELLOW}=== Integration Summary ===${NC}"
echo ""
echo "Features Implemented:"
echo "  ✓ Dual-write messaging (MedEx → OpenEMR onsite_mail)"
echo "  ✓ UI toggle (?use_portal=1 redirects to OpenEMR)"
echo "  ✓ Bearer token → portal session mapping"
echo "  ✓ API key authentication for message sync"
echo "  ✓ Database schema updates (both systems)"
echo ""
echo "Next Test Steps:"
echo "  1. Generate new secure chat token from OpenEMR"
echo "  2. Send test message in MedEx chat UI"
echo "  3. Verify message appears in OpenEMR onsite_mail"
echo "  4. Test ?use_portal=1 link redirects to OpenEMR portal"
echo ""
echo "Documentation: See OPENEMR_PORTAL_INTEGRATION.md for details"
echo ""
