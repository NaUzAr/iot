# 🔄 Server Restart & Maintenance Guide

Dokumentasi untuk restart server SmartAgri IoT.

## Quick Restart (Setelah Reboot VPS)

```bash
# SSH ke server
ssh root@203.194.115.76

# Masuk ke folder project
cd ~/smartagri-iot

# Cek status container
docker ps

# Jika container tidak jalan, start semua
docker-compose up -d

# Verifikasi semua container running
docker ps
```

---

## Deploy Update dari GitHub

```bash
cd ~/smartagri-iot
git pull
docker-compose up -d --build
docker exec smartagri-app php artisan cache:clear
docker exec smartagri-app php artisan view:clear
```

---

## Restart Individual Service

```bash
# Restart Nginx (jika HTTPS bermasalah)
docker restart smartagri-nginx

# Restart App (jika PHP error)
docker restart smartagri-app

# Restart Database
docker restart smartagri-db

# Restart MQTT Broker
docker restart smartagri-mqtt
```

---

## Cek Status Services

```bash
# Cek semua container
docker ps

# Cek MQTT Listener berjalan
docker exec smartagri-app ps aux | grep mqtt

# Cek log Nginx
docker logs smartagri-nginx --tail 20

# Cek log App
docker logs smartagri-app --tail 50

# Cek log MQTT Listener
docker exec smartagri-app cat /var/log/supervisor/mqtt.log
```

---

## Full Server Reboot

```bash
# Pastikan Docker auto-start saat boot
sudo systemctl enable docker

# Reboot server
sudo reboot

# Setelah reboot, SSH kembali dan verifikasi:
docker ps
```

---

## Troubleshooting

### HTTPS Tidak Jalan
```bash
# Cek SSL certificate
docker exec smartagri-nginx ls -la /etc/nginx/ssl/

# Restart nginx
docker restart smartagri-nginx
```

### MQTT Listener Tidak Jalan
```bash
# Restart container app
docker restart smartagri-app

# Cek log error
docker exec smartagri-app cat /var/log/supervisor/mqtt-error.log
```

### Container Tidak Start Setelah Reboot
```bash
cd ~/smartagri-iot
docker-compose up -d
```

---

## SSL Certificate Renewal (Setiap 3 Bulan)

Certificate expire: **2026-04-08**

```bash
# Stop nginx
docker stop smartagri-nginx

# Renew certificate
sudo certbot renew

# Copy certificate baru
sudo cp /etc/letsencrypt/live/smartagri.web.id/fullchain.pem ~/smartagri-iot/docker/ssl/
sudo cp /etc/letsencrypt/live/smartagri.web.id/privkey.pem ~/smartagri-iot/docker/ssl/

# Start nginx
docker start smartagri-nginx
```

---

## Kontak & Info

- **Server IP**: 203.194.115.76
- **Domain**: smartagri.web.id
- **Project Path**: ~/smartagri-iot
