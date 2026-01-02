# 🐳 Deploy SmartAgri IoT dengan Docker

## 🚀 Quick Deploy (One-Click)

SSH ke VPS Ubuntu Anda, lalu jalankan:

```bash
# Clone dan deploy
git clone https://github.com/NaUzAr/smartagri-iot.git
cd smartagri-iot
bash deploy.sh
```

Selesai! Akses aplikasi di `http://IP_VPS_ANDA`

---

## 📋 Manual Deploy Step-by-Step

### Prasyarat
- Ubuntu Server 20.04+ 
- Minimal RAM 1GB
- Port 80 dan 443 terbuka

### 1. Install Docker

```bash
# Update system
sudo apt update

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo apt install docker-compose-plugin -y

# Tambah user ke group docker
sudo usermod -aG docker $USER
newgrp docker
```

### 2. Clone Project

```bash
cd $HOME
git clone https://github.com/NaUzAr/smartagri-iot.git
cd smartagri-iot
```

### 3. Setup Environment

```bash
cp .env.example .env
nano .env
```

**Edit .env:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=smartagri
DB_USERNAME=smartagri
DB_PASSWORD=GANTI_PASSWORD_AMAN
```

**Update docker-compose.yml dengan password yang sama:**
```bash
nano docker-compose.yml
# Ganti semua "smartagri_secret" dengan password Anda
```

### 4. Build & Run

```bash
# Build dan jalankan
docker compose up -d --build

# Tunggu 10 detik, lalu setup Laravel
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Fix permissions
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### 5. Akses Aplikasi

Buka browser: `http://IP_VPS_ANDA`

---

## 🔒 Setup SSL dengan Certbot (HTTPS)

### Opsi A: Dengan Domain

```bash
# Install Certbot
sudo apt install certbot -y

# Stop nginx sementara
docker compose stop nginx

# Generate certificate
sudo certbot certonly --standalone -d yourdomain.com

# Certificate akan tersimpan di:
# /etc/letsencrypt/live/yourdomain.com/fullchain.pem
# /etc/letsencrypt/live/yourdomain.com/privkey.pem
```

**Update docker-compose.yml untuk SSL:**

```yaml
  nginx:
    image: nginx:alpine
    container_name: smartagri-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www
      - ./docker/nginx-ssl.conf:/etc/nginx/conf.d/default.conf
      - /etc/letsencrypt:/etc/letsencrypt:ro
    networks:
      - smartagri-network
    depends_on:
      - app
```

**Buat file `docker/nginx-ssl.conf`:**

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Restart dengan SSL
docker compose up -d
```

### Opsi B: Tanpa Domain (Self-Signed)

Untuk testing saja:

```bash
# Generate self-signed certificate
mkdir -p docker/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout docker/ssl/privkey.pem \
    -out docker/ssl/fullchain.pem \
    -subj "/CN=localhost"
```

---

## 📱 Setelah Deploy: Build APK

Setelah aplikasi online dengan HTTPS:

1. Buka [pwabuilder.com](https://www.pwabuilder.com/)
2. Masukkan URL: `https://yourdomain.com`
3. Package → Android → Download APK

---

## 🛠️ Useful Commands

```bash
# Masuk ke folder project
cd ~/smartagri-iot

# Lihat status container
docker compose ps

# Lihat logs semua service
docker compose logs -f

# Lihat logs MQTT listener saja
docker compose logs -f app | grep mqtt

# Restart semua
docker compose restart

# Stop semua
docker compose down

# Update code dan rebuild
git pull
docker compose up -d --build
docker compose exec app php artisan migrate --force

# Backup database
docker compose exec db pg_dump -U smartagri smartagri > backup_$(date +%Y%m%d).sql

# Restore database
cat backup.sql | docker compose exec -T db psql -U smartagri -d smartagri

# Clear Laravel cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear

# Masuk ke shell container
docker compose exec app bash

# Masuk ke PostgreSQL
docker compose exec db psql -U smartagri -d smartagri
```

---

## 🔧 Troubleshooting

### Container tidak start
```bash
docker compose logs app
docker compose logs db
docker compose logs nginx
```

### Permission error
```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Database connection refused
```bash
# Tunggu database ready
sleep 10
docker compose exec app php artisan migrate --force
```

### Rebuild dari awal
```bash
docker compose down -v  # -v hapus volume (DATABASE HILANG!)
docker compose up -d --build
docker compose exec app php artisan migrate --force
```

---

## 📁 Struktur Docker

```
smartagri-iot/
├── Dockerfile              # PHP 8.2-FPM + Composer + Supervisor
├── docker-compose.yml      # App + Nginx + PostgreSQL
├── deploy.sh               # One-click deploy script
└── docker/
    ├── nginx.conf          # Nginx config (HTTP)
    ├── nginx-ssl.conf      # Nginx config (HTTPS) - buat manual
    └── supervisord.conf    # PHP-FPM + MQTT Listener
```

---

## ⚡ Auto-Start saat Reboot

Docker containers dengan `restart: unless-stopped` akan auto-start. 
Pastikan Docker service enabled:

```bash
sudo systemctl enable docker
```

---

✅ **Selesai!** Aplikasi Anda sekarang online dan siap di-build menjadi APK.
