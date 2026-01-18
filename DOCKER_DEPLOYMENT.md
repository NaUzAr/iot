# 🐳 Docker Deployment Guide - SmartAgri IoT

Panduan step-by-step deploy multi-website Laravel ke VPS dengan Docker.

---

## 📋 STEP 1: Setup VPS

### 1.1 Login & Update System
```bash
ssh root@203.194.115.76
apt update && apt upgrade -y
```

### 1.2 Install Docker
```bash
curl -fsSL https://get.docker.com -o get-docker.sh && sh get-docker.sh
apt install docker-compose-plugin -y
```

### 1.3 Buat Struktur Folder & Network
```bash
mkdir -p /opt/docker-apps/{nginx-proxy,forlizz/src,smartagri/src,postgres}
docker network create webapps
```

---

## 📋 STEP 2: Setup PostgreSQL

### 2.1 Buat docker-compose.yml
```bash
nano /opt/docker-apps/postgres/docker-compose.yml
```

**Isi file:**
```yaml
services:
  postgres:
    image: postgres:15-alpine
    container_name: shared_postgres
    restart: always
    environment:
      POSTGRES_USER: webadmin
      POSTGRES_PASSWORD: Rizal1234
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./init-databases.sql:/docker-entrypoint-initdb.d/init.sql
    networks:
      - webapps

volumes:
  postgres_data:

networks:
  webapps:
    external: true
```

### 2.2 Buat init-databases.sql
```bash
nano /opt/docker-apps/postgres/init-databases.sql
```

**Isi file:**
```sql
CREATE DATABASE db_forlizz;
CREATE DATABASE db_smartagri;
GRANT ALL PRIVILEGES ON DATABASE db_forlizz TO webadmin;
GRANT ALL PRIVILEGES ON DATABASE db_smartagri TO webadmin;
```

### 2.3 Start PostgreSQL
```bash
cd /opt/docker-apps/postgres && docker compose up -d
```

---

## 📋 STEP 3: Setup Forlizz (Birthday Project)

### 3.1 Buat Dockerfile
```bash
nano /opt/docker-apps/forlizz/Dockerfile
```

**Isi file:**
```dockerfile
FROM nginx:alpine
COPY src/ /usr/share/nginx/html/
EXPOSE 80
```

### 3.2 Buat docker-compose.yml
```bash
nano /opt/docker-apps/forlizz/docker-compose.yml
```

**Isi file:**
```yaml
services:
  app:
    build: .
    container_name: forlizz_app
    restart: always
    networks:
      - webapps

networks:
  webapps:
    external: true
```

### 3.3 Clone Source Code
```bash
cd /opt/docker-apps/forlizz/src
git clone https://github.com/NaUzAr/forlizz.git .
```

### 3.4 Build & Start
```bash
cd /opt/docker-apps/forlizz && docker compose up -d --build
```

---

## 📋 STEP 4: Setup SmartAgri (Laravel Project)

### 4.1 Buat Dockerfile
```bash
nano /opt/docker-apps/smartagri/Dockerfile
```

**Isi file:**
```dockerfile
FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx supervisor libpng-dev libzip-dev postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql zip gd bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY nginx.conf /etc/nginx/http.d/default.conf

RUN mkdir -p /etc/supervisor.d
COPY supervisord.ini /etc/supervisor.d/supervisord.ini

WORKDIR /var/www/html
COPY src/ /var/www/html/

RUN composer install --optimize-autoloader --no-dev --no-interaction

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
```

### 4.2 Buat nginx.conf
```bash
nano /opt/docker-apps/smartagri/nginx.conf
```

**Isi file:**
```nginx
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4.3 Buat supervisord.ini
```bash
nano /opt/docker-apps/smartagri/supervisord.ini
```

**Isi file:**
```ini
[supervisord]
nodaemon=true

[program:php-fpm]
command=/usr/local/sbin/php-fpm
autostart=true
autorestart=true

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
autostart=true
autorestart=true
```

### 4.4 Buat docker-compose.yml
```bash
nano /opt/docker-apps/smartagri/docker-compose.yml
```

**Isi file:**
```yaml
services:
  app:
    build: .
    container_name: smartagri_app
    restart: always
    environment:
      APP_ENV: production
      APP_DEBUG: "false"
      APP_KEY: base64:YOUR_APP_KEY_HERE
      APP_URL: https://smartagri.web.id
      DB_CONNECTION: pgsql
      DB_HOST: shared_postgres
      DB_PORT: 5432
      DB_DATABASE: db_smartagri
      DB_USERNAME: webadmin
      DB_PASSWORD: Rizal1234
    volumes:
      - ./src/storage:/var/www/html/storage
    networks:
      - webapps

networks:
  webapps:
    external: true
```

### 4.5 Clone Source Code
```bash
cd /opt/docker-apps/smartagri/src
git clone https://github.com/NaUzAr/web2.git .
```

### 4.6 Build & Start
```bash
cd /opt/docker-apps/smartagri && docker compose up -d --build
```

### 4.7 Run Laravel Setup
```bash
docker exec smartagri_app php artisan key:generate
docker exec smartagri_app php artisan migrate --force
docker exec smartagri_app php artisan storage:link
```

---

## 📋 STEP 5: Setup Nginx Reverse Proxy

### 5.1 Buat docker-compose.yml
```bash
nano /opt/docker-apps/nginx-proxy/docker-compose.yml
```

**Isi file:**
```yaml
services:
  nginx-proxy:
    image: nginx:alpine
    container_name: nginx_proxy
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
      - ./certs:/etc/nginx/certs:ro
      - ./html:/usr/share/nginx/html:ro
    networks:
      - webapps

networks:
  webapps:
    external: true
```

### 5.2 Buat nginx.conf (HTTP Only - Sementara)
```bash
nano /opt/docker-apps/nginx-proxy/nginx.conf
```

**Isi file:**
```nginx
events {
    worker_connections 1024;
}

http {
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    # forlizz.online
    server {
        listen 80;
        server_name forlizz.online www.forlizz.online;
        
        location /.well-known/acme-challenge/ {
            root /usr/share/nginx/html;
        }
        
        location / {
            proxy_pass http://forlizz_app:80;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
        }
    }

    # smartagri.web.id
    server {
        listen 80;
        server_name smartagri.web.id www.smartagri.web.id;
        
        location /.well-known/acme-challenge/ {
            root /usr/share/nginx/html;
        }
        
        location / {
            proxy_pass http://smartagri_app:80;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
        }
    }
}
```

### 5.3 Start Nginx Proxy
```bash
mkdir -p /opt/docker-apps/nginx-proxy/html/.well-known/acme-challenge
cd /opt/docker-apps/nginx-proxy && docker compose up -d
```

---

## 📋 STEP 6: Setup SSL (HTTPS)

### 6.1 Generate SSL untuk forlizz.online
```bash
docker run -it --rm \
  -v /opt/docker-apps/nginx-proxy/certs:/etc/letsencrypt \
  -v /opt/docker-apps/nginx-proxy/html:/usr/share/nginx/html \
  certbot/certbot certonly --webroot \
  -w /usr/share/nginx/html \
  -d forlizz.online -d www.forlizz.online \
  --email rizal@email.com --agree-tos --no-eff-email
```

### 6.2 Generate SSL untuk smartagri.web.id
```bash
docker run -it --rm \
  -v /opt/docker-apps/nginx-proxy/certs:/etc/letsencrypt \
  -v /opt/docker-apps/nginx-proxy/html:/usr/share/nginx/html \
  certbot/certbot certonly --webroot \
  -w /usr/share/nginx/html \
  -d smartagri.web.id -d www.smartagri.web.id \
  --email rizal@email.com --agree-tos --no-eff-email
```

### 6.3 Update nginx.conf dengan HTTPS
```bash
nano /opt/docker-apps/nginx-proxy/nginx.conf
```

**Isi file baru:**
```nginx
events {
    worker_connections 1024;
}

http {
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    # forlizz.online - Redirect HTTP ke HTTPS
    server {
        listen 80;
        server_name forlizz.online www.forlizz.online;
        location /.well-known/acme-challenge/ { root /usr/share/nginx/html; }
        location / { return 301 https://$host$request_uri; }
    }

    # forlizz.online - HTTPS
    server {
        listen 443 ssl;
        server_name forlizz.online www.forlizz.online;
        ssl_certificate /etc/nginx/certs/live/forlizz.online/fullchain.pem;
        ssl_certificate_key /etc/nginx/certs/live/forlizz.online/privkey.pem;
        
        location / {
            proxy_pass http://forlizz_app:80;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }

    # smartagri.web.id - Redirect HTTP ke HTTPS
    server {
        listen 80;
        server_name smartagri.web.id www.smartagri.web.id;
        location /.well-known/acme-challenge/ { root /usr/share/nginx/html; }
        location / { return 301 https://$host$request_uri; }
    }

    # smartagri.web.id - HTTPS
    server {
        listen 443 ssl;
        server_name smartagri.web.id www.smartagri.web.id;
        ssl_certificate /etc/nginx/certs/live/smartagri.web.id/fullchain.pem;
        ssl_certificate_key /etc/nginx/certs/live/smartagri.web.id/privkey.pem;
        
        location / {
            proxy_pass http://smartagri_app:80;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
}
```

### 6.4 Restart Nginx
```bash
cd /opt/docker-apps/nginx-proxy && docker compose restart
```

---

## 🔧 Commands Berguna

```bash
# Lihat semua container
docker ps

# Lihat logs
docker logs forlizz_app
docker logs smartagri_app
docker logs nginx_proxy

# Masuk ke container
docker exec -it smartagri_app sh

# Rebuild setelah update
cd /opt/docker-apps/smartagri && docker compose up -d --build

# Clear Laravel cache
docker exec smartagri_app php artisan cache:clear
docker exec smartagri_app php artisan config:clear
```

---

## 🆘 Troubleshooting

### Error 502 Bad Gateway
```bash
# Cek container running
docker ps

# Cek logs nginx
docker logs nginx_proxy
```

### Error Permission Denied
```bash
docker exec smartagri_app chown -R www-data:www-data /var/www/html/storage
docker exec smartagri_app chmod -R 755 /var/www/html/storage
```

### Error Database Connection
```bash
# Cek postgres running
docker logs shared_postgres

# Test koneksi
docker exec smartagri_app php artisan migrate:status
```
