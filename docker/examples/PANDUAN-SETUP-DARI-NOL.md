# =============================================
# PANDUAN LENGKAP: Setup Multi-Web dari Nol
# =============================================
# Server: Ubuntu/Debian dengan Docker terinstall
# =============================================

## Prasyarat

- VPS/Server dengan Docker & Docker Compose terinstall
- Domain sudah pointing ke IP server
- Akses SSH ke server

---

## STEP 1: Install Docker (jika belum)

```bash
# Update system
apt update && apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Docker Compose
apt install docker-compose -y

# Verifikasi
docker --version
docker-compose --version
```

---

## STEP 2: Buat Struktur Folder

```bash
# Buat folder utama
mkdir -p /root/reverse-proxy
mkdir -p /root/shared-db
mkdir -p /root/smartagri-iot
mkdir -p /root/tokoonline    # Web 2 (contoh)

# Lihat struktur
tree /root/
```

Struktur akhir:
```
/root/
├── reverse-proxy/           # Reverse Proxy
│   ├── docker-compose.yml
│   ├── nginx-proxy.conf
│   └── ssl/                 # SSL certificates
│
├── shared-db/               # Database (shared)
│   └── docker-compose.yml
│
├── smartagri-iot/           # Web 1
│   ├── docker-compose.yml
│   ├── docker/
│   │   └── nginx.conf
│   └── ...
│
└── tokoonline/              # Web 2
    ├── docker-compose.yml
    ├── docker/
    │   └── nginx.conf
    └── ...
```

---

## STEP 3: Buat Network

```bash
docker network create smartagri-network
```

---

## STEP 4: Setup Database (Shared)

```bash
cd /root/shared-db
```

Buat file `docker-compose.yml`:
```yaml
version: '2.4'

services:
  db:
    image: postgres:15-alpine
    container_name: shared-db
    restart: unless-stopped
    environment:
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: postgres_secret_2024
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - smartagri-network
    ports:
      - "5432:5432"

networks:
  smartagri-network:
    external: true

volumes:
  postgres_data:
```

Start database:
```bash
docker-compose up -d
```

Buat database untuk tiap web:
```bash
docker exec -it shared-db psql -U postgres

# Di dalam psql:
CREATE DATABASE smartagri_db;
CREATE USER smartagri_user WITH ENCRYPTED PASSWORD 'smartagri_secret';
GRANT ALL PRIVILEGES ON DATABASE smartagri_db TO smartagri_user;

CREATE DATABASE tokoonline_db;
CREATE USER tokoonline_user WITH ENCRYPTED PASSWORD 'tokoonline_secret';
GRANT ALL PRIVILEGES ON DATABASE tokoonline_db TO tokoonline_user;

\q
```

---

## STEP 5: Setup Web 1 (SmartAgri)

```bash
cd /root/smartagri-iot

# Clone project
git clone https://github.com/NaUzAr/smartagri-iot.git .

# Atau pull jika sudah ada
git pull origin main
```

Buat `docker-compose.yml`:
```yaml
version: '2.4'

services:
  app:
    build: .
    image: smartagri-app
    container_name: smartagri-app
    restart: unless-stopped
    volumes:
      - .:/var/www
      - smartagri_vendor:/var/www/vendor
      - smartagri_storage:/var/www/storage
    networks:
      - smartagri-network
    environment:
      - DB_HOST=shared-db
      - DB_DATABASE=smartagri_db
      - DB_USERNAME=smartagri_user
      - DB_PASSWORD=smartagri_secret

  nginx:
    image: nginx:alpine
    container_name: smartagri-nginx
    restart: unless-stopped
    # TIDAK ADA ports - hanya internal
    volumes:
      - .:/var/www
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    networks:
      - smartagri-network
    depends_on:
      - app

networks:
  smartagri-network:
    external: true

volumes:
  smartagri_vendor:
  smartagri_storage:
```

Build & Start:
```bash
docker-compose build
docker-compose up -d

# Setup Laravel
docker exec smartagri-app cp .env.example .env
docker exec smartagri-app php artisan key:generate
docker exec smartagri-app php artisan migrate --force
```

---

## STEP 6: Setup Web 2 (Toko Online) - SAMA seperti Web 1

```bash
cd /root/tokoonline
git clone https://github.com/xxx/tokoonline.git .
```

Buat `docker-compose.yml` (BEDA: nama container & DB):
```yaml
version: '2.4'

services:
  app:
    build: .
    image: tokoonline-app           # ← BEDA
    container_name: tokoonline-app  # ← BEDA
    restart: unless-stopped
    volumes:
      - .:/var/www
      - tokoonline_vendor:/var/www/vendor    # ← BEDA
      - tokoonline_storage:/var/www/storage
    networks:
      - smartagri-network
    environment:
      - DB_HOST=shared-db
      - DB_DATABASE=tokoonline_db        # ← BEDA
      - DB_USERNAME=tokoonline_user      # ← BEDA
      - DB_PASSWORD=tokoonline_secret    # ← BEDA

  nginx:
    image: nginx:alpine
    container_name: tokoonline-nginx     # ← BEDA
    restart: unless-stopped
    volumes:
      - .:/var/www
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    networks:
      - smartagri-network
    depends_on:
      - app

networks:
  smartagri-network:
    external: true

volumes:
  tokoonline_vendor:     # ← BEDA
  tokoonline_storage:
```

Build & Start:
```bash
docker-compose build
docker-compose up -d
docker exec tokoonline-app cp .env.example .env
docker exec tokoonline-app php artisan key:generate
docker exec tokoonline-app php artisan migrate --force
```

---

## STEP 7: Setup Reverse Proxy

```bash
cd /root/reverse-proxy
```

Buat `docker-compose.yml`:
```yaml
version: '2.4'

services:
  proxy:
    image: nginx:alpine
    container_name: reverse-proxy
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx-proxy.conf:/etc/nginx/conf.d/default.conf
      - ./ssl:/etc/nginx/ssl:ro
    networks:
      - smartagri-network

networks:
  smartagri-network:
    external: true
```

Buat `nginx-proxy.conf`:
```nginx
# Web 1: smartagri.web.id
server {
    listen 80;
    server_name smartagri.web.id www.smartagri.web.id;

    location / {
        proxy_pass http://smartagri-nginx:80;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# Web 2: tokoonline.com
server {
    listen 80;
    server_name tokoonline.com www.tokoonline.com;

    location / {
        proxy_pass http://tokoonline-nginx:80;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Start:
```bash
docker-compose up -d
```

---

## STEP 8: Verifikasi

```bash
# Cek semua container running
docker ps

# Test akses
curl -I http://smartagri.web.id
curl -I http://tokoonline.com
```

---

## STEP 9: Setup SSL (Opsional)

```bash
# Install Certbot
apt install certbot -y

# Generate SSL
certbot certonly --standalone -d smartagri.web.id
certbot certonly --standalone -d tokoonline.com

# Copy ke folder
cp /etc/letsencrypt/live/smartagri.web.id/fullchain.pem /root/reverse-proxy/ssl/smartagri/
cp /etc/letsencrypt/live/smartagri.web.id/privkey.pem /root/reverse-proxy/ssl/smartagri/

# Update nginx-proxy.conf untuk HTTPS
# Restart
docker-compose restart
```

---

## Ringkasan Urutan Start:

```bash
# 1. Network
docker network create smartagri-network

# 2. Database
cd /root/shared-db && docker-compose up -d

# 3. Web 1
cd /root/smartagri-iot && docker-compose up -d

# 4. Web 2
cd /root/tokoonline && docker-compose up -d

# 5. Reverse Proxy (TERAKHIR)
cd /root/reverse-proxy && docker-compose up -d
```

---

## Troubleshooting:

### Container restart terus
```bash
docker logs [container_name] --tail 50
```

### Tidak bisa connect ke database
- Pastikan network sama
- Cek DB_HOST=shared-db (bukan localhost)

### 502 Bad Gateway
- Pastikan container app sudah running
- Cek nama container di proxy_pass sama dengan container_name

### Permission denied storage
```bash
docker exec [app_container] chmod -R 775 /var/www/storage
docker exec [app_container] chown -R www-data:www-data /var/www/storage
```
