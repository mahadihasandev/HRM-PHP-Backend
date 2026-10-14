# Employee email verification

New employee registration requires an actual email address and proof that its mailbox can receive a code. HR must send a code, ask the employee for the code received in their inbox, verify it, and finish registration within 15 minutes of sending. Sending alone does not prove that an address exists.

## Deployment

1. Deploy the backend and run `php artisan migrate --force` before deploying the frontend.
2. Configure a real delivery mailer on the backend host. The default `log` mailer does not send email and is rejected for this workflow outside automated tests. No code is returned to the browser.
3. For SMTP, set `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_SCHEME`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME` using the sending provider's settings. Use its TLS-enabled SMTP endpoint. Set credentials in the hosting provider's secret environment settings, never in Git or frontend variables. `MAIL_TIMEOUT` defaults to 15 seconds.
4. Verify the sender/domain with the provider and configure its required DNS records. Rebuild Laravel's configuration cache if the deployment uses it (`php artisan config:cache`).
5. Test with an inbox you control: confirm receipt, enter the code, create the employee, and check that their user account is marked verified. A local SMTP capture test validates the transport and UI only; it does not establish real inbox delivery.

## Gmail / Google Workspace

Use these settings in the backend host's environment (replace the address and password privately):

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_TIMEOUT=15
MAIL_USERNAME=your-sending-address@gmail.com
MAIL_PASSWORD=your-google-app-password
MAIL_FROM_ADDRESS=your-sending-address@gmail.com
MAIL_FROM_NAME="HRM"
```

Enable 2-Step Verification and create an app password for HRM in the sending Google account. Use that app password, not the account's normal password. Some managed accounts do not allow app passwords; ask the Workspace administrator to enable an approved sending method in that case. Keep the sender address equal to the authenticated address or a sender alias Google has authorized. Remove any conflicting `MAIL_URL` override.

Official setup references: [Google SMTP settings](https://support.google.com/a/answer/176600?hl=en) and [Google app passwords](https://support.google.com/accounts/answer/185833?hl=en).

Do not commit the populated environment file or share the app password in chat. Until these credentials are configured and a real inbox receives a test code, Gmail delivery is not verified.

## API flow

All three requests require the same authenticated HR user with `action.employees.create` permission:

- `POST /api/v1/auth/send-verification-code` with `email`: sends a six-digit code, valid for 15 minutes. Resend cooldown is 60 seconds.
- `POST /api/v1/auth/verify-code` with `email` and `code`: returns a single-use `verification_token`. Five wrong guesses lock the challenge. The token expires with the original code.
- `POST /api/v1/hrm/employee-create` (or `/hrm/employees`) with employee fields, required `email`, and `email_verification_token`: atomically consumes the proof and creates the employee and verified user account. Client flags such as `is_verified` are not proof.

Codes and proof tokens are stored hashed, bound to the requesting HR user and normalized email. Resending invalidates previous proof. Failed delivery does not leave a newly usable challenge. Existing employees are preserved and are not retroactively verified by this change. The legacy `email_verifications` table is no longer used for new registration.

Validation: `php artisan test` covers required-email enforcement, authorization, delivery failure, hashing, wrong-attempt limits, expiry, resend, proof binding and successful creation.
