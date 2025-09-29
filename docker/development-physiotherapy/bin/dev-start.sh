#!/bin/bash
# OpenEMR Docker Development Services Startup Script
# Author: Dang Tran <tqvdang@msn.com>

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DOCKER_DIR="$(dirname "$SCRIPT_DIR")"
COMPOSE_FILE="$DOCKER_DIR/docker-compose.yml"

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

# Check if Docker is running
check_docker() {
    if ! docker info >/dev/null 2>&1; then
        log_error "Docker is not running. Please start Docker Desktop."
        exit 1
    fi
    log_success "Docker is running"
}

# Check if required directories exist
check_directories() {
    log_info "Creating required directories..."
    
    mkdir -p "$DOCKER_DIR/logs/mariadb"
    mkdir -p "$DOCKER_DIR/data/mariadb"
    mkdir -p "$DOCKER_DIR/data/redis"
    mkdir -p "$DOCKER_DIR/backups"
    
    log_success "Directories created"
}

# Start Docker services
start_services() {
    log_info "Starting OpenEMR Docker development services..."
    
    cd "$DOCKER_DIR"
    
    # Pull latest images
    log_info "Pulling latest Docker images..."
    docker-compose pull
    
    # Start services
    docker-compose up -d
    
    log_info "Services starting..."
}

# Wait for services to be ready
wait_for_services() {
    log_info "Waiting for services to be ready..."
    
    # Wait for MariaDB
    log_info "Waiting for MariaDB to be ready..."
    local max_attempts=60
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose exec -T mariadb mysqladmin ping -h"127.0.0.1" --silent >/dev/null 2>&1; then
            log_success "MariaDB is ready"
            break
        fi
        
        if [ $attempt -eq $max_attempts ]; then
            log_error "MariaDB failed to start within 60 seconds"
            docker-compose logs mariadb
            exit 1
        fi
        
        sleep 1
        ((attempt++))
    done
    
    # Wait for Redis
    log_info "Waiting for Redis to be ready..."
    attempt=1
    while [ $attempt -le 30 ]; do
        if docker-compose exec -T redis redis-cli ping >/dev/null 2>&1; then
            log_success "Redis is ready"
            break
        fi
        
        if [ $attempt -eq 30 ]; then
            log_warning "Redis may not be ready, but continuing..."
            break
        fi
        
        sleep 1
        ((attempt++))
    done
    
    # Test database connection
    log_info "Testing database connection..."
    if docker-compose exec -T mariadb mysql -uopenemr -popenemr openemr -e "SELECT 'Database connection successful' as status;" >/dev/null 2>&1; then
        log_success "Database connection verified"
    else
        log_warning "Database connection test failed, but services are running"
    fi
}

# Display service information
show_service_info() {
    echo ""
    log_success "OpenEMR Docker Development Services Started Successfully!"
    echo ""
    echo -e "${BLUE}📊 Service URLs:${NC}"
    echo "  phpMyAdmin:  http://127.0.0.1:8083"
    echo "  Adminer:     http://127.0.0.1:8084"
    echo "  MailHog:     http://127.0.0.1:8025"
    echo ""
    echo -e "${BLUE}🔧 Direct Service Access:${NC}"
    echo "  Database:    127.0.0.1:3306 (user: openemr, password: openemr)"
    echo "  Redis:       127.0.0.1:6380 (password: openemr_redis)"
    echo "  SMTP:        127.0.0.1:1025 (no auth required)"
    echo ""
    echo -e "${BLUE}🌐 Network Configuration:${NC}"
    echo "  Docker Network: 172.20.0.0/16"
    echo "  MariaDB IP:     172.20.0.10"
    echo "  phpMyAdmin IP:  172.20.0.11"
    echo "  Redis IP:       172.20.0.12"
    echo "  MailHog IP:     172.20.0.13"
    echo "  Adminer IP:     172.20.0.14"
    echo ""
    echo -e "${BLUE}📝 Next Steps:${NC}"
    echo "  1. Configure your local PHP to connect to 127.0.0.1:3306"
    echo "  2. Use configs/local-php/local_development.php for integration"
    echo "  3. Run Vietnamese database tools: ./scripts/vietnamese-db-tools.sh"
    echo "  4. Monitor performance: ./scripts/db-performance-monitor.sh"
    echo ""
    echo -e "${YELLOW}To stop services: ./bin/dev-stop.sh${NC}"
}

# Check for existing services
check_existing_services() {
    if docker-compose ps | grep -q "Up"; then
        log_warning "Some services are already running"
        read -p "Do you want to restart them? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            log_info "Stopping existing services..."
            docker-compose down
        else
            log_info "Continuing with existing services..."
            return 0
        fi
    fi
}

# Main execution
main() {
    echo -e "${GREEN}🚀 OpenEMR Docker Development Environment Startup${NC}"
    echo "=================================================="
    
    cd "$DOCKER_DIR"
    
    check_docker
    check_directories
    check_existing_services
    start_services
    wait_for_services
    show_service_info
}

# Handle Ctrl+C gracefully
cleanup() {
    echo ""
    log_warning "Startup interrupted by user"
    exit 1
}

trap cleanup INT

# Run main function
main "$@"