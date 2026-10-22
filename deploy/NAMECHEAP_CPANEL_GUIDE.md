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

## ৫. স্বয়ংক্রিয় ইনস্টলেশন (২টি অত্যন্ত সহজ উপায় - কোনো ম্যানুয়াল কোডিং/এডিটিং ছাড়া)

### উপায় ১: ব্রাউজার ওয়েব ইনস্টলার দিয়ে (সবচেয়ে সহজ ও রিকমেন্ডেড ⭐)
ফাইল আপলোড শেষে কোনো ফাইল এডিট করতে হবে না:
1. ব্রাউজারে ভিজিট করুন: **`https://license.corevisys.com/install`** (বা সরাসরি `https://license.corevisys.com`)
2. স্ক্রিনে সুন্দর ওয়েব ইনস্টলেশন উইজার্ড দেখতে পাবেন।
3. আপনার cPanel এ তৈরি করা ডাটাবেজ নাম (`Database Name`), ইউজারনেম ও পাসওয়ার্ড দিন।
4. **Test Connection** বাটনে ক্লিক করে কানেকশন ঠিক আছে কিনা নিশ্চিত করুন।
5. আপনার পছন্দের অ্যাডমিন নাম, ইমেইল এবং স্ট্রং পাসওয়ার্ড দিন।
6. **Complete Installation & Initialize Server** বাটনে ক্লিক করুন।
   - *সিস্টেম স্বয়ংক্রিয়ভাবে `.env` ফাইল তৈরি করবে, শক্তিশালী অ্যাপ্লিকেশন এনক্রিপশন কি তৈরি করবে, লাইসেন্স সাইনিং কি (RSA-2048) ও সিক্রেট পেপার জেনারেট করবে, ডাটাবেজ টেবিল মাইগ্রেট করবে এবং অ্যাডমিন অ্যাকাউন্ট সেটআপ করে ফেলবে।*
7. কাজ শেষ! সরাসরি লগইন স্ক্রিনে নিয়ে যাবে।

---

### উপায় ২: cPanel Terminal দিয়ে (ইন্টারেক্টিভ কমান্ড)
যদি আপনি টার্মিনাল ব্যবহার করতে পছন্দ করেন:
```bash
cd ~/license.corevisys.com
php artisan corevisys:install
```
- কমান্ডটি রান করার পর আপনাকে ডাটাবেজ হোস্ট, ডাটাবেজের নাম, ইউজারনেম এবং পাসওয়ার্ড জিজ্ঞেস করবে।
- তথ্য প্রদান করলে এটি স্বয়ংক্রিয়ভাবে `.env` তৈরি করবে, সব কি (RSA ও Pepper) জেনারেট করবে এবং মাইগ্রেশন রান করে ইন্সটলেশন কমপ্লিট করবে।

---

## ৬. Namecheap Free SSL (HTTPS) নিশ্চিত করা

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
