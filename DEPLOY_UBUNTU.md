# 🚀 Deploy SmartAgri ke Ubuntu Server

## 1. Install Dependencies

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2+ dan extensions
sudo apt install php php-cli php-fpm php-mbstring php-xml php-curl php-pgsql php-zip unzip -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install PostgreSQL
sudo apt install postgresql postgresql-contrib -y

# Install Nginx
sudo apt install nginx -y
```

## 2. Setup Database

```bash
sudo -u postgres psql

# Di dalam psql:
CREATE DATABASE smartagri;
CREATE USER smartagri_user WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE smartagri TO smartagri_user;
\q
```

## 3. Clone Project

```bash
cd /var/www
sudo git clone https://github.com/NaUzAr/smartagri-iot.git
sudo chown -R www-data:www-data smartagri-iot
cd smartagri-iot
```

## 4. Setup Laravel

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

# Edit .env
nano .env
```

**Edit .env:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smartagri
DB_USERNAME=smartagri_user
DB_PASSWORD=your_secure_password
```

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5. Nginx Config

```bash
sudo nano /etc/nginx/sites-available/smartagri
```

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/smartagri-iot/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/smartagri /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## 6. MQTT Listener Auto-Start (Systemd)

```bash
sudo nano /etc/systemd/system/smartagri-mqtt.service
```

```ini
[Unit]
Description=SmartAgri MQTT Listener
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/smartagri-iot
ExecStart=/usr/bin/php artisan mqtt:listen --host=broker.hivemq.com
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
# Enable dan start service
sudo systemctl daemon-reload
sudo systemctl enable smartagri-mqtt
sudo systemctl start smartagri-mqtt

# Cek status
sudo systemctl status smartagri-mqtt
```

## 7. SSL (HTTPS) dengan Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com
```

## 8. Useful Commands

```bash
# Restart MQTT listener
sudo systemctl restart smartagri-mqtt

# View MQTT logs
sudo journalctl -u smartagri-mqtt -f

# Clear Laravel cache
cd /var/www/smartagri-iot
php artisan cache:clear
php artisan config:clear
```

## 9. Firewall

```bash
sudo ufw allow 'Nginx Full'
sudo ufw allow ssh
sudo ufw enable
```

---

✅ **Selesai!** Server akan auto-start MQTT listener setiap reboot.
