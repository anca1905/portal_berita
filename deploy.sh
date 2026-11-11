#!/bin/bash
# ==============================================================================
# Script Otomatis Deployment Portal Berita (Production)
# Kompatibel: Ubuntu 20.04 / 22.04 / 24.04 & Debian 11 / 12
# ==============================================================================

set -e

echo "=========================================================="
echo " 🚀 Memulai Instalasi & Setup Live Server Portal Berita"
echo "=========================================================="

# Pastikan dijalankan sebagai root
if [ "$EUID" -ne 0 ]; then
  echo "❌ Harap jalankan script ini sebagai root (sudo bash deploy.sh)"
  exit 1
fi

# 1. Update Repository Sistem
echo "📦 [1/6] Memperbarui paket sistem OS..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -y && apt-get upgrade -y

# 2. Instalasi Nginx, PHP, MariaDB & Ekstensi
echo "🌐 [2/6] Menginstal Nginx, PHP-FPM, MariaDB & modul pendukung..."
apt-get install -y nginx mariadb-server mariadb-client git curl unzip ufw
apt-get install -y php-fpm php-mysql php-mbstring php-gd php-curl php-xml php-zip php-intl

# Deteksi socket PHP-FPM
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
PHP_FPM_SOCK=$(find /run/php/ -name "php*fpm.sock" | head -n 1)

if [ -z "$PHP_FPM_SOCK" ]; then
    PHP_FPM_SOCK="/run/php/php${PHP_VERSION}-fpm.sock"
fi
echo "✅ Menggunakan PHP versi: ${PHP_VERSION} (Socket: ${PHP_FPM_SOCK})"

# 3. Konfigurasi Database MariaDB
echo "🗄️ [3/6] Menyiapkan Database xnn..."
systemctl start mariadb
systemctl enable mariadb

DB_NAME="xnn"
DB_USER="xnn_user"
DB_PASS="XnnNews#Secure2026!"

mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF

echo "✅ Database '${DB_NAME}' & User '${DB_USER}' berhasil dibuat."

# 4. Clone / Deploy Source Code dari GitHub
echo "📂 [4/6] Mengunduh source code dari GitHub..."
TARGET_DIR="/var/www/portal_berita"
REPO_URL="https://github.com/anca1905/portal_berita.git"

if [ -d "$TARGET_DIR/.git" ]; then
    echo "Direktori sudah ada, memperbarui dari GitHub..."
    cd "$TARGET_DIR"
    git fetch origin main
    git reset --hard origin/main
else
    rm -rf "$TARGET_DIR"
    git clone "$REPO_URL" "$TARGET_DIR"
    cd "$TARGET_DIR"
fi

# Impor database awal jika ada file SQL
if [ -f "$TARGET_DIR/database_xnn.sql" ]; then
    echo "Mengimpor database xnn ke MariaDB..."
    mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "$TARGET_DIR/database_xnn.sql"
    echo "✅ Database berhasil diimpor!"
fi

# Deteksi IP Server atau Domain
SERVER_IP=$(curl -s https://api.ipify.org || hostname -I | awk '{print $1}')

# Buat file .env untuk production
cat << EOF > "$TARGET_DIR/.env"
DB_HOST=localhost
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}
APP_URL=http://${SERVER_IP}
MAINTENANCE_MODE=false
EOF
echo "✅ File .env berhasil dibuat."

# 5. Konfigurasi Permission Folder
echo "🔒 [5/6] Mengatur permission folder dan file..."
chown -R www-data:www-data "$TARGET_DIR"
find "$TARGET_DIR" -type d -exec chmod 755 {} \;
find "$TARGET_DIR" -type f -exec chmod 644 {} \;
chmod -R 775 "$TARGET_DIR/uploads"

# 6. Konfigurasi Nginx VirtualHost
echo "⚙️ [6/6] Menyiapkan VirtualHost Nginx..."

cat << EOF > /etc/nginx/sites-available/portal_berita
server {
    listen 80;
    listen [::]:80;
    server_name ${SERVER_IP} _;

    root ${TARGET_DIR};
    index index.php index.html;

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript image/svg+xml;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    # Clean URL routing
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # PHP-FPM Handler
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_FPM_SOCK};
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # Protect Hidden Files
    location ~ /\. {
        deny all;
    }

    # Static Assets Caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp|svg|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
EOF

# Aktifkan site di Nginx
ln -sf /etc/nginx/sites-available/portal_berita /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test Nginx & Restart
nginx -t
systemctl restart nginx
systemctl restart php${PHP_VERSION}-fpm

echo "=========================================================="
echo " 🎉 DEPLOYMENT SELESAI!"
echo " Portal Berita sudah LIVE dan dapat diakses di:"
echo " 👉 http://${SERVER_IP}/"
echo " 👉 Pasang Iklan: http://${SERVER_IP}/pasang-iklan.php"
echo " 👉 Admin Panel : http://${SERVER_IP}/admin/"
echo "=========================================================="
