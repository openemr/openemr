#!/bin/bash

# OpenEMR Hybrid Development Environment - Stop Script
# Stops Docker services for hybrid development
# Author: Dang Tran <tqvdang@msn.com>

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🛑 Stopping OpenEMR Hybrid Development Environment${NC}"
echo -e "${BLUE}==================================================${NC}"

# Check if docker-compose.yml exists
if [ ! -f "docker-compose.yml" ]; then
    echo -e "${RED}❌ docker-compose.yml not found. Are you in the correct directory?${NC}"
    exit 1
fi

# Load environment variables if .env exists
if [ -f ".env" ]; then
    set -a
    source .env
    set +a
fi

# Ask user for confirmation and backup option
echo -e "${YELLOW}🤔 How would you like to stop the services?${NC}"
echo -e "1. Stop services (keep data)"
echo -e "2. Stop and remove containers (keep data)"
echo -e "3. Stop, remove containers and networks (keep data)"
echo -e "4. Full cleanup (⚠️  REMOVES ALL DATA)"
echo -e "5. Cancel"

read -p "Choose an option [1-5]: " choice

case $choice in
    1)
        echo -e "${YELLOW}🛑 Stopping services...${NC}"
        docker-compose stop
        echo -e "${GREEN}✅ Services stopped. Data preserved.${NC}"
        ;;
    2)
        echo -e "${YELLOW}🛑 Stopping and removing containers...${NC}"
        docker-compose down
        echo -e "${GREEN}✅ Containers removed. Data and networks preserved.${NC}"
        ;;
    3)
        echo -e "${YELLOW}🛑 Stopping and removing containers and networks...${NC}"
        docker-compose down --remove-orphans
        echo -e "${GREEN}✅ Containers and networks removed. Data preserved.${NC}"
        ;;
    4)
        echo -e "${RED}⚠️  WARNING: This will remove ALL data including database content!${NC}"
        read -p "Are you absolutely sure? Type 'YES' to confirm: " confirm
        if [ "$confirm" = "YES" ]; then
            echo -e "${YELLOW}🧹 Performing full cleanup...${NC}"
            
            # Offer to backup before cleanup
            read -p "Do you want to create a backup before cleanup? (y/n): " backup_choice
            if [ "$backup_choice" = "y" ] || [ "$backup_choice" = "Y" ]; then
                echo -e "${YELLOW}💾 Creating backup...${NC}"
                if [ -f "./scripts/backup-db.sh" ]; then
                    ./scripts/backup-db.sh
                else
                    echo -e "${YELLOW}⚠️  Backup script not found, continuing with cleanup...${NC}"
                fi
            fi
            
            # Stop and remove everything
            docker-compose down --volumes --remove-orphans
            
            # Remove local data directories
            if [ -d "data" ]; then
                echo -e "${YELLOW}🗑️  Removing local data directories...${NC}"
                rm -rf data/*
            fi
            
            if [ -d "logs" ]; then
                echo -e "${YELLOW}🗑️  Removing log directories...${NC}"
                rm -rf logs/*
            fi
            
            # Clean up Docker system (optional)
            read -p "Clean up unused Docker resources? (y/n): " docker_clean
            if [ "$docker_clean" = "y" ] || [ "$docker_clean" = "Y" ]; then
                docker system prune -f
            fi
            
            echo -e "${GREEN}✅ Full cleanup completed. All data removed.${NC}"
        else
            echo -e "${YELLOW}❌ Full cleanup cancelled.${NC}"
        fi
        ;;
    5)
        echo -e "${YELLOW}❌ Operation cancelled.${NC}"
        exit 0
        ;;
    *)
        echo -e "${RED}❌ Invalid option. Operation cancelled.${NC}"
        exit 1
        ;;
esac

# Show remaining resources
echo -e "\n${BLUE}📊 Current Docker status:${NC}"
echo -e "${BLUE}========================${NC}"

# Show running containers
running_containers=$(docker-compose ps -q 2>/dev/null | wc -l | tr -d ' ')
if [ "$running_containers" -gt 0 ]; then
    echo -e "${YELLOW}🐳 Running containers:${NC}"
    docker-compose ps
else
    echo -e "${GREEN}✅ No containers running${NC}"
fi

# Show Docker volumes
echo -e "\n${BLUE}💾 Docker volumes:${NC}"
docker volume ls | grep -E "(mariadb|redis|mailhog)" || echo -e "${GREEN}✅ No project volumes found${NC}"

echo -e "\n${YELLOW}💡 Useful Commands:${NC}"
echo -e "   Restart services:    ./scripts/start-dev.sh"
echo -e "   View logs:           docker-compose logs [service]"
echo -e "   Clean up Docker:     docker system prune"
echo -e ""
echo -e "${GREEN}🏁 OpenEMR hybrid development environment stopped.${NC}"