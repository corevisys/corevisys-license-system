# Production Deployment Guide: https://license.corevisys.com

এই গাইডটি অনুসরণ করে Corevisys License System খুব সহজে আপনার লাইভ সার্ভারে (`https://license.corevisys.com`) ডিপ্লয় ও রান করতে পারবেন।

---

## ১. সার্ভারের প্রাথমিক রিকোয়ারমেন্টস (Server Prerequisites)
- **OS:** Ubuntu 22.04 / 24.04 LTS অথবা যেকোনো লিনাক্স / cPanel সার্ভার
- **PHP:** 8.2 বা 8.3 (প্রয়োজনীয় এক্সটেনশন: `php-fpm`, `php-mysql`, `php-xml`, `php-curl`, `php-mbstring`, `php-zip`, `php-bcmath`, `php-openssl`)
- **Database:** MySQL 8.0+ অথবা MariaDB 10.5+
- **Web Server:** Nginx (সুপারিশকৃত) অথবা Apache
- **Composer:** 2.x
- **Node.js & NPM:** v18+ বা v20+ (সার্ভারে Vite বিল্ড করার জন্য)
- **SSL:** Let's Encrypt (Certbot)

---

## ২. DNS রেকর্ড কনফিগারেশন (DNS Pointing)
আপনার ডোমেইন প্রোভাইডার (Cloudflare, Namecheap, cPanel ইত্যাদি) এর DNS ম্যানেজমেন্টে যান:
- **Type:** `A`
- **Name / Host:** `license` (পূর্ণাঙ্গ: `license.corevisys.com`)
- **Value / Target:** আপনার সার্ভারের পাবলিক আইপি (`Your Server IP`)
- **TTL:** Auto বা 5 minutes

---

## ৩. সার্ভারে প্রজেক্ট ক্লোন বা আপলোড
সার্ভারে লগইন করুন (SSH) এবং প্রজেক্ট ডিরেক্টরিতে যান:

```bash
cd /var/www
sudo git clone https://github.com/corevisys/corevisys-license-system.git license.corevisys.com
cd /var/www/license.corevisys.com
```

---

## ৪. পারমিশন ও ফাইল ওনারশিপ (File Permissions)
```bash
sudo chown -R www-data:www-data /var/www/license.corevisys.com
sudo find /var/www/license.corevisys.com -type f -exec chmod 644 {} \;
sudo find /var/www/license.corevisys.com -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/license.corevisys.com/storage /var/www/license.corevisys.com/bootstrap/cache
```

---

## ৫. ডিপেন্ডেন্সি ইন্সটল ও অ্যাসেট বিল্ড (Composer & Vite)
```bash
# Composer Production Install
composer install --no-dev --prefer-dist --optimize-autoloader

# Frontend Vite Assets Build
npm ci
npm run build
```

---

## ৬. প্রোডাকশন এনভায়রনমেন্ট ফাইল (.env)
```bash
cp deploy/.env.production.example .env
nano .env
```
`.env` ফাইলে নিচের তথ্যগুলো নিশ্চিত করুন:
1. `APP_ENV=production`
2. `APP_DEBUG=false`
3. `APP_URL=https://license.corevisys.com`
4. ডাটাবেজ তথ্য (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
5. লাইসেন্স সাইনিং কি (`LICENSE_SIGNING_PRIVATE_KEY` ও `LICENSE_SIGNING_PUBLIC_KEY`)

এরপর নতুন Application Encryption Key তৈরি করুন এবং মাইগ্রেশন চালান:
```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
```

---

## ৭. Nginx ও SSL (Certbot) কনফিগারেশন
১. Nginx কনফিগ ফাইল কপি করুন:
```bash
sudo cp deploy/nginx/license.corevisys.com.conf /etc/nginx/sites-available/license.corevisys.com.conf
sudo ln -s /etc/nginx/sites-available/license.corevisys.com.conf /etc/nginx/sites-enabled/
```

২. SSL সার্টিফিকেট তৈরি করুন (Let's Encrypt):
```bash
sudo certbot --nginx -d license.corevisys.com
```

৩. Nginx কনফিগারেশন টেস্ট করে রিলোড দিন:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## ৮. প্রোডাকশন অপ্টিমাইজেশন ক্যাশিং (Laravel Optimize)
উৎকৃষ্ট পারফরম্যান্স ও দ্রুত রেসপন্সের জন্য ক্যাশ তৈরি করুন:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## ৯. ব্যাকগ্রাউন্ড কিউ (Queue) ও ক্রন শিডিউলার (Cron)
### ক্রন জব যুক্ত করুন (`crontab -e -u www-data`):
```cron
* * * * * cd /var/www/license.corevisys.com && php artisan schedule:run >> /dev/null 2>&1
```

### Supervisor (Queue Worker):
```bash
sudo cp deploy/supervisord.conf /etc/supervisor/conf.d/license-worker.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## ১০. নিয়মিত আপডেট বা ডিপ্লয়মেন্ট (One-Click Deploy Script)
ভবিষ্যতে GitHub থেকে আপডেট নামাতে এক কমান্ডেই ডিপ্লয় করতে পারবেন:
```bash
chmod +x deploy/deploy.sh
./deploy/deploy.sh
```

---

## ১১. ভেরিফিকেশন ও হেলথ চেক (Health Check URLs)
ডিপ্লয়মেন্ট সফল হয়েছে কিনা তা যাচাই করার লিংকগুলো:
- **ওয়েবসাইট হোম:** `https://license.corevisys.com/`
- **Laravel Health Check:** `https://license.corevisys.com/up`
- **Public Signing Key Endpoint:** `https://license.corevisys.com/api/v1/license/public-key`
