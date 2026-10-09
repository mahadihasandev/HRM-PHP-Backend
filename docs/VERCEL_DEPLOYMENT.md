# Vercel Backend Deployment Guide

This guide details how to deploy the **HRM Laravel 13 API Backend** to **Vercel** serverless functions using the `vercel-php@0.8.0` (PHP 8.4) community runtime and **Supabase PostgreSQL**.

---

## Architecture Summary

| Component | Target / Configuration |
|---|---|
| **Runtime** | `vercel-php@0.8.0` (PHP 8.4 serverless runtime) |
| **Entrypoint** | `api/index.php` (routes through `public/index.php`) |
| **Database** | Managed PostgreSQL on Supabase (port `6543` via PgBouncer transaction pooler) |
| **Filesystem** | Ephemeral `/tmp/storage` (read-only root mitigation) |
| **Logging** | `LOG_CHANNEL=stderr` (streams directly to Vercel Function Logs) |
| **Sessions** | `SESSION_DRIVER=cookie` (stateless client session) or `database` |
| **Cache** | `CACHE_STORE=database` or `array` |

---

## 1. Deploying via Vercel Dashboard (Recommended)

1. Push your repository to GitHub / GitLab / Bitbucket.
2. Go to [vercel.com](https://vercel.com) and click **"Add New..."** > **"Project"**.
3. Import your repository.
4. In the **Configure Project** screen:
   - **Project Name**: e.g., `smart-hrm-backend`
   - **Framework Preset**: Select **`Other`**
   - **Root Directory**: Click **Edit** and choose **`backend`**
   - **Build & Development Settings**: Leave blank (handled by `vercel.json`)
5. Expand **Environment Variables** and enter the production configuration below.
6. Click **Deploy**.

---

## 2. Deploying via Vercel CLI

From your terminal, navigate to the `backend` directory:

```bash
cd backend

# Log in to Vercel (if not logged in)
vercel login

# Deploy preview
vercel

# Deploy to production
vercel --prod
```

---

## 3. Required Environment Variables

Set these in **Project Settings > Environment Variables** on Vercel:

| Variable | Example Value | Description |
|---|---|---|
| `APP_ENV` | `production` | Application environment |
| `APP_DEBUG` | `false` | Disable debug stack traces |
| `APP_KEY` | `base64:...` | Persistent 32-byte key (`php artisan key:generate --show`) |
| `APP_URL` | `https://your-backend.vercel.app` | Backend production HTTPS URL |
| `FORCE_HTTPS` | `true` | Enforce TLS |
| `DB_CONNECTION` | `pgsql` | PostgreSQL driver |
| `DB_HOST` | `ep-small-darkness-b36178y3-pooler.c-4.ap-southeast-1.aws.neon.tech` | Neon pooler host |
| `DB_PORT` | `5432` | PostgreSQL port |
| `DB_DATABASE` | `neondb` | Database name |
| `DB_USERNAME` | `neondb_owner` | Neon user |
| `DB_PASSWORD` | `your-neon-password` | Neon database password |
| `DB_SSLMODE` | `require` | Enforce encrypted SSL DB connection |
| `DB_EMULATE_PREPARES` | `true` | Required for PgBouncer transaction pooling |
| `DB_PERSISTENT` | `false` | Disable persistent connections for serverless |
| `CACHE_STORE` | `database` | Store cache in database `cache` table |
| `SESSION_DRIVER` | `cookie` | Stateless cookie-based sessions |
| `QUEUE_CONNECTION` | `sync` | Synchronous queues |
| `LOG_CHANNEL` | `stderr` | Pipe logs to Vercel logs console |
| `FRONTEND_URL` | `https://your-frontend.vercel.app` | Main frontend URL |
| `CORS_ALLOWED_ORIGINS` | `https://your-frontend.vercel.app,http://localhost:3000` | Allowed origins separated by comma |

---

## 4. Running Database Migrations

Serverless functions on Vercel are ephemeral and spin down when idle. Do not run migrations inside HTTP requests.

Run migrations against Supabase from your local environment or CI/CD:

```bash
# In backend/ directory with Supabase credentials in .env:
php artisan migrate --force

# Or check migration status:
php artisan migrate:status
```

---

## 5. Connecting Frontend to Backend

On your **Vercel Frontend project** (in `frontend/`):

1. Go to **Settings > Environment Variables**.
2. Add / Update:
   ```text
   NEXT_PUBLIC_API_URL=https://your-backend.vercel.app/api/v1
   ```
3. Trigger a redeploy of the frontend so the Next.js client uses the new backend URL.

---

## 6. Verification Checklist

Once deployed:

1. **Health Check Endpoint**:
   ```bash
   curl -i https://your-backend.vercel.app/api/v1/health
   ```
   Expected response:
   ```json
   {
     "status": "success",
     "message": "API operational status",
     "data": {
       "status": "operational",
       "environment": "production",
       "services": {
         "database": { "status": "healthy", "error": null },
         "cache": { "status": "healthy", "driver": "database", "error": null }
       }
     }
   }
   ```

2. **Root Endpoint**:
   ```bash
   curl -i https://your-backend.vercel.app/
   ```
   Expected response:
   ```json
   {
     "name": "HRM Backend",
     "version": "1.0.0",
     "status": "healthy",
     "docs": "https://your-backend.vercel.app/api/v1/health"
   }
   ```

3. **CORS Headers**:
   Verify `access-control-allow-origin` matches your frontend domain when called with `Origin` header.
