# BACKEND ARCHITECTURE & DEVELOPMENT GUIDELINES

## Project Overview
This is a high-performance Human Resource Management (HRM) API backend built with **Laravel 13** and PHP 8.4+. It is configured to run in production on **Render** using a managed PostgreSQL database hosted on **Supabase** and in-memory caching powered by **Redis**.

---

## 1. Architectural Principles & Clean Design
Follow Clean Layered Architecture and Domain-Driven Design concepts:
- **Controllers (`app/Http/Controllers/Api/`)**: Thin controllers only. Do not put business logic or direct database queries in controllers. Extend `BaseApiController` and utilize `ApiResponse` trait for standard JSON responses.
- **Form Requests (`app/Http/Requests/`)**: All incoming HTTP input validation must be done in FormRequest classes. Never validate directly inside controller methods.
- **DTOs (`app/DTO/`)**: Use strongly typed Data Transfer Objects extending `BaseDTO` for passing data between Controllers, Services, and Jobs.
- **Services (`app/Services/`)**: Place all business operations, workflows, external API integrations, and transactions in service classes extending `BaseService`.
- **Repositories (`app/Repositories/`)**: Encapsulate database queries inside Eloquent repositories implementing contracts in `app/Repositories/Contracts/`.
- **Resources (`app/Http/Resources/`)**: Format all outgoing API responses with Laravel Eloquent API Resources. Do not expose raw database models directly.
- **DRY Principle**: Never duplicate logic. Use shared traits (`app/Traits/ApiResponse.php`), helpers, and abstract base classes.

---

## 2. Supabase (PostgreSQL) Configuration
- **Driver**: `pgsql` (PDO PostgreSQL).
- **SSL Requirement**: Supabase requires SSL (`DB_SSLMODE=require`).
- **Connection Modes**:
  - **Transaction Pooler (Port 6543)**: Ideal for stateless API requests and serverless environments.
  - **Direct / Session Connection (Port 5432)**: Required for persistent migrations or complex transactions.
- Keep migrations clean, explicit, and index foreign keys properly.

---

## 3. Caching & Redis Strategy
- **Driver**: Redis (`predis` / `phpredis`), configured in `config/database.php` and `config/cache.php`.
- **Service**: Always use `App\Services\Cache\CacheService` for cached data.
- **Tags**: Use Redis tags where appropriate for selective cache invalidation (e.g., `['users', 'departments']`).
- **TTL Conventions**:
  - `TTL_SHORT` (5 mins): Frequent volatile queries.
  - `TTL_MEDIUM` (1 hr): User profiles, permissions.
  - `TTL_LONG` (24 hrs): Static system lookups, countries, roles.

---

## 4. Security & TLS Enforcement
- **Strict Typing**: Every PHP file must declare `declare(strict_types=1);` at the top.
- **TLS/HTTPS**: `ForceHttpsMiddleware` enforces TLS in production environments (`X-Forwarded-Proto`).
- **Security Headers**: `SecurityHeadersMiddleware` automatically injects:
  - `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: DENY`
  - `X-XSS-Protection: 1; mode=block`
  - `Referrer-Policy: strict-origin-when-cross-origin`
- **CORS**: `config/cors.php` is restricted to authorized origins (Vercel production frontend and local development).
- **Authentication**: Laravel Sanctum with token and cookie-based stateful support.
- **Rate Limiting**: Applied to all `/api/v1/*` routes using Laravel throttle middleware.

---

## 5. Deployment Options

### 5.1 Deployment on Render (Docker)
- **Blueprint**: Defined in `render.yaml`.
- **Container**: `Dockerfile` uses PHP 8.4 Apache with OPcache and PostgreSQL enabled.
- **Health Check Route**: `/api/v1/health` verifies database and cache connectivity.
- **Reverse Proxy**: `bootstrap/app.php` trusts reverse proxies (`*`) to correctly determine client IP and TLS scheme.

### 5.2 Deployment on Vercel (Serverless PHP)
- **Configuration**: Defined in `vercel.json` and `.vercelignore`.
- **Runtime**: `vercel-php@0.8.0` (PHP 8.4 serverless runtime).
- **Entrypoint**: `api/index.php` (normalizes `$_SERVER['SCRIPT_NAME']` to `/index.php` and prepares `/tmp` directories).
- **Filesystem**: Dynamically routes storage and view caches to `/tmp/storage` when `VERCEL` environment variable is detected.
- **Guide**: Detailed steps documented in `docs/VERCEL_DEPLOYMENT.md`.

---

## 6. Code Style & Quality Standards
- Write clean, readable, self-documenting code.
- Avoid unnecessary files, dead comments, or boilerplate views (no Blade views for API).
- Run `php artisan test` to ensure all unit and feature tests pass before committing.
