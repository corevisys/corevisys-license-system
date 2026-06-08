<p align="center">
  <img src="public/storage/logo/Main-Logo.png" width="300" alt="CoreVisys Logo">
</p>

# CoreVisys — Enterprise Software Agency

Welcome to the official repository for **CoreVisys**, a premium enterprise software development agency website. This platform showcases our custom software solutions, mobile app development, AI & machine learning capabilities, and cloud architecture services.

## 🚀 Tech Stack

The platform is built with a modern, high-performance tech stack:

- **Backend:** [Laravel](https://laravel.com/) (PHP)
- **Frontend:** [Vue.js 3](https://vuejs.org/) powered by [Inertia.js](https://inertiajs.com/)
- **Styling:** [Tailwind CSS](https://tailwindcss.com/)
- **Build Tool:** [Vite](https://vitejs.dev/)

## 📂 Project Structure

- `app/` - Core Laravel backend logic (Controllers, Models, Middleware).
- `resources/js/` - Vue.js frontend components, layouts, and pages.
  - `Pages/` - Main views (Welcome, Services, Case Study, Contact, etc.).
  - `Components/` - Reusable UI elements and generic components.
- `public/` - Compiled CSS/JS assets, fonts, and the main entry point.
- `storage/` - User-uploaded files (like the site logo) and application logs.

## ⚙️ Installation & Setup

To run this project locally, follow these steps:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/corevisys-site.git
   cd corevisys-site
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies:**
   ```bash
   npm install
   ```

4. **Environment Setup:**
   Duplicate the `.env.example` file and rename it to `.env`, then generate the application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Link Storage:**
   Ensure that the public disk is accessible by creating a symbolic link:
   ```bash
   php artisan storage:link
   ```

6. **Run Migrations (If applicable):**
   ```bash
   php artisan migrate
   ```

7. **Start the Development Servers:**
   You will need two terminal windows to run both the backend and frontend simultaneously.
   
   Terminal 1 (Laravel Backend):
   ```bash
   php artisan serve
   ```
   
   Terminal 2 (Vite Frontend):
   ```bash
   npm run dev
   ```

## 🌐 Building for Production

When you are ready to deploy the site to a production server, compile the frontend assets:

```bash
npm run build
```

Then clear all caches for optimal performance:
```bash
php artisan optimize:clear
```

## 📄 License

This project is proprietary and confidential. All rights reserved by [CoreVisys](https://corevisys.com/).
