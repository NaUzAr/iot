#!/bin/bash
# ===========================================
# SmartAgri IoT - One-Click Deploy Script
# ===========================================
# Jalankan di VPS Ubuntu 20.04+ dengan:
# curl -fsSL https://raw.githubusercontent.com/NaUzAr/smartagri-iot/main/deploy.sh | bash
# Atau: bash deploy.sh
# ===========================================

set -e

echo "🌱 SmartAgri IoT - Docker Deployment"
echo "====================================="

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    echo -e "${YELLOW}⚠️  Jangan jalankan sebagai root. Jalankan dengan user biasa.${NC}"
    exit 1
fi

# Step 1: Install Docker if not present
echo ""
echo -e "${GREEN}📦 Step 1: Checking Docker...${NC}"
if ! command -v docker &> /dev/null; then
    echo "Installing Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    rm get-docker.sh
    echo -e "${GREEN}✅ Docker installed!${NC}"
    echo -e "${YELLOW}⚠️  Please logout and login again, then run this script again.${NC}"
    exit 0
else
    echo -e "${GREEN}✅ Docker is already installed${NC}"
fi

# Step 2: Install Docker Compose if not present
echo ""
echo -e "${GREEN}📦 Step 2: Checking Docker Compose...${NC}"
if ! docker compose version &> /dev/null; then
    echo "Installing Docker Compose plugin..."
    sudo apt-get update
    sudo apt-get install -y docker-compose-plugin
    echo -e "${GREEN}✅ Docker Compose installed!${NC}"
else
    echo -e "${GREEN}✅ Docker Compose is already installed${NC}"
fi

# Step 3: Clone or update repository
echo ""
echo -e "${GREEN}📥 Step 3: Setting up project...${NC}"
PROJECT_DIR="$HOME/smartagri-iot"

if [ -d "$PROJECT_DIR" ]; then
    echo "Updating existing project..."
    cd "$PROJECT_DIR"
    git pull origin main
else
    echo "Cloning project..."
    git clone https://github.com/NaUzAr/smartagri-iot.git "$PROJECT_DIR"
    cd "$PROJECT_DIR"
fi

# Step 4: Setup .env if not exists
echo ""
echo -e "${GREEN}⚙️  Step 4: Configuring environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    
    # Generate random password
    DB_PASSWORD=$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9' | head -c 16)
    
    # Update .env for production
    sed -i "s/APP_ENV=local/APP_ENV=production/" .env
    sed -i "s/APP_DEBUG=true/APP_DEBUG=false/" .env
    sed -i "s/DB_HOST=127.0.0.1/DB_HOST=db/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=smartagri/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=smartagri/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
    
    # Update docker-compose.yml with same password
    sed -i "s/POSTGRES_PASSWORD: smartagri_secret/POSTGRES_PASSWORD: $DB_PASSWORD/" docker-compose.yml
    sed -i "s/DB_PASSWORD=smartagri_secret/DB_PASSWORD=$DB_PASSWORD/" docker-compose.yml
    
    echo -e "${GREEN}✅ Environment configured${NC}"
    echo -e "${YELLOW}📝 DB Password: $DB_PASSWORD (saved in .env)${NC}"
else
    echo -e "${GREEN}✅ Environment file exists${NC}"
fi

# Step 5: Build and start containers
echo ""
echo -e "${GREEN}🐳 Step 5: Building Docker containers...${NC}"
docker compose down 2>/dev/null || true
docker compose up -d --build

# Step 6: Wait for database to be ready
echo ""
echo -e "${GREEN}⏳ Step 6: Waiting for database...${NC}"
sleep 10

# Step 7: Run Laravel setup
echo ""
echo -e "${GREEN}🔧 Step 7: Running Laravel setup...${NC}"
docker compose exec -T app php artisan key:generate --force
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

# Step 8: Fix permissions
echo ""
echo -e "${GREEN}🔐 Step 8: Fixing permissions...${NC}"
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache

# Get server IP
SERVER_IP=$(curl -s ifconfig.me 2>/dev/null || hostname -I | awk '{print $1}')

echo ""
echo "====================================="
echo -e "${GREEN}🎉 DEPLOYMENT COMPLETE!${NC}"
echo "====================================="
echo ""
echo -e "🌐 Access your app at: ${GREEN}http://$SERVER_IP${NC}"
echo ""
echo "📋 Useful commands:"
echo "   cd ~/smartagri-iot"
echo "   docker compose logs -f        # View logs"
echo "   docker compose restart        # Restart all services"
echo "   docker compose down           # Stop all services"
echo ""
echo -e "${YELLOW}⚠️  Next steps:${NC}"
echo "   1. Setup domain & SSL (see DEPLOY_DOCKER.md)"
echo "   2. Update APP_URL in .env with your domain"
echo "   3. Build APK with PWABuilder (see BUILD_APK.md)"
echo ""
