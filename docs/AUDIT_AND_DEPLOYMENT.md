# HRM audit and deployment

The audit protects both `/api` and `/api/v1` HR endpoints with Sanctum and active employee context. Employee profiles, directory, permission reads, leave workflows, attendance reads, dashboard and salary statements are scoped to the authenticated employee/company. Browser identity headers cannot grant permissions. Account provisioning is performed by authorized HR staff; public self-registration is disabled. Password changes revoke tokens, and logout revokes the current token.

Removed verification-code disclosure, default employee passwords and invented bank accounts, dashboard metrics, attendance job cards and paid salary statements. Stored payslip amounts are returned unchanged. Employee removal deactivates the employee and revokes access while retaining payroll history. Leave dates and day counts are validated on the server, and recommendation/approval enforce their current state.

## Inherited modules requiring implementation

Legacy accounting, loans, notices, requests/shift exchanges/outwork, sales, tours, training and filter controllers contain demo/global data or unfinished persistence. They return 503 in production. Biometric push returns 501 because it does not store records. Do not connect factory biometric devices until authenticated device ingestion is implemented. The frontend also blocks inherited demo modules unless explicitly enabled for local preview. Company leave allowances and assignment of factory shifts to individual employees remain configuration/integration work; category lists are not a certified leave balance.

## Render deployment

Use the Docker Blueprint. Render has no native PHP runtime; see the [official Blueprint runtime reference](https://render.com/docs/blueprint-spec#runtime). The image uses PHP 8.4 Apache with PostgreSQL, mbstring, ZIP, BCMath and OPcache. The document root is `public`. Credentials, SQLite preview databases and payroll files are excluded from the image.

Set `APP_KEY` to the persistent output of `php artisan key:generate --show`, `APP_URL` to the backend HTTPS origin, `DB_URL` to the PostgreSQL connection URL, and `CORS_ALLOWED_ORIGINS` to the exact frontend origins separated by commas. Preview URLs must be explicitly listed; arbitrary vercel.app sites are not allowed. Supply SMTP credentials and sender address. Database cache/session drivers avoid depending on an unconfigured localhost Redis instance. Startup validates the key, migrates the schema, caches configuration/routes and starts Apache on Render's PORT. Private payroll uploads persist at `/var/data/private` on the attached disk. Back up that disk and database separately. This disk configuration uses one service instance; multi-instance hosting requires shared private object storage.

Set the frontend `NEXT_PUBLIC_API_URL` to `https://YOUR-BACKEND/api/v1` before building. Leave `NEXT_PUBLIC_ENABLE_DEMO_PREVIEWS=false`. Rebuild after changing public environment variables.

Health endpoints return 503 when database/cache checks fail. The Docker image could not be built on the local host because Docker is unavailable; validate the actual container build and deployment logs on Render before serving production traffic. No deployed Render/Vercel environment or real bank transaction was tested.

## Verification

Backend workflow, security and factory tests; route cache; Composer validation and advisory audit; frontend TypeScript, full ESLint, payroll import/transport tests, production build and browser checks. Remaining ESLint warnings are predominantly inherited unused variables/imports and do not block the build. Tests use fake company and payroll data, never production salary files.
