# SeptaPanel — Server Setup & Deployment Guide (Debian 11/12 & Ubuntu 22.04 / 24.04 LTS)

Dokumen ini berisi panduan langkah demi langkah dan script setup otomatis untuk mengonfigurasi VPS baru (1 vCPU, 1 GB RAM, Debian 11/12 / Ubuntu 22/24) agar siap menjalankan **SeptaPanel**.

---

## 🛠 Prerequisites Packages & Dependencies

SeptaPanel membutuhkan paket-paket utama berikut pada VPS Debian / Ubuntu:

| Service | Package Name | Fungsi |
| :--- | :--- | :--- |
| **Web Server** | `nginx` | HTTP/HTTPS Web Server |
| **Script Engine** | `php8.3-fpm`, `php8.3-cli` | PHP 8.3 Runtime Engine |
| **Database** | `mariadb-server`, `mariadb-client` | MariaDB RDBMS |
| **SSL Manager** | `certbot`, `python3-certbot-nginx` | Menerbitkan SSL Let's Encrypt |
| **SFTP Service** | `openssh-server` | Remote SFTP file transfer |
| **Package Manager** | `composer`, `git`, `unzip`, `curl` | Manajemen Laravel & Depedensi |

---

## 🚀 Langkah-Langkah Installation di VPS Debian 11 / Debian 12

### Langkah 1: Update & Install Software Stack (Khusus Debian)

Jika VPS Anda menggunakan **Debian 11 (Bullseye)** atau **Debian 12 (Bookworm)**, jalankan perintah berikut sebagai `root`:

```bash
# 1. Hapus PPA Ubuntu yang tidak kompatibel jika sempat ditambahkan
sudo rm -f /etc/apt/sources.list.d/ondrej-*.list

# 2. Update System & Install Dependensi Basic
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip ufw certbot python3-certbot-nginx openssh-server apt-transport-https lsb-release ca-certificates

# 3. Tambahkan Repository Resmi PHP 8.3 khusus Debian (sury.org)
sudo wget -qO /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
DEB_CODENAME=$(cat /etc/os-release | grep VERSION_CODENAME | cut -d= -f2 || echo "buster")
echo "deb https://packages.sury.org/php/ $DEB_CODENAME main" | sudo tee /etc/apt/sources.list.d/php.list

# 4. Update apt & Install PHP 8.3 & Ekstensi
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring \
php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd php8.3-sqlite3

# 5. Install Nginx & MariaDB Server
sudo apt install -y nginx mariadb-server mariadb-client

# 6. Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

### Langkah 2: Setup Database MariaDB untuk SeptaPanel

Jalankan pengamanan MariaDB & buat database untuk panel:

```bash
# Amankan MariaDB
sudo mysql_secure_installation

# Masuk ke MariaDB Console
sudo mysql -u root
```

Di dalam MariaDB Console, jalankan query berikut:

```sql
CREATE DATABASE septapanel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'septa_user'@'localhost' IDENTIFIED BY 'PasswordSeptaPanelyangAman123!';
GRANT ALL PRIVILEGES ON septapanel.* TO 'septa_user'@'localhost';
GRANT ALL PRIVILEGES ON *.* TO 'septa_user'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EXIT;
```

---

### Langkah 3: Setup Izin Privileges Terbatas (`/etc/sudoers.d/septapanel`)

Sesuai spesifikasi keamanan SeptaPanel, Laravel **TIDAK** diberikan akses `sudo` bebas. Akses `sudo` hanya diberikan secara spesifik untuk perintah yang diizinkan saja (`nginx -t`, `systemctl reload nginx`, `certbot`):

Buat file `/etc/sudoers.d/septapanel`:

```bash
sudo nano /etc/sudoers.d/septapanel
```

Isi dengan baris berikut:

```bash
# SeptaPanel Restricted Sudo Rule for www-data
www-data ALL=(ALL) NOPASSWD: /usr/sbin/nginx -t, /usr/bin/systemctl reload nginx, /usr/bin/certbot
```

Simpan file (`Ctrl+O`, `Enter`, `Ctrl+X`) dan atur permission:

```bash
sudo chmod 0440 /etc/sudoers.d/septapanel
```

---

### Langkah 4: Deploy Source Code SeptaPanel

```bash
# 1. Clone / Copy Source Code ke /var/www/septapanel
sudo mkdir -p /var/www/septapanel
sudo chown -R www-data:www-data /var/www/septapanel
cd /var/www/septapanel

# 2. Install Dependensi Composer
sudo -u www-data composer install --no-dev --optimize-autoloader

# 3. Setup File Environment (.env)
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate

# Edit file .env sesuaikan dengan DB MariaDB:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=septapanel
# DB_USERNAME=septa_user
# DB_PASSWORD=PasswordSeptaPanelyangAman123!

# 4. Jalankan Migration & Seeder Akun Default
sudo -u www-data php artisan migrate:fresh --seed --force

# 5. Optimization & Storage Permission
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo chmod -R 775 storage bootstrap/cache
```

---

### Langkah 5: Setup Nginx VirtualHost untuk SeptaPanel Panel

Buat file `/etc/nginx/sites-available/septapanel.conf`:

```nginx
server {
    listen 80;
    server_name panel.domainanda.com; # Ganti dengan domain/IP VPS Anda

    root /var/www/septapanel/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Aktifkan Nginx site & reload:

```bash
sudo ln -s /etc/nginx/sites-available/septapanel.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

### Langkah 6: Pasang SSL untuk Panel Utama & Aktifkan Firewall (UFW)

```bash
# 1. Pasang SSL Let's Encrypt untuk domain panel
sudo certbot --nginx -d panel.domainanda.com --non-interactive --agree-tos -m admin@domainanda.com

# 2. Setup Firewall UFW
sudo ufw allow 22/tcp   # SSH / SFTP
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw enable
```

---

## ⚡ Script Installer Otomatis (`setup-vps.sh`)

Anda juga bisa menggunakan script installer otomatis `setup-vps.sh` yang secara otomatis mendeteksi apakah VPS Anda menggunakan Debian 11/12 atau Ubuntu:

```bash
chmod +x setup-vps.sh
sudo ./setup-vps.sh
```

---

## 🔑 Akun Default Setelah Deploy

- **URL Panel**: `https://panel.domainanda.com`
- **Admin**: `admin@septapanel.local` / `password123`
- **Client**: `client@septapanel.local` / `password123`
