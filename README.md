# LiveScores Tips

Football predictions and betting tips website — livescorestips.com.

Built with Laravel 12, Tailwind CSS 4, and Vite.

## Features

- **Predictions** — Daily free predictions by category: 1X2, Over/Under 2.5, BTTS, Double Chance, Correct Score, and more
- **Fixture pages** — Match details with odds from API-Football
- **Blog** — Football betting articles and guides
- **SEO pages** — Catch-all landing pages for keywords (managed via admin)
- **VIP subscriptions** — Paid prediction plans with Flutterwave payment gateway
- **Admin panel** — Full CRUD for predictions, articles, SEO pages, VIP plans, site settings
- **Performance tracking** — Daily/monthly prediction win rates
- **World Cup section** — Dedicated pages during tournaments
- **4 sitemaps** — `main.xml`, `blog.xml`, `seo.xml`, `predictions.xml`

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2
- **Frontend:** Blade templates, Tailwind CSS 4, Vanilla JS
- **Database:** MySQL
- **Build tool:** Vite 7
- **External APIs:** API-Football (odds, fixtures, standings)
- **Payments:** Flutterwave

## Local Setup

```bash
cp .env.example .env
# Edit .env with your DB credentials and API keys
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run dev
php artisan serve
```

### Required API Keys

- `API_FOOTBALL_KEY` — from api-football.com
- `FLW_PUBLIC_KEY` / `FLW_SECRET_KEY` — from Flutterwave

## Deployment

The project includes a `deploy.sh` script. The production server uses Apache with `.htaccess` rewrite rules.

## Sitemaps

Available at:
- `https://livescorestips.com/sitemaps/main.xml`
- `https://livescorestips.com/sitemaps/blog.xml`
- `https://livescorestips.com/sitemaps/seo.xml`
- `https://livescorestips.com/sitemaps/predictions.xml`

## Admin Panel

Accessible at `admin.livescorestips.com`. Login credentials are managed via the `admin_admins` table.
