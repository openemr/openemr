# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

> **📖 For general OpenEMR project information, see [AI_INSTRUCTIONS.md](AI_INSTRUCTIONS.md)**
>
> This file contains Claude-specific instructions and enhanced development workflows.

## Claude-Specific Enhancements

### Docker Development Environment (Recommended)

#### Quick Start
```bash
# 1. Clone your fork and navigate to development directory
cd openemr/docker/development-easy

# 2. Start the environment
docker compose up

# 3. Access OpenEMR at http://localhost:8300/ or https://localhost:9300/
# Login: admin / pass

# 4. Stop and cleanup
docker compose down -v
```

#### Access URLs
- OpenEMR: http://localhost:8300/ or https://localhost:9300/
- phpMyAdmin: http://localhost:8310/ (openemr/openemr)
- CouchDB: http://localhost:5984/_utils/ (admin/password)
- MySQL Direct: localhost:8320 (openemr/openemr)

#### Docker Development Tools
Available via `/root/devtools` inside the container:

```bash
# Reset and Setup
docker compose exec openemr /root/devtools dev-reset-install-demodata  # Reset with demo data
docker compose exec openemr /root/devtools dev-reset-install           # Reset clean install

# Code Quality
docker compose exec openemr /root/devtools psr12-fix                   # Fix PSR12 issues
docker compose exec openemr /root/devtools rector-process              # Apply Rector changes
docker compose exec openemr /root/devtools clean-sweep                 # Run full dev suite

# Testing
docker compose exec openemr /root/devtools unit-test                   # PHPUnit tests
docker compose exec openemr /root/devtools api-test                    # API tests
docker compose exec openemr /root/devtools e2e-test                    # End-to-end tests

# API Development
docker compose exec openemr /root/devtools build-api-docs              # Update API docs
docker compose exec openemr /root/devtools register-oauth2-client      # Get OAuth2 credentials

# Backup/Restore
docker compose exec openemr /root/devtools backup <snapshot-name>      # Create backup
docker compose exec openemr /root/devtools restore <snapshot-name>     # Restore backup

# Test Data
docker compose exec openemr /root/devtools import-random-patients 100  # Add 100 random patients
```

#### openemr-cmd Tool
For advanced Docker management, install [openemr-cmd](https://github.com/openemr/openemr-devops/tree/master/utilities/openemr-cmd):

```bash
openemr-cmd change-webroot-blank     # Set webroot to blank
openemr-cmd change-webroot-openemr   # Set webroot to 'openemr'
```

### Console Commands (Symfony)
```bash
bin/console                                    # List all available commands
bin/console openemr:acl-modify                # Modify ACL permissions
bin/console openemr:register                  # Register Zend modules
bin/console openemr:zfc-module                # Module management
bin/console openemr:ccda-import               # Import CCDA documents
bin/console openemr:create-api-documentation  # Generate API docs
```

## Claude Code-Specific Behaviors

### Task Management
- Use the TodoWrite and TodoRead tools to help manage and plan tasks
- Break down larger complex tasks into smaller steps
- Mark todos as completed as soon as you are done with a task

### Code References
When referencing specific functions or pieces of code include the pattern `file_path:line_number` to allow easy navigation to source code locations.

Example:
```
Clients are marked as failed in the `connectToServer` function in src/services/process.ts:712.
```

### Tool Usage
- When doing file search, prefer to use the Task tool to reduce context usage
- Batch tool calls together for optimal performance when requesting multiple independent pieces of information
- When making multiple bash tool calls, send a single message with multiple tool calls to run them in parallel

### Response Style
- Be concise, direct, and to the point
- Answer with fewer than 4 lines of text unless detail is requested
- Avoid unnecessary preamble or postamble
- One word answers are preferred when appropriate
- Avoid introductions, conclusions, and explanations unless specifically asked
