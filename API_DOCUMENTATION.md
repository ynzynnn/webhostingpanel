# 📖 SeptaPanel RESTful API v1 Documentation

Dokumentasi resmi pengintegrasian SeptaPanel RESTful API v1 untuk Website Utama, Billing System (WHMCS), atau Aplikasi Kustom.

---

## 🔑 1. Autentikasi (Authentication)

Setiap request ke endpoint REST API v1 wajib menyertakan **API Key** yang telah digenerate pada menu **API Keys** di SeptaPanel.

### Metode Autentikasi yang Didukung:
1. **HTTP Header `X-API-Key`** *(Direkomendasikan)*:
   ```http
   X-API-Key: septa_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```
2. **HTTP Header `Authorization` (Bearer Token)**:
   ```http
   Authorization: Bearer septa_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```
3. **Query Parameter URL**:
   ```http
   GET https://cp.septacloud.net/api/v1/websites?api_key=septa_xxxxxxxx
   ```

---

## 🌐 2. Base URL & Format Respon Standard

- **Base URL**: `https://cp.septacloud.net/api/v1`
- **Content-Type**: `application/json`

### Format Respon Sukses (HTTP 200 / 201):
```json
{
  "success": true,
  "message": "Pesan deskripsi respon",
  "data": { ... }
}
```

### Format Respon Gagal (HTTP 400 / 401 / 403 / 422):
```json
{
  "success": false,
  "message": "Pesan deskripsi error",
  "errors": { ... }
}
```

---

## 🚀 3. Daftar Endpoint API

### 🌐 3.1 Website Management API

#### A. Provisi Website Baru
Otomatis membuat website, isole Linux user (`site_xxx`), UNIX socket PHP-FPM, VirtualHost Nginx, dan menerbitkan SSL Certbot.

- **Method**: `POST`
- **Endpoint**: `/api/v1/websites`
- **Request Body (JSON)**:
  ```json
  {
    "domain_name": "clientbaru.com",
    "php_version": "8.3",
    "client_email": "budi@gmail.com",
    "enable_auto_ssl": true
  }
  ```
- **Response (HTTP 201)**:
  ```json
  {
    "success": true,
    "message": "Website clientbaru.com berhasil diprovisi dengan Linux user site_clientbaru_ab12.",
    "data": {
      "id": 18,
      "domain_name": "clientbaru.com",
      "system_user": "site_clientbaru_ab12",
      "document_root": "/var/www/vhosts/site_clientbaru_ab12/public_html",
      "php_version": "8.3",
      "status": "active"
    }
  }
  ```

#### B. Ambil Seluruh Daftar Website
- **Method**: `GET`
- **Endpoint**: `/api/v1/websites`

#### C. Detail Website Spesifik
- **Method**: `GET`
- **Endpoint**: `/api/v1/websites/{id}`

#### D. Suspend / Aktifkan Kembali Website
- **Method**: `POST`
- **Endpoint**: `/api/v1/websites/{id}/suspend`

#### E. Terbitkan SSL Let's Encrypt Remotely
- **Method**: `POST`
- **Endpoint**: `/api/v1/websites/{id}/issue-ssl`

#### F. Hapus Website
- **Method**: `DELETE`
- **Endpoint**: `/api/v1/websites/{id}`

---

### 👥 3.2 Client Management API

#### A. Buat Akun Client Baru
- **Method**: `POST`
- **Endpoint**: `/api/v1/clients`
- **Request Body (JSON)**:
  ```json
  {
    "name": "Budi Client",
    "email": "budi@gmail.com",
    "password": "PasswordAman123!",
    "max_websites": 5
  }
  ```

#### B. Ambil Daftar Client
- **Method**: `GET`
- **Endpoint**: `/api/v1/clients`

#### C. Ubah Quota Batas Maksimal Website Client
- **Method**: `PUT`
- **Endpoint**: `/api/v1/clients/{user_id}/quota`
- **Request Body (JSON)**:
  ```json
  {
    "max_websites": 10
  }
  ```

---

### 🗄️ 3.3 Database Management API

#### A. Buat Database MariaDB & User
- **Method**: `POST`
- **Endpoint**: `/api/v1/databases`
- **Request Body (JSON)**:
  ```json
  {
    "db_name": "wpdb",
    "password": "DatabasePass123!",
    "website_id": 18
  }
  ```

#### B. Ambil Daftar Database
- **Method**: `GET`
- **Endpoint**: `/api/v1/databases`

#### C. Hapus Database
- **Method**: `DELETE`
- **Endpoint**: `/api/v1/databases/{id}`

---

### 📊 3.4 System Health & Monitoring API

#### A. Ambil Status & Statistik Server VPS
- **Method**: `GET`
- **Endpoint**: `/api/v1/system/status`
- **Response (HTTP 200)**:
  ```json
  {
    "success": true,
    "message": "Status server SeptaPanel berhasil diambil.",
    "data": {
      "server_name": "cp.septacloud.net",
      "os": "Linux",
      "php_version": "8.3.33",
      "cpu_load_1min": 0.15,
      "disk_usage": {
        "total_gb": 50,
        "free_gb": 32.5,
        "used_percent": 35
      },
      "statistics": {
        "total_websites": 12,
        "total_clients": 5,
        "total_databases": 8
      }
    }
  }
  ```

---

## 💻 4. Contoh Kode Integrasi

### 🐘 A. PHP (cURL / Guzzle)
```php
<?php
$apiKey = "septa_YOUR_API_KEY_HERE";
$endpoint = "https://cp.septacloud.net/api/v1/websites";

$data = [
    "domain_name"  => "domainclient.com",
    "php_version"  => "8.3",
    "client_email" => "client@domain.com"
];

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-API-Key: {$apiKey}",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
```

---

### 🟩 B. Node.js (Axios)
```javascript
const axios = require('axios');

const apiKey = 'septa_YOUR_API_KEY_HERE';
const endpoint = 'https://cp.septacloud.net/api/v1/websites';

axios.post(endpoint, {
    domain_name: 'domainclient.com',
    php_version: '8.3',
    client_email: 'client@domain.com'
}, {
    headers: {
        'X-API-Key': apiKey,
        'Content-Type': 'application/json'
    }
})
.then(response => console.log(response.data))
.catch(error => console.error(error.response.data));
```

---

### 🐍 C. Python (`requests`)
```python
import requests

api_key = "septa_YOUR_API_KEY_HERE"
endpoint = "https://cp.septacloud.net/api/v1/websites"

headers = {
    "X-API-Key": api_key,
    "Content-Type": "application/json"
}

payload = {
    "domain_name": "domainclient.com",
    "php_version": "8.3",
    "client_email": "client@domain.com"
}

response = requests.post(endpoint, json=payload, headers=headers)
print(response.json())
```
