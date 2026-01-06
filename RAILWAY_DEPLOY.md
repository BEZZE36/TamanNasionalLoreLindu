# Railway Deployment Guide - TNLL Explore

## 🚀 Deploy ke Railway

### Step 1: Buat Project di Railway
1. Buka [railway.app](https://railway.app) dan login dengan GitHub
2. Klik **"New Project"** → **"Deploy from GitHub repo"**
3. Pilih repository **TamanNasionalLoreLindu**

### Step 2: Tambahkan MySQL Database
1. Di dashboard project Railway, klik **"+ New"** → **"Database"** → **"MySQL"**
2. Tunggu database selesai di-provision

### Step 3: Set Environment Variables
Klik service app Anda → Tab **"Variables"** → Tambahkan:

```
APP_NAME=TNLL Explore
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-APP.up.railway.app
APP_KEY= (jalankan: php artisan key:generate --show)

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MIDTRANS_SERVER_KEY=Mid-server-xxxxx (key asli Anda)
MIDTRANS_CLIENT_KEY=Mid-client-xxxxx (key asli Anda)
MIDTRANS_IS_PRODUCTION=false
```

### Step 4: Deploy
Railway akan otomatis build dan deploy setelah environment variables diset.

### Step 5: Generate APP_KEY
Jalankan di Railway terminal (atau lokal):
```bash
php artisan key:generate --show
```
Lalu paste hasilnya ke variable `APP_KEY`

## ⚠️ Catatan Penting
- File upload tidak persistent di Railway. Untuk production, gunakan **Cloudinary** atau **AWS S3**
- Database Railway gratis terbatas, pertimbangkan upgrade untuk production
