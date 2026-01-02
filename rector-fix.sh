#!/bin/bash
#
# Rector Auto-Fix Helper Script
# Usage: ./rector-fix.sh [path]
#
# Examples:
#   ./rector-fix.sh                           # Fix all files
#   ./rector-fix.sh src/Reports              # Fix specific directory
#   ./rector-fix.sh --staged                 # Fix only staged files

set -e

if [[ "$1" == "--staged" ]]; then
    echo "Running Rector on staged PHP files..."
    
    # Get list of staged PHP files
    staged_files=$(git diff --cached --name-only --diff-filter=ACMR | grep '\.php$' || true)
    
    if [[ -z "$staged_files" ]]; then
        echo "No staged PHP files found."
        exit 0
    fi
    
    echo "Staged files:"
    echo "$staged_files"
    echo ""
    
    # Run rector on each staged file
    echo "$staged_files" | xargs -I {} vendor/bin/rector process {}
    
    # Re-stage the fixed files
    echo "$staged_files" | xargs git add
    
    echo "✓ Rector fixes applied and re-staged"
elif [[ -n "$1" ]]; then
    echo "Running Rector on: $1"
    vendor/bin/rector process "$1"
    echo "✓ Rector fixes applied"
else
    echo "Running Rector on entire codebase..."
    vendor/bin/rector process
    echo "✓ Rector fixes applied"
fi
