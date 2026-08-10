# SeptaPanel — Custom Lightweight VPS Hosting Control Panel

**SeptaPanel** adalah custom web hosting control panel berbasis Laravel 11, Blade, Tailwind CSS, dan Alpine.js yang dioptimalkan untuk VPS spesifikasi ringan (1 vCPU, 1 GB RAM, SSD/NVMe, Ubuntu 22.04/24.04 atau Debian 11/12).

Repository: [https://github.com/ynzynnn/webhostingpanel](https://github.com/ynzynnn/webhostingpanel)

---

## ✨ Fitur Utama

- **Security Isolation & Zero Full Sudo Rule**: Proses Laravel tidak diberi akses `sudo` bebas. Eksekusi privilege (Nginx reload, Certbot, FPM pool) dikontrol secara ketat.
- **Clean Minimalist Dashboard UI**: Desain card-box modern yang bersih, responsif, dan mudah digunakan.
- **Website Provisioning Engine**:
  - Pembuatan Linux System User khusus per website.
  - Document root terisolasi (`/home/username/public_html`) & log directory (`/home/username/logs`).
  - Pembuatan PHP-FPM Pool (`pm = ondemand`) & VirtualHost Nginx otomatis.
  - **Syntax Validation (`nginx -t`) & Automatic Rollback Engine** jika terjadi kesalahan konfigurasi.
- **Auto SSL (Let's Encrypt / Certbot)**: Penerbitan dan pemasangan SSL otomatis 1-click.
- **Role-Based Access Control (RBAC)**: Role Admin & Client dengan hak akses yang aman.
- **Lightweight System Monitoring**: Resource meter CPU, RAM, Disk, Server Uptime, dan service probe Nginx, PHP-FPM, MariaDB yang di-cache 15 detik.
- **Audit Logging**: Jejak audit aktivitas keamanan dan login pengguna.

---

## ⚡ Instalasi Cepat di VPS (Ubuntu / Debian)

Jalankan perintah 1-baris berikut pada VPS Anda sebagai `root`:

```bash
curl -sSL https://raw.githubusercontent.com/ynzynnn/webhostingpanel/main/setup-vps.sh | bash
```

Atau ikuti panduan lengkap di [SERVER_SETUP_GUIDE.md](SERVER_SETUP_GUIDE.md).

---

## 🔑 Akun Default Seeder

- **Admin**: `admin@septapanel.local` / `password123`
- **Client**: `client@septapanel.local` / `password123`
