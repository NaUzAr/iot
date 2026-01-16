# ========================================
# PANDUAN: Tambah Web/Domain Baru
# ========================================

## Checklist Cepat

Untuk menambah web baru dengan domain baru:

| Yang Diubah | Nilai |
|-------------|-------|
| Nama project | `webname` |
| Nama domain | `webname.com` |
| Container name | `webname-app` |
| Database name | `webname_db` |
| DB username | `webname_user` |
| DB password | `webname_secret` |

---

## Langkah-langkah

### 1. Buat Database Baru

```bash
# Masuk ke PostgreSQL
docker exec -it shared-db psql -U postgres

# Jalankan SQL (ganti webname)
CREATE DATABASE webname_db;
CREATE USER webname_user WITH ENCRYPTED PASSWORD 'webname_secret';
GRANT ALL PRIVILEGES ON DATABASE webname_db TO webname_user;
\c webname_db
GRANT ALL ON SCHEMA public TO webname_user;
\q
```

### 2. Clone/Setup Project

```bash
mkdir -p /root/webname
cd /root/webname
git clone https://github.com/xxx/webname.git .
```

### 3. Copy & Edit docker-compose.app.yml

```bash
# Copy dari template
cp docker/templates/docker-compose.app.template.yml docker-compose.app.yml

# Edit dan ganti semua "webname" dengan nama project
nano docker-compose.app.yml
```

### 4. Tambah Nginx Server Block

Edit `/root/nginx-proxy/nginx.conf` atau tambahkan file baru:

```bash
# Copy template
cp docker/templates/nginx-server.template.conf /root/nginx-proxy/conf.d/webname.conf

# Edit dan ganti semua "webname" dengan nama yang sesuai
nano /root/nginx-proxy/conf.d/webname.conf
```

### 5. Setup Environment

```bash
cd /root/webname
cp .env.example .env

# Edit .env
nano .env
# Set:
# DB_HOST=shared-db
# DB_DATABASE=webname_db
# DB_USERNAME=webname_user
# DB_PASSWORD=webname_secret
```

### 6. Build & Start Container

```bash
docker-compose -f docker-compose.app.yml build
docker-compose -f docker-compose.app.yml up -d

# Migrate database
docker exec webname-app php artisan migrate --force
docker exec webname-app php artisan key:generate
```

### 7. Reload Nginx

```bash
docker exec smartagri-nginx nginx -s reload
# atau
docker-compose -f docker-compose.nginx.yml restart
```

---

## Verifikasi

```bash
# Cek container running
docker ps | grep webname

# Test akses
curl -I http://webname.com

# Cek logs jika error
docker logs webname-app --tail 50
```

---

## Troubleshooting

### Error: "host not found in upstream"
- Pastikan container app sudah running
- Pastikan nama container di nginx sama dengan di docker-compose

### Error: "Permission denied" storage
```bash
docker exec webname-app chmod -R 775 /var/www/storage
docker exec webname-app chown -R www-data:www-data /var/www/storage
```

### Error: Database connection refused
- Pastikan DB_HOST=shared-db (bukan localhost)
- Pastikan network sama: smartagri-network
