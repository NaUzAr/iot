# =============================================
# PANDUAN: Setup Reverse Proxy + Multi Web
# =============================================

## Arsitektur:

```
                    ┌─────────────────────────┐
    User Request    │    REVERSE PROXY        │
    ────────────────►   (Port 80/443)         │
                    │   reverse-proxy         │
                    └───────────┬─────────────┘
                                │
         ┌──────────────────────┼──────────────────────┐
         │                      │                      │
         ▼                      ▼                      ▼
   smartagri.web.id      tokoonline.com        portfolio.me
         │                      │                      │
         ▼                      ▼                      ▼
   smartagri-nginx       tokoonline-nginx      portfolio-nginx
   (internal)            (internal)            (internal)
         │                      │                      │
         ▼                      ▼                      ▼
   smartagri-app         tokoonline-app        portfolio-app
```

---

## Langkah Setup:

### 1. Buat Network

```bash
docker network create smartagri-network
```

### 2. Start Database

```bash
cd /root/shared-services
docker-compose -f docker-compose.db.yml up -d
```

### 3. Start Tiap Web (tanpa expose port)

```bash
# Web 1
cd /root/smartagri-iot
docker-compose -f docker-compose.yml up -d

# Web 2
cd /root/tokoonline
docker-compose -f docker-compose.yml up -d

# Web 3
cd /root/portfolio
docker-compose -f docker-compose.yml up -d
```

### 4. Start Reverse Proxy

```bash
cd /root/reverse-proxy
docker-compose up -d
```

---

## Tambah Web Baru:

### 1. Setup Web seperti biasa (tanpa ports di nginx)

### 2. Edit `nginx-proxy.conf`, tambah:

```nginx
server {
    listen 80;
    server_name newdomain.com www.newdomain.com;

    location / {
        proxy_pass http://newdomain-nginx:80;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### 3. Reload Reverse Proxy

```bash
docker exec reverse-proxy nginx -s reload
```

---

## Keuntungan Setup Ini:

| Aspek | Benefit |
|-------|---------|
| **Akses** | Tanpa port (domain.com) |
| **Isolasi** | Tiap web punya nginx sendiri |
| **Update** | Restart 1 web, yang lain tidak kena |
| **SSL** | Bisa di proxy atau per-web |
| **Scaling** | Mudah tambah web baru |

---

## SSL (HTTPS):

Untuk SSL, bisa setup di Reverse Proxy:

```nginx
server {
    listen 443 ssl;
    server_name smartagri.web.id;
    
    ssl_certificate /etc/nginx/ssl/smartagri/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/smartagri/privkey.pem;

    location / {
        proxy_pass http://smartagri-nginx:80;
        ...
    }
}
```

Atau pakai **Certbot** untuk auto SSL.
