#!/bin/bash
# OpenEMR Docker Development Services Stop Script
# Author: Dang Tran <tqvdang@msn.com>

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DOCKER_DIR="$(dirname "$SCRIPT_DIR")"

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

# Stop services
stop_services() {
    log_info "Stopping OpenEMR Docker development services..."
    
    cd "$DOCKER_DIR"
    
    if ! docker-compose ps | grep -q "Up"; then
        log_warning "No running services found"
        return 0
    fi
    
    # Stop services gracefully
    docker-compose down
    
    log_success "Services stopped successfully"
}

# Clean up (optional)
cleanup_resources() {
    if [[ "${1:-}" == "--clean" ]]; then
        log_warning "Cleaning up Docker resources..."
        
        # Remove stopped containers
        docker container prune -f >/dev/null 2>&1 || true
        
        # Remove unused networks  
        docker network prune -f >/dev/null 2>&1 || true
        
        log_success "Docker resources cleaned up"
    fi
}

# Show status
show_status() {
    echo ""
    log_success "OpenEMR Docker Development Services Stopped"
    echo ""
    echo -e "${BLUE}💾 Data Persistence:${NC}"
    echo "  Database data is preserved in Docker volumes"
    echo "  Redis data is preserved in Docker volumes"
    echo "  Configuration files remain unchanged"
    echo ""
    echo -e "${BLUE}🔄 To restart services:${NC}"
    echo "  ./bin/dev-start.sh"
    echo ""
    echo -e "${BLUE}🧹 To clean up resources:${NC}"
    echo "  ./bin/dev-stop.sh --clean"
}

# Main execution
main() {
    echo -e "${RED}🛑 OpenEMR Docker Development Environment Shutdown${NC}"
    echo "====================================================="
    
    stop_services
    cleanup_resources "$@"
    show_status
}

# Run main function
main "$@"