# Namecheap cPanel Deployment Guide: https://license.corevisys.com

Namecheap Shared Hosting / cPanel এ Corevisys License System সেটআপ করার জন্য নিচে সহজ ও স্টেপ-বাই-স্টেপ গাইড দেওয়া হলো।

---

## ১. cPanel এ Subdomain তৈরি করুন

1. Namecheap cPanel এ লগইন করুন।
2. **Domains** সেকশনে গিয়ে **Domains** অথবা **Subdomains** এ ক্লিক করুন।
3. **Create A New Domain** এ ক্লিক করুন:
   - **Domain:** `license.corevisys.com`
   - **Document Root:** ⚠️ **সবচেয়ে গুরুত্বপূর্ণ ধাপ:**
     - এটি দিন: `license.corevisys.com/public` (বা আপনার ইউজারনেম অনুযায়ী `/home/cpaneluser/license.corevisys.com/public`)
     - *নোট: ডিরেক্টরির শেষে অবশ্যই `/public` থাকতে হবে।*
4. **Submit** এ ক্লিক করুন।

---

## ২. PHP ভার্সন সিলেক্ট করুন (PHP 8.2 বা 8.3)

1. cPanel সার্চ বারে লিখুন **Select PHP Version**।
2. বর্তমান PHP ভার্সন পরিবর্তন করে **8.2** অথবা **8.3** সিলেক্ট করে **Set as current** দিন।
3. **Extensions** ট্যাবে গিয়ে নিচের এক্সটেনশনগুলো টিক চিহ্ন দেওয়া আছে কিনা দেখে নিন:
   - `bcmath`
   - `ctype`
   - `curl`
   - `fileinfo`
   - `json`
   - `mbstring`
   - `openssl`
   - `pdo_mysql`
   - `tokenizer`
   - `xml`
   - `zip`

---

## ৩. MySQL ডাটাবেজ তৈরি করুন

1. cPanel থেকে **MySQL® Database Wizard** এ যান।
2. **Step 1:** ডাটাবেজের নাম দিন (যেমন: `cpaneluser_license`) -> *Next Step*।
3. **Step 2:** ডাটাবেজ ইউজারনেম ও স্ট্রং পাসওয়ার্ড দিন (যেমন: `cpaneluser_dbuser` এবং একটি নিরাপদ পাসওয়ার্ড)। পাসওয়ার্ডটি কপি করে সংরক্ষণ করুন -> *Create User*।
4. **Step 3:** **ALL PRIVILEGES** বক্সে টিক দিয়ে *Next Step* এ ক্লিক করুন।

---

## ৪. প্রজেক্ট ফাইল আপলোড (২টি সহজ পদ্ধতি)

### পদ্ধতি ক: cPanel Terminal / SSH দিয়ে (সবচেয়ে দ্রুত ও সহজ)
যদি আপনার cPanel এ **Terminal** এনাবল থাকে:
1. cPanel থেকে **Terminal** ওপেন করুন।
2. কমান্ডগুলো রান করুন:
```bash
cd ~
git clone https://github.com/corevisys/corevisys-license-system.git license.corevisys.com
cd license.corevisys.com

# প্রোডাকশন ডিপেন্ডেন্সি ইনস্টল
composer install --no-dev --prefer-dist --optimize-autoloader
```

### পদ্ধতি খ: Zip ফাইল আপলোড করে (File Manager দিয়ে)
যদি SSH/Terminal না থাকে:
1. আপনার লোকাল কম্পিউটারে প্রোজেক্টের ফ্রন্টএন্ড আগে থেকেই বিল্ড করা আছে (`public/build` ফোল্ডার তৈরি আছে)।
2. লোকাল কম্পিউটারে রান করুন:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. পুরো প্রজেক্টের একটি `.zip` তৈরি করুন (`node_modules` এবং `.git` ফোল্ডার বাদ দিয়ে)।
4. cPanel এর **File Manager** এ গিয়ে `license.corevisys.com` ফোল্ডারে জিপ ফাইলটি আপলোড করে **Extract** করুন।

---

## ৫. প্রোডাকশন `.env` ফাইল কনফিগারেশন

1. cPanel File Manager এ গিয়ে `license.corevisys.com` ফোল্ডারে ঢুকুন।
   *(হিডেন ফাইল দেখতে Settings -> Show Hidden Files (dotfiles) টিক দিন)*
2. `deploy/.env.production.example` ফাইলটির একটি কপি বানিয়ে নাম দিন `.env`।
3. `.env` ফাইলটি এডিট করে নিচের মানগুলো ঠিক করুন:

```ini
APP_NAME="Corevisys License"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://license.corevisys.com

# আপনার cPanel ডাটাবেজ তথ্য
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpaneluser_license
DB_USERNAME=cpaneluser_dbuser
DB_PASSWORD=your_actual_db_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# ইমেইল (Namecheap Webmail বা SMTP)
MAIL_MAILER=smtp
MAIL_HOST=mail.corevisys.com
MAIL_PORT=465
MAIL_USERNAME=no-reply@corevisys.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="no-reply@corevisys.com"
MAIL_FROM_NAME="Corevisys License"

# লাইসেন্স সাইনিং কি (RSA Keys)
LICENSE_SIGNING_PRIVATE_KEY="আপনার_প্রাইভেট_কি"
LICENSE_SIGNING_PUBLIC_KEY="আপনার_পাবলিক_কি"
LICENSE_SIGNING_KEY_ID=corevisys-key-1
LICENSE_SIGNING_ALGORITHM=RSA-SHA256
OFFLINE_VALIDITY_DAYS=7
PULSE_INTERVAL_DAYS=30
PULSE_GRACE_DAYS=7
```

---

## ৬. মাইগ্রেশন ও অপ্টিমাইজেশন রান করা

Terminal এ গিয়ে রান করুন:
```bash
cd ~/license.corevisys.com

# অ্যাপ কি তৈরি
php artisan key:generate

# ডাটাবেজ টেবিল তৈরি
php artisan migrate --force

# স্টোরেজ লিংক তৈরি
php artisan storage:link

# ক্যাশিং অপ্টিমাইজেশন
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

*(যদি Terminal না থাকে, আপনি cPanel Cron Jobs এ গিয়ে এই কমান্ডটি একবার রান করে নিতে পারেন: `cd /home/cpaneluser/license.corevisys.com && php artisan migrate --force`)*

---

## ৭. Namecheap Free SSL (HTTPS) চালু করা

1. cPanel এ গিয়ে সার্চ করুন **SSL/TLS Status**।
2. তালিকায় `license.corevisys.com` দেখতে পাবেন।
3. সিলেক্ট করে **Run AutoSSL** বাটনে ক্লিক করুন।
4. কয়েক মিনিটের মধ্যে Namecheap/cPanel স্বয়ংক্রিয়ভাবে ভ্যালিড SSL সার্টিফিকেট ইনস্টল করে দিবে।

---

## ৮. cPanel Cron Jobs সেটআপ (Scheduler ও Queue)

cPanel এর **Cron Jobs** সেকশনে যান:

### ১) Laravel Task Scheduler (প্রতি মিনিটে):
- **Common Settings:** Once Per Minute (`* * * * *`)
- **Command:**
  ```bash
  /usr/local/bin/php /home/cpaneluser/license.corevisys.com/artisan schedule:run >> /dev/null 2>&1
  ```
  *(নোট: `cpaneluser` এর জায়গায় আপনার cPanel ইউজারনেম বসাবেন)*

### ২) Background Queue Worker (প্রতি মিনিটে বা ৫ মিনিট পর পর):
Namecheap Shared হোস্টিংয়ে ব্যাকগ্রাউন্ডে সবসময় প্রসেস চালু রাখা যায় না, তাই `--stop-when-empty` ফ্ল্যাগ ব্যবহার করা হয়:
- **Common Settings:** Every 5 Minutes (`*/5 * * * *` বা `* * * * *`)
- **Command:**
  ```bash
  /usr/local/bin/php /home/cpaneluser/license.corevisys.com/artisan queue:work --stop-when-empty >> /dev/null 2>&1
  ```

---

## ৯. ভেরিফিকেশন ও লাইভ চেক

ব্রাউজারে নিচের লিংকগুলো ভিজিট করে পরীক্ষা করুন:
- **ওয়েবসাইট:** `https://license.corevisys.com/`
- **হেলথ চেক:** `https://license.corevisys.com/up`
- **লাইসেন্স পাবলিক কি API:** `https://license.corevisys.com/api/v1/license/public-key`
