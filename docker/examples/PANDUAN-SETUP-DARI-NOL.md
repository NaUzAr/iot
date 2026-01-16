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

## STEP 2: Clone Server Infrastructure

```bash
cd /root
git clone https://github.com/NaUzAr/server-infra.git
cd server-infra
```

---

## STEP 3: Buat Network

```bash
docker network create smartagri-network
```

---

## STEP 4: Start Database (Shared)

```bash
cd /root/server-infra/shared-db
docker-compose up -d

# Tunggu database ready
sleep 5

# Cek running
docker ps
```

Database otomatis buat `smartagri_db` dari `init.sql`.

**Tambah database baru (manual):**
```bash
docker exec -it shared-db psql -U postgres

# Di dalam psql:
CREATE DATABASE tokoonline_db;
CREATE USER tokoonline_user WITH ENCRYPTED PASSWORD 'tokoonline_secret';
GRANT ALL PRIVILEGES ON DATABASE tokoonline_db TO tokoonline_user;
\q
```

---

## STEP 5: Clone Web App(s)

```bash
# Web 1: SmartAgri
cd /root
git clone https://github.com/NaUzAr/smartagri-iot.git
cd smartagri-iot

# Edit docker-compose jika perlu
# Pastikan:
# - network: smartagri-network (external: true)
# - nginx: TIDAK ada ports
# - DB_HOST: shared-db
```

**Contoh docker-compose.yml untuk web:**
```yaml
version: '2.4'

services:
  app:
    build: .
    image: smartagri-app
    container_name: smartagri-app
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
    # ❌ TIDAK ADA ports - internal only
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

---

## STEP 6: Build & Start Web App

```bash
cd /root/smartagri-iot

# Build
docker-compose build

# Start
docker-compose up -d

# Setup Laravel
docker exec smartagri-app cp .env.example .env
docker exec smartagri-app php artisan key:generate
docker exec smartagri-app php artisan migrate --force

# Fix permissions
docker exec smartagri-app chmod -R 775 /var/www/storage
docker exec smartagri-app chown -R www-data:www-data /var/www/storage
```

---

## STEP 7: Tambah Domain ke Reverse Proxy

Edit `nginx.conf` di server-infra:

```bash
nano /root/server-infra/reverse-proxy/nginx.conf
```

Tambah server block:
```nginx
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
```

---

## STEP 8: Start Reverse Proxy

```bash
cd /root/server-infra/reverse-proxy
docker-compose up -d
```

---

## STEP 9: Verifikasi

```bash
# Cek semua container running
docker ps

# Seharusnya ada:
# - shared-db
# - smartagri-app
# - smartagri-nginx
# - reverse-proxy

# Test akses
curl -I http://smartagri.web.id
```

---

## Ringkasan Urutan Start:

```bash
# 1. Network
docker network create smartagri-network

# 2. Clone server-infra
cd /root
git clone https://github.com/NaUzAr/server-infra.git

# 3. Database
cd /root/server-infra/shared-db
docker-compose up -d

# 4. Clone & start web
cd /root
git clone https://github.com/NaUzAr/smartagri-iot.git
cd smartagri-iot
docker-compose up -d

# 5. Edit nginx config
nano /root/server-infra/reverse-proxy/nginx.conf

# 6. Start reverse proxy (TERAKHIR)
cd /root/server-infra/reverse-proxy
docker-compose up -d
```

---

## Tambah Web Baru:

### 1. Buat database
```bash
docker exec -it shared-db psql -U postgres
CREATE DATABASE newweb_db;
CREATE USER newweb_user WITH ENCRYPTED PASSWORD 'newweb_secret';
GRANT ALL PRIVILEGES ON DATABASE newweb_db TO newweb_user;
\q
```

### 2. Clone project
```bash
cd /root
git clone https://github.com/xxx/newweb.git
cd newweb
docker-compose up -d
```

### 3. Tambah domain
```bash
nano /root/server-infra/reverse-proxy/nginx.conf
# Tambah server block baru
```

### 4. Reload proxy
```bash
docker exec reverse-proxy nginx -s reload
```

---

## Setup SSL (Let's Encrypt)

```bash
# Install certbot
apt install certbot -y

# Stop reverse proxy 
cd /root/server-infra/reverse-proxy
docker-compose down

# Generate certificate
certbot certonly --standalone -d smartagri.web.id

# Copy ke folder ssl
mkdir -p ssl/smartagri
cp /etc/letsencrypt/live/smartagri.web.id/fullchain.pem ssl/smartagri/
cp /etc/letsencrypt/live/smartagri.web.id/privkey.pem ssl/smartagri/

# Update nginx.conf untuk HTTPS (lihat template di file)

# Start reverse proxy
docker-compose up -d
```

---

## Troubleshooting

### Container restart terus
```bash
docker logs [container_name] --tail 50
```

### 502 Bad Gateway
- Pastikan container nginx web sudah running
- Cek nama container di `proxy_pass` sama dengan `container_name`

### Database connection refused
- Pastikan `DB_HOST=shared-db` (bukan localhost)
- Pastikan network sama (`smartagri-network`)

### Permission denied storage
```bash
docker exec [app_container] chmod -R 775 /var/www/storage
docker exec [app_container] chown -R www-data:www-data /var/www/storage
```

### SSL Certificate error
- Pastikan file *.pem ada di folder `ssl/[domain]/`
- Cek path di nginx.conf benar

---

## Struktur Akhir di Server:

```
/root/
├── server-infra/           # Clone dari GitHub
│   ├── reverse-proxy/
│   │   ├── docker-compose.yml
│   │   ├── nginx.conf      # ← Edit untuk tambah domain
│   │   └── ssl/
│   └── shared-db/
│       ├── docker-compose.yml
│       └── init.sql
│
├── smartagri-iot/          # Web 1
│   ├── docker-compose.yml
│   └── ...
│
└── tokoonline/             # Web 2
    ├── docker-compose.yml
    └── ...
```
