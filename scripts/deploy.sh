#!/bin/bash

# GitDeploy Script for Project Management App
# This script handles deployment to a shared hosting server

set -e  # Exit on error

# Configuration
REMOTE_HOST="your-server.com"
REMOTE_USER="your-username"
REMOTE_PATH="/var/www/html/project-management-app"
BRANCH="main"
BACKUP_DIR="backups"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting deployment...${NC}"

# Check if we're on the correct branch
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
    echo -e "${YELLOW}Warning: You're on branch $CURRENT_BRANCH, not $BRANCH${NC}"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Pull latest changes
echo -e "${GREEN}Pulling latest changes...${NC}"
git pull origin $BRANCH

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR
BACKUP_FILE="$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz"

# Create local backup
echo -e "${GREEN}Creating backup...${NC}"
tar -czf $BACKUP_FILE --exclude='node_modules' --exclude='vendor' --exclude='.git' .

# Sync files to remote server
echo -e "${GREEN}Syncing files to remote server...${NC}"
rsync -avz --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='*.log' \
    ./ $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/

# Run post-deployment tasks on remote server
echo -e "${GREEN}Running post-deployment tasks...${NC}"
ssh $REMOTE_USER@$REMOTE_HOST << 'ENDSSH'
    cd $REMOTE_PATH
    
    # Set proper permissions
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    
    # Run database migrations if needed
    # psql -U postgres -d project_management -f database/schema.sql
    
    # Clear any caches
    # php backend/scripts/clear_cache.php
    
    echo "Deployment completed successfully!"
ENDSSH

echo -e "${GREEN}Deployment completed!${NC}"
echo -e "${YELLOW}Backup saved to: $BACKUP_FILE${NC}"
