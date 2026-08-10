#!/bin/bash
# ==============================================================================
# SeptaPanel — Interactive Auto Installer (Pterodactyl-Style Wizard)
# Supported OS: Debian 10/11/12 & Ubuntu 20.04/22.04/24.04
# Repository: https://github.com/ynzynnn/webhostingpanel
# ==============================================================================

set -e

# Clear Terminal & Display Banner
clear
echo -e "\033[1;36m"
echo "  ____            _        ____  _     "
echo " / ___|  ___ _ __| |_ __ _|  _ \/ \    "
echo " \___ \ / _ \ '__| __/ _\` | |_) / _ \   "
echo "  ___) |  __/ |  | || (_| |  __/ ___ \  "
echo " |____/ \___|_|   \__\__,_|_| /_/   \_\ "
echo "                                        "
echo "  SeptaPanel — VPS Hosting Control Panel Installer"
echo -e "\033[0m"
echo "========================================================"

# Ensure running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "\033[1;31m[ERROR] Please run this installer script as root (sudo bash install.sh)\033[0m"
    exit 1
fi

# ------------------------------------------------------------------------------
# Interactive Setup Wizard Questions
# ------------------------------------------------------------------------------
echo -e "\033[1;33m[?] Interactive Configuration Wizard\033[0m"

# 1. Panel Domain / IP
read -p "Enter Panel Domain / FQDN (e.g. panel.yourdomain.com or IP) [localhost]: " PANEL_DOMAIN
PANEL_DOMAIN=${PANEL_DOMAIN:-localhost}

# 2. Database Password
read -sp "Enter MariaDB Password for 'septa_user' [randomly generated if empty]: " DB_PASS
echo ""
if [ -z "$DB_PASS" ]; then
    DB_PASS=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 16)
    echo -e "\033[1;32mGenerated DB Password: $DB_PASS\033[0m"
fi

# 3. Admin User Credentials
read -p "Enter Admin Email [admin@septapanel.local]: " ADMIN_EMAIL
ADMIN_EMAIL=${ADMIN_EMAIL:-admin@septapanel.local}

read -sp "Enter Admin Password [password123]: " ADMIN_PASS
echo ""
ADMIN_PASS=${ADMIN_PASS:-password123}

# 4. SSL Option
read -p "Enable Let's Encrypt Auto SSL for Panel Domain? (y/n) [y]: " ENABLE_SSL
ENABLE_SSL=${ENABLE_SSL:-y}

echo ""
echo -e "\033[1;36mStarting automated installation with configured options...\033[0m"
echo "--------------------------------------------------------"

# ------------------------------------------------------------------------------
# Step 1: Detect OS & Clean Repositories
# ------------------------------------------------------------------------------
echo "[1/7] Cleaning old PPA lists & detecting operating system..."
rm -f /etc/apt/sources.list.d/ondrej-*.list /etc/apt/sources.list.d/php.list

if [ -f /etc/debian_version ]; then
    IS_DEBIAN=true
    DEB_CODENAME=$(cat /etc/os-release | grep VERSION_CODENAME | cut -d= -f2 || lsb_release -sc || echo "buster")
    echo "Detected OS: Debian ($DEB_CODENAME)"
else
    IS_DEBIAN=false
    DEB_CODENAME=$(lsb_release -sc 2>/dev/null || echo "jammy")
    echo "Detected OS: Ubuntu ($DEB_CODENAME)"
fi

# ------------------------------------------------------------------------------
# Step 2: Install Core Dependencies & PHP 8.3
# ------------------------------------------------------------------------------
echo "[2/7] Installing Nginx, MariaDB, PHP 8.3 & core utilities..."
apt update && apt upgrade -y
apt install -y curl wget git unzip ufw certbot python3-certbot-nginx openssh-server gnupg lsb-release ca-certificates apt-transport-https nginx mariadb-server mariadb-client

if [ "$IS_DEBIAN" = true ]; then
    wget -qO /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
    echo "deb https://packages.sury.org/php/ $DEB_CODENAME main" > /etc/apt/sources.list.d/php.list
else
    echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ondrej-php.list
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C 2>/dev/null || true
fi

apt update
apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring \
php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd php8.3-sqlite3

if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

# ------------------------------------------------------------------------------
# Step 3: Configure MariaDB Database
# ------------------------------------------------------------------------------
echo "[3/7] Setting up MariaDB database & septa_user..."
mysql -u root <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS septapanel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'septa_user'@'localhost' IDENTIFIED BY '$DB_PASS';
ALTER USER 'septa_user'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON septapanel.* TO 'septa_user'@'localhost';
GRANT ALL PRIVILEGES ON *.* TO 'septa_user'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
MYSQL_SCRIPT

# ------------------------------------------------------------------------------
# Step 4: Clone & Configure SeptaPanel Source Code
# ------------------------------------------------------------------------------
echo "[4/7] Deploying SeptaPanel application to /var/www/septapanel..."
mkdir -p /var/www/vhosts /var/www/septapanel
chown -R www-data:www-data /var/www/vhosts /var/www/septapanel
rm -rf /var/www/septapanel
mkdir -p /var/www/septapanel
git clone https://github.com/ynzynnn/webhostingpanel.git /var/www/septapanel

cd /var/www/septapanel
composer install --no-dev --optimize-autoloader

cp .env.example .env
sed -i "s/APP_URL=.*/APP_URL=http:\/\/$PANEL_DOMAIN/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=septapanel/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=septa_user/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

php artisan key:generate --force
php artisan migrate:fresh --seed --force

# Update Admin credentials if customized
if [ "$ADMIN_EMAIL" != "admin@septapanel.local" ] || [ "$ADMIN_PASS" != "password123" ]; then
    php artisan tinker --execute="\$u = App\Models\User::where('role', 'admin')->first(); if(\$u){ \$u->email = '$ADMIN_EMAIL'; \$u->password = Hash::make('$ADMIN_PASS'); \$u->save(); }"
fi

chown -R www-data:www-data /var/www/septapanel
chmod -R 775 /var/www/septapanel/storage /var/www/septapanel/bootstrap/cache

# ------------------------------------------------------------------------------
# Step 5: Configure Nginx VirtualHost
# ------------------------------------------------------------------------------
echo "[5/7] Configuring Nginx VirtualHost for $PANEL_DOMAIN..."
cat << EOF > /etc/nginx/sites-available/septapanel.conf
server {
    listen 80;
    server_name $PANEL_DOMAIN;

    root /var/www/septapanel/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/septapanel.conf /etc/nginx/sites-enabled/septapanel.conf
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

# ------------------------------------------------------------------------------
# Step 6: Security Sudoers & Firewall (UFW)
# ------------------------------------------------------------------------------
echo "[6/7] Configuring Sudoers security rules & Firewall..."
cat << 'EOF' > /etc/sudoers.d/septapanel
www-data ALL=(ALL) NOPASSWD: /usr/sbin/nginx -t, /usr/bin/systemctl reload nginx, /usr/bin/certbot, /bin/cp, /bin/ln, /bin/rm, /usr/bin/cp, /usr/bin/ln, /usr/bin/rm
EOF
chmod 0440 /etc/sudoers.d/septapanel

ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
echo "y" | ufw enable

# ------------------------------------------------------------------------------
# Step 7: Optional Auto SSL
# ------------------------------------------------------------------------------
if [[ "$ENABLE_SSL" =~ ^[Yy]$ ]] && [ "$PANEL_DOMAIN" != "localhost" ] && [[ ! "$PANEL_DOMAIN" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "[7/7] Issuing Let's Encrypt SSL for $PANEL_DOMAIN..."
    certbot --nginx -d $PANEL_DOMAIN --non-interactive --agree-tos -m $ADMIN_EMAIL || true
else
    echo "[7/7] Skipping SSL (Domain is IP or localhost or SSL disabled)."
fi

# ------------------------------------------------------------------------------
# Installation Completed Summary
# ------------------------------------------------------------------------------
clear
echo -e "\033[1;32m"
echo "========================================================"
echo "  🎉 SeptaPanel Installation Completed Successfully!"
echo "========================================================"
echo -e "\033[0m"
echo "Access URL     : http://$PANEL_DOMAIN (or https://$PANEL_DOMAIN)"
echo "Admin Email    : $ADMIN_EMAIL"
echo "Admin Password : $ADMIN_PASS"
echo "Database Pass  : $DB_PASS"
echo "--------------------------------------------------------"
echo "SeptaPanel documentation: SERVER_SETUP_GUIDE.md"
echo "========================================================"
