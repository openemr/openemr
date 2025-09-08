#!/bin/bash

# OpenEMR Hybrid Development Environment - Start Script
# Starts Docker services for hybrid development (services only, no OpenEMR container)
# Author: Dang Tran <tqvdang@msn.com>

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Starting OpenEMR Hybrid Development Environment${NC}"
echo -e "${BLUE}================================================${NC}"

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo -e "${RED}❌ .env file not found. Please create it from .env.example${NC}"
    exit 1
fi

# Load environment variables
set -a
source .env
set +a

# Create necessary directories
echo -e "${YELLOW}📁 Creating necessary directories...${NC}"
mkdir -p data/{mariadb,redis,mailhog}
mkdir -p logs/mariadb
mkdir -p backups

# Start Docker services
echo -e "${YELLOW}🐳 Starting Docker services...${NC}"

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker is not running. Please start Docker first.${NC}"
    exit 1
fi

# Start the services
docker-compose up -d --remove-orphans

# Wait for services to be healthy
echo -e "${YELLOW}⏳ Waiting for services to be ready...${NC}"

# Check MariaDB health
echo -e "${YELLOW}📊 Checking MariaDB health...${NC}"
timeout=60
counter=0
while [ $counter -lt $timeout ]; do
    if docker-compose exec -T mariadb mysql -u${DB_USER} -p${DB_PASSWORD} -e "SELECT 1" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ MariaDB is ready${NC}"
        break
    fi
    echo -e "${YELLOW}   Waiting for MariaDB... ($((counter+1))/${timeout})${NC}"
    sleep 2
    counter=$((counter+1))
done

if [ $counter -eq $timeout ]; then
    echo -e "${RED}❌ MariaDB failed to start within $timeout seconds${NC}"
    echo -e "${RED}   Check logs: docker-compose logs mariadb${NC}"
    exit 1
fi

# Check Redis health
echo -e "${YELLOW}🔴 Checking Redis health...${NC}"
if docker-compose exec -T redis redis-cli -a ${REDIS_PASSWORD} ping > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Redis is ready${NC}"
else
    echo -e "${RED}❌ Redis health check failed${NC}"
    echo -e "${RED}   Check logs: docker-compose logs redis${NC}"
fi

# Verify database character set
echo -e "${YELLOW}🔤 Verifying Vietnamese UTF-8mb4 support...${NC}"
if docker-compose exec -T mariadb mysql -u${DB_USER} -p${DB_PASSWORD} -e "SHOW VARIABLES LIKE 'character_set_server'" | grep -q utf8mb4; then
    echo -e "${GREEN}✅ Database configured with UTF-8mb4 support${NC}"
else
    echo -e "${RED}❌ Database character set configuration issue${NC}"
fi

# Test Vietnamese characters
echo -e "${YELLOW}🇻🇳 Testing Vietnamese character support...${NC}"
if docker-compose exec -T mariadb mysql -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} -e "SELECT vietnamese_text FROM vietnamese_test LIMIT 1" > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Vietnamese character support verified${NC}"
else
    echo -e "${YELLOW}⚠️  Vietnamese test table not found (will be created on first OpenEMR setup)${NC}"
fi

# Display service information
echo -e "\n${GREEN}🎉 Services started successfully!${NC}"
echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}📋 Service Information:${NC}"
echo -e "${BLUE}================================================${NC}"
echo -e "🗄️  MariaDB:      localhost:${DB_PORT}"
echo -e "   Database:     ${DB_NAME}"
echo -e "   Username:     ${DB_USER}"
echo -e "   Password:     ${DB_PASSWORD}"
echo -e ""
echo -e "🔴 Redis:         localhost:${REDIS_PORT}"
echo -e "   Password:     ${REDIS_PASSWORD}"
echo -e ""
echo -e "📊 phpMyAdmin:    http://localhost:${PMA_PORT}"
echo -e "🔍 Adminer:       http://localhost:${ADMINER_PORT:-8082}"
echo -e "📧 MailHog:       http://localhost:${MAILHOG_HTTP_PORT:-8025}"
echo -e "   SMTP:         localhost:${MAILHOG_SMTP_PORT:-1025}"
echo -e ""
echo -e "${YELLOW}💡 Next Steps:${NC}"
echo -e "1. Configure your local PHP to connect to these services"
echo -e "2. Start your local web server (Apache/Nginx)"
echo -e "3. Access OpenEMR through your local web server"
echo -e "4. Use phpMyAdmin or Adminer for database management"
echo -e ""
echo -e "${YELLOW}🔧 Useful Commands:${NC}"
echo -e "   Stop services:    ./scripts/stop-dev.sh"
echo -e "   View logs:        docker-compose logs -f [service]"
echo -e "   Backup database:  ./scripts/backup-db.sh"
echo -e "   Restore database: ./scripts/restore-db.sh [backup-file]"
echo -e ""
echo -e "${GREEN}🏥 Ready for OpenEMR hybrid development!${NC}"