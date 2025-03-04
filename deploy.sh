#!/bin/bash

# Simple deployment script for a Docker-based PHP/MySQL application

# Configuration
REMOTE_USER="username"
REMOTE_HOST="your-server.com"
REMOTE_DIR="/var/www/myapp"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Starting deployment to ${REMOTE_HOST}...${NC}"

# 1. Push latest changes to Git repository
echo -e "${GREEN}Pushing latest changes to Git...${NC}"
git push origin main

# 2. SSH into the remote server and pull the latest changes
echo -e "${GREEN}Connecting to remote server and updating codebase...${NC}"
ssh ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
  cd ${REMOTE_DIR}
  
  # Pull the latest changes from Git
  git pull origin main
  
  # Load environment variables (if they exist in a .env file)
  if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
  fi
  
  # Stop and restart Docker containers
  docker-compose down
  docker-compose up -d --build
  
  # Run any database migrations if needed
  # docker-compose exec php php artisan migrate --force
  
  # Clear any caches
  # docker-compose exec php php artisan cache:clear
EOF

echo -e "${GREEN}Deployment completed successfully!${NC}"
echo -e "${YELLOW}You can access your application at: http://${REMOTE_HOST}${NC}"
