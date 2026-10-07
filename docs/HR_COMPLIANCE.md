# HR and compliance workspace

The new HR & compliance module adds six persisted workflows that were missing from the garment HRM. Priorities were chosen after reviewing [Better Work Bangladesh training and HR guidance](https://betterwork.org/bangladesh/our-services/), [ILO garment-sector grievance guidance (2025)](https://researchrepository.ilo.org/esploro/outputs/encyclopediaEntry/Promoting-and-strengthening-effective-grievance-mechanisms/995700671202676), and [ILO guidance on safety training in Bangladesh garments](https://www.ilo.org/resource/article/bangladesh-improving-safety-garment-industry). Research reviewed on 7 October 2026. These sources support the priorities; this software does not certify legal compliance.

## Available workflows

- Worker documents: appointment letters, contracts, ID records and certificates; issue and optional expiry dates; pending/verified/expired/archived states; private supporting files. Upcoming expiries appear in the dashboard; overdue dates are flagged in the list.
- Shift roster: one employee per assignment, factory, shift, optional production line and inclusive start/end dates. Active assignments cannot overlap for a worker. Cancel the old assignment before replacing its dates. Factory/shift/line selections use existing factory setup. The current-shift attendance endpoint returns the saved assignment or null.
- Training and skills: induction, fire safety, first aid, worker rights, harassment prevention and skills topics; trainer/location; selected workers; registered/attended/absent attendance. Planned sessions cannot claim attendance. Individual workers see only their own attendance.
- Grievances: workers submit concerns about wages, harassment, safety or welfare. Reports belong to the signed-in worker; normal workers cannot change response/status. Only independently authorized grievance handlers can review and resolve other workers' concerns. Resolution requires a written response. Identities are visible to handlers; this is not an anonymous reporting channel.
- Safety incidents: workers report hazards, near misses, injuries, fire or equipment incidents by factory. HR managers assign an owner and corrective action, with follow-up dates and open/in-progress/completed tracking. Closing an incident requires an owner and corrective action. Use the factory emergency procedure for immediate danger.
- Holiday calendar: publish public/festival/weekly/company closures, dates and paid/unpaid designation. Dates are company-configured. Holiday records do not automatically alter attendance, leave deductions or salary calculations.

## Authorization and privacy

`module.people` permits the workspace. Active workers have self-service access by default, including departments absent from the legacy department catalog. `action.people.manage` permits company HR management. `action.people.grievances` independently permits reading and updating company grievances. Human Resources and Administration have both actions by default; all other existing department profiles default to false. Explicit employee permission overrides take precedence, including revocation. Assign sensitive permissions deliberately through the existing access manager.

Workers can read their own documents, rosters, training attendance, grievances and incident reports, and company holidays. Other workers' records cannot be opened by guessing IDs. HR management does not bypass a revoked grievance-handling permission. All queries are company-scoped.

Private attachments are supported only on worker document records, up to 20 files per record and 10 MB per file, restricted to PDF/JPEG/PNG/DOCX/TXT. Storage uses the existing private `local` disk and `PRIVATE_STORAGE_PATH`. Responses omit storage paths. Downloads require authentication and record visibility; there is no public URL. Preserve private storage across deployments and include it in backups. The module records create/update/upload events with the actual actor, status and version. It is a change history, not a full forensic before/after snapshot.

## API and rollout

Both `/api/v1/hrm/people` and `/api/hrm/people` support:

- `GET /overview`: permitted statistics, manager employee options, active factory setup and capabilities.
- `GET /records?kind=document&page=1&q=&status=`: 25 records per page, scoped search/status filter.
- `GET /records/{id}`: scoped record, files and history.
- `POST /records`: typed record payload, with `id` and current `version` for updates. A stale version returns 409; record kind cannot change. Workers can create their own grievance or incident, but only authorized handlers/managers can update it.
- `POST /records/{id}/files`: multipart document attachment.
- `GET /records/{id}/files/{file}`: authorized download.

Deploy the backend migration before the frontend: `php artisan migrate --force`. No new third-party dependencies or environment keys are required. Do not enable legacy demo previews in production. The old Training/Requests controllers remain guarded in production; these new workflows use PeopleController.

Rosters report assigned shifts but do not change the inherited attendance lateness/overtime calculations. Payroll remains based on reviewed/imported amounts. Holiday and training records do not submit payments, notify employees or contact outside parties. Expiry and follow-up reminders are in-app counters and flags, not scheduled email/SMS.

Remaining priorities for separate implementation: configurable leave entitlement and accrual policies; approved attendance corrections and overtime; recruitment/onboarding tasks; employee separation/final settlements; advances and repayment ledgers; automated notification delivery. Statutory wage/leave/benefit formulas need verified current company policy and jurisdiction before automation.

## Verification

Backend regression tests cover authenticated identity, company isolation, explicit permission revocation, private file access/type validation, shift overlap and cancellation, missing required dates, cross-company participants, participant attendance privacy, current shift, stale versions, incident closure, and saved-record dashboard counts. Tests use SQLite; production PostgreSQL behavior still requires deployment verification.

Local verification: 26 backend tests / 256 assertions passed; routes cache and clear succeeded. The browser saved and edited a worker document, showed the expiry counter, preserved a training form on validation failure, saved selected participants, and downloaded the exact 53-byte private preview attachment. No production deployment was performed.
