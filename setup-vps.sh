#!/bin/bash
# SeptaPanel VPS Setup Script for Debian (Buster/Bullseye/Bookworm) & Ubuntu

set -e

echo "=== [1/5] Cleaning up old repository files ==="
rm -f /etc/apt/sources.list.d/ondrej-*.list /etc/apt/sources.list.d/php.list

echo "=== [2/5] Installing base packages ==="
apt update
apt install -y curl wget git unzip ufw certbot python3-certbot-nginx openssh-server gnupg lsb-release ca-certificates apt-transport-https nginx mariadb-server mariadb-client

echo "=== [3/5] Installing PHP 8.3 Repository ==="
if [ -f /etc/debian_version ]; then
    DEB_CODENAME=$(cat /etc/os-release | grep VERSION_CODENAME | cut -d= -f2 || lsb_release -sc || echo "buster")
    echo "Configuring PHP 8.3 for Debian ($DEB_CODENAME)..."
    wget -qO /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
    echo "deb https://packages.sury.org/php/ $DEB_CODENAME main" > /etc/apt/sources.list.d/php.list
else
    echo "Configuring PHP 8.3 for Ubuntu..."
    echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ondrej-php.list
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C 2>/dev/null || true
fi

apt update
apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd php8.3-sqlite3

echo "=== [4/5] Setting up VHost Web Directory & Composer ==="
mkdir -p /var/www/vhosts
chown -R www-data:www-data /var/www/vhosts /var/www
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

echo "=== [5/5] Configuring Sudo Rules & LetsEncrypt Permissions ==="
chmod 755 /etc/letsencrypt /etc/letsencrypt/live /etc/letsencrypt/archive 2>/dev/null || true
echo "www-data ALL=(ALL) NOPASSWD: /usr/sbin/nginx -t, /usr/bin/systemctl reload nginx, /usr/bin/certbot, /bin/cp, /bin/ln, /bin/rm, /usr/bin/cp, /usr/bin/ln, /usr/bin/rm" > /etc/sudoers.d/septapanel
chmod 0440 /etc/sudoers.d/septapanel

ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
echo "y" | ufw enable

echo "=== SeptaPanel Server Setup Complete! ==="
