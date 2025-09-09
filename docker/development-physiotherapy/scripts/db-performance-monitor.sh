#!/bin/bash

# Database Performance Monitoring for Vietnamese Physiotherapy
# Real-time monitoring of database performance metrics
# Author: Dang Tran <tqvdang@msn.com>

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DOCKER_DIR="$(dirname "$SCRIPT_DIR")"
REFRESH_INTERVAL=5

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Database settings
DB_USER="openemr"
DB_PASSWORD="openemr123!"
DB_NAME="openemr"

clear_screen() {
    clear
    echo -e "${CYAN}╔══════════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║         Vietnamese Physiotherapy Database Performance Monitor         ║${NC}"
    echo -e "${CYAN}║                        Press Ctrl+C to exit                          ║${NC}"
    echo -e "${CYAN}╚══════════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

get_db_metrics() {
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" -e "
        SELECT 
            VARIABLE_NAME,
            VARIABLE_VALUE
        FROM information_schema.GLOBAL_STATUS 
        WHERE VARIABLE_NAME IN (
            'Connections',
            'Threads_connected',
            'Threads_running',
            'Questions',
            'Slow_queries',
            'Open_tables',
            'Qcache_hits',
            'Qcache_inserts',
            'Qcache_queries_in_cache',
            'Sort_merge_passes',
            'Table_locks_waited',
            'Innodb_buffer_pool_pages_dirty',
            'Innodb_buffer_pool_pages_free',
            'Innodb_buffer_pool_pages_total'
        );" -s -N 2>/dev/null || echo "Error getting metrics"
}

get_vietnamese_table_stats() {
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
        SELECT 
            TABLE_NAME,
            TABLE_ROWS,
            ROUND(DATA_LENGTH/1024/1024,2) as DATA_MB,
            ROUND(INDEX_LENGTH/1024/1024,2) as INDEX_MB,
            ROUND((DATA_LENGTH + INDEX_LENGTH)/1024/1024,2) as TOTAL_MB,
            TABLE_COLLATION
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = '$DB_NAME' 
        AND (TABLE_NAME LIKE 'pt_%' OR TABLE_NAME LIKE 'vietnamese_%')
        ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC;" -s -N 2>/dev/null || echo "Error getting table stats"
}

get_active_queries() {
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" -e "
        SELECT 
            ID,
            USER,
            HOST,
            DB,
            COMMAND,
            TIME,
            STATE,
            LEFT(INFO, 50) as QUERY_START
        FROM information_schema.PROCESSLIST 
        WHERE COMMAND != 'Sleep' 
        AND USER != 'system user'
        ORDER BY TIME DESC;" -s -N 2>/dev/null || echo "Error getting active queries"
}

get_slow_queries() {
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" -e "
        SELECT 
            VARIABLE_VALUE as 'Total Slow Queries'
        FROM information_schema.GLOBAL_STATUS 
        WHERE VARIABLE_NAME = 'Slow_queries';" -s -N 2>/dev/null || echo "0"
}

get_vietnamese_search_performance() {
    local start_time=$(date +%s.%N)
    
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec -T mariadb mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "
        SELECT COUNT(*) 
        FROM vietnamese_medical_terms 
        WHERE MATCH(vietnamese_term, synonyms_vi) AGAINST('đau' IN BOOLEAN MODE);" -s -N 2>/dev/null || echo "0"
    
    local end_time=$(date +%s.%N)
    local duration=$(echo "$end_time - $start_time" | bc -l 2>/dev/null || echo "0")
    echo "$duration"
}

display_metrics() {
    local metrics="$1"
    
    echo -e "${GREEN}Database Connection Metrics:${NC}"
    echo "┌─────────────────────────┬──────────────┐"
    echo "│ Metric                  │ Value        │"
    echo "├─────────────────────────┼──────────────┤"
    
    while IFS=$'\t' read -r name value; do
        case "$name" in
            "Connections")
                printf "│ %-23s │ %-12s │\n" "Total Connections" "$value"
                ;;
            "Threads_connected")
                printf "│ %-23s │ %-12s │\n" "Active Connections" "$value"
                ;;
            "Threads_running")
                printf "│ %-23s │ %-12s │\n" "Running Threads" "$value"
                ;;
            "Questions")
                printf "│ %-23s │ %-12s │\n" "Total Queries" "$value"
                ;;
            "Slow_queries")
                printf "│ %-23s │ %-12s │\n" "Slow Queries" "$value"
                ;;
        esac
    done <<< "$metrics"
    
    echo "└─────────────────────────┴──────────────┘"
    echo ""
}

display_table_stats() {
    local stats="$1"
    
    echo -e "${BLUE}Vietnamese PT Table Statistics:${NC}"
    echo "┌─────────────────────────────┬──────────┬─────────┬──────────┬──────────┐"
    echo "│ Table Name                  │ Rows     │ Data MB │ Index MB │ Total MB │"
    echo "├─────────────────────────────┼──────────┼─────────┼──────────┼──────────┤"
    
    while IFS=$'\t' read -r table_name rows data_mb index_mb total_mb collation; do
        if [ -n "$table_name" ]; then
            printf "│ %-27s │ %-8s │ %-7s │ %-8s │ %-8s │\n" "$table_name" "$rows" "$data_mb" "$index_mb" "$total_mb"
        fi
    done <<< "$stats"
    
    echo "└─────────────────────────────┴──────────┴─────────┴──────────┴──────────┘"
    echo ""
}

display_active_queries() {
    local queries="$1"
    
    if [ -n "$queries" ] && [ "$queries" != "Error getting active queries" ]; then
        echo -e "${YELLOW}Active Queries:${NC}"
        echo "┌─────┬──────────┬─────────┬──────────────────────────────────────────┐"
        echo "│ ID  │ User     │ Time(s) │ Query                                    │"
        echo "├─────┼──────────┼─────────┼──────────────────────────────────────────┤"
        
        while IFS=$'\t' read -r id user host db command time state query; do
            if [ -n "$id" ] && [ "$id" != "Error getting active queries" ]; then
                printf "│ %-3s │ %-8s │ %-7s │ %-40s │\n" "$id" "$user" "$time" "${query:0:40}"
            fi
        done <<< "$queries"
        
        echo "└─────┴──────────┴─────────┴──────────────────────────────────────────┘"
    else
        echo -e "${GREEN}No active queries (excluding sleeping connections)${NC}"
    fi
    echo ""
}

monitor_performance() {
    while true; do
        clear_screen
        
        local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
        echo -e "${CYAN}Last Updated: $timestamp${NC}"
        echo ""
        
        # Get all metrics
        local db_metrics=$(get_db_metrics)
        local table_stats=$(get_vietnamese_table_stats)
        local active_queries=$(get_active_queries)
        local slow_queries=$(get_slow_queries)
        
        # Test Vietnamese search performance
        echo -e "${GREEN}Testing Vietnamese Search Performance...${NC}"
        local search_duration=$(get_vietnamese_search_performance)
        echo -e "Vietnamese search query time: ${search_duration}s"
        echo ""
        
        # Display metrics
        display_metrics "$db_metrics"
        display_table_stats "$table_stats"
        display_active_queries "$active_queries"
        
        # Additional performance indicators
        echo -e "${CYAN}Performance Indicators:${NC}"
        echo "┌──────────────────────────────┬─────────────────┐"
        echo "│ Metric                       │ Value           │"
        echo "├──────────────────────────────┼─────────────────┤"
        printf "│ %-28s │ %-15s │\n" "Total Slow Queries" "$slow_queries"
        printf "│ %-28s │ %-15s │\n" "Vietnamese Search Time" "${search_duration}s"
        
        # Memory usage estimation
        local container_memory=$(docker stats openemr-mariadb --no-stream --format "{{.MemUsage}}" 2>/dev/null | cut -d'/' -f1 || echo "N/A")
        printf "│ %-28s │ %-15s │\n" "Container Memory Usage" "$container_memory"
        
        echo "└──────────────────────────────┴─────────────────┘"
        echo ""
        
        # Health status
        local health_status="Healthy"
        local health_color="$GREEN"
        
        if [ "$slow_queries" -gt 10 ]; then
            health_status="Warning - High Slow Queries"
            health_color="$YELLOW"
        fi
        
        if (( $(echo "$search_duration > 1.0" | bc -l 2>/dev/null || echo 0) )); then
            health_status="Warning - Slow Search Performance"
            health_color="$YELLOW"
        fi
        
        echo -e "${health_color}Database Health: $health_status${NC}"
        echo ""
        echo -e "${CYAN}Refreshing in ${REFRESH_INTERVAL} seconds... (Press Ctrl+C to exit)${NC}"
        
        sleep "$REFRESH_INTERVAL"
    done
}

# Handle Ctrl+C gracefully
cleanup() {
    echo ""
    echo -e "${GREEN}Performance monitoring stopped.${NC}"
    exit 0
}

trap cleanup INT

# Main execution
case "${1:-monitor}" in
    "monitor"|*)
        echo -e "${GREEN}Starting Vietnamese Physiotherapy Database Performance Monitor...${NC}"
        echo -e "${YELLOW}Refresh interval: ${REFRESH_INTERVAL} seconds${NC}"
        echo ""
        sleep 2
        monitor_performance
        ;;
esac