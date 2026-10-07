# Payroll and factory operations

## Deployment

Deploy the backend and frontend changes together. Run `php artisan migrate --force` using the persistent PostgreSQL connection (the session/direct connection recommended in AGENTS.md), then refresh configuration and route caches. Do not run the demo seeder on production.

The frontend's `NEXT_PUBLIC_API_URL` must point to this backend's `/api/v1` URL. Log in with an existing provisioned employee account; payroll requests use the actual Sanctum token. Headers that claim an administrator identity no longer authorize employee/permission writes. Public registrations receive the General Operations department, and cannot choose a privileged department.

Payroll source files and supporting documents are private. Configure persistent storage for `storage/app/private` before accepting production documents; Render's ordinary deployment filesystem is not persistent. The configurable local root is `PRIVATE_STORAGE_PATH`. Set it to a mounted persistent directory and keep it outside `public/`. Uploads are limited to 10 MB; allow at least 11 MB PHP upload/post size. Do not expose payroll uploads with a public storage symlink. Back up both PostgreSQL and private documents.

Existing employee accounts without a corresponding user must be provisioned through authorized employee registration/account administration. Login never creates an account or changes a stored password on a mismatch.

## Payroll workflow

1. Open Salary & Payroll. Download the CSV template or use an existing XLSX, CSV, TSV, TXT or JSON salary file. Excel import uses the first worksheet; replace formulas with values. TXT must be delimited. JSON must be an array of flat records. Legacy XLS must first be saved as XLSX. Maximum 5,000 rows / 100 columns / 10 MB.
2. Select the salary month and name the batch. Map columns to payroll fields. Employee Full ID and Basic Salary are required. Use full IDs from the employee directory. Store bank account and routing numbers as text to retain leading zeroes. Money and OT hours/rates accept non-negative values with at most two decimals.
3. Enter the forwarding bank, branch, debit account and authorized signatory. These are required when the batch has bank payments. Employee bank details fall back to the directory if omitted from the file.
4. Validate. Employee names come from active company employee records. The server checks duplicate/unknown employees, bank details, numeric values, positive net salary and optional source Net Payable reconciliation. No records are saved while errors remain.
5. Save and review the draft register. The original source is attached when supported. Discard an incorrect draft and re-import; approved and paid batches cannot be discarded.
6. Approve the batch. Earnings, deductions, names and bank details are snapshots and do not change when employee records change later.
7. Print the bank forwarding letter or use the browser's Save as PDF. Export the standard Bank CSV. Cash employees are excluded from bank documents. A bank-specific upload specification may differ; verify the CSV format with your bank before upload.
8. After the bank/cashier confirms payment, record the actual date and reference. Recording payment does not initiate a bank transfer. Print individual payslips or the full register from the stored snapshot.

`net_payable = basic + house rent + medical + conveyance + food + attendance bonus + production bonus + festival bonus + rounded(OT hours × supplied OT rate) − absence − loan − PF − tax − other deductions`.

Rates, bonuses and deductions are explicit company inputs. This module does not assume statutory minimum wages, tax slabs, absence rules or automatic deduction eligibility. Factory grade rates are reference settings and do not overwrite salary files or employee salaries.

Only one batch can include an employee for a given company/month, enforced by a database unique key. Previously paid/approved legacy payslips are also checked when importing. Partial supplemental payroll for the same employee/month is not supported. To correct a draft, discard and import a replacement. Correction of an approved batch requires a separately audited adjustment process.

## Factory operations

Maintain factory units, production lines (with section/capacity), day/night shifts (with breaks), and grade salary/OT references. Mark unused setup records inactive rather than deleting history.

The production register records date, factory, line, order, style, target, completed pieces and rejected pieces. Accepted output = completed − rejected; achievement = accepted / target. One record per company/date/factory/line/order; edit the record as daily totals change. Production data does not automatically calculate payroll bonuses.

The action register records safety, training and maintenance work with a date, factory, owner, notes and open/in-progress/completed status. It is a follow-up register, not a compliance certification or machine integration.

Factory configuration/production/actions require `module.factory`; writes also require `action.employees.edit`. Department defaults mirror employee-directory visibility. Overrides can be granted by an authenticated administrator using the existing permission screen. Payroll visibility requires `module.salary`; imports, approval, payment confirmation and attachment writes also require `action.salary.disburse`. All new data is scoped to the authenticated employee's company; persona/header selection cannot change that identity. The UI lists the latest 100 payroll batches and up to 1,000 factory records per register.

## APIs

All paths below are relative to `/api/v1` (the existing `/api` aliases are also registered).

- `GET /hrm/payroll/runs`: summaries of recent batches; `GET /hrm/payroll/runs/{id}`: full batch details.
- `GET /hrm/payroll/history`: stored previous payslips (no recalculation).
- `POST /hrm/payroll/preview` and `POST /hrm/payroll/runs`: month, title, rows, source_name, company_address, bank_name, bank_branch, debit_account, signatory, signatory_title.
- `POST /hrm/payroll/runs/{id}/action`: `{action: "approve"}` or `{action: "paid", payment_date: "YYYY-MM-DD", payment_reference: "..."}`.
- `DELETE /hrm/payroll/runs/{id}`: unapproved drafts only.
- `POST /hrm/payroll/runs/{id}/attachments`: multipart `file` (PDF/XLSX/CSV/TXT/JSON/JPEG/PNG/DOCX).
- `GET /hrm/payroll/runs/{id}/attachments/{attachment}`: authorized private download.
- `GET /hrm/factory/operations`
- `POST /hrm/factory/records`: discriminated `type` (setup, production, safety); optional company-scoped `id` edits an existing record.

Both API aliases have distinct route names so production route caching succeeds. All new APIs require Sanctum authentication and have a 60 requests/minute throttle.
