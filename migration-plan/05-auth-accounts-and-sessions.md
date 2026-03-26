# 05 Auth, Accounts, and Sessions

## Current State

- `index.php` (login page)
- `register.php`
- `aktywacja.php` (account activation)
- `logout.php`
- `account.php`
- `preset.php`
- `reset.php`
- `referrals.php`
- `includes/sessions.php`
- `includes/verifymail.php`
- `includes/verifypass.php`
- `includes/avatars.php` (avatar scaling/upload)
- `mailer/mailerconfig.php`
- `mailer/mailerconfig.php`

## Why This Module Exists

Port all identity and account lifecycle flows while preserving legacy behavior where needed for a staged cutover.

## Target Rust Shape

- `crates/web/src/handlers/auth.rs` — Login, logout, registration, activation, password-reset handlers.
- `crates/web/src/handlers/account.rs` — Account settings, profile, preset, referrals.
- `crates/domain/src/auth.rs` — Password verification (MD5 compat + Argon2 upgrade), token generation.
- `crates/domain/src/account.rs` — Registration validation, activation, password-reset domain logic.
- `crates/data/src/auth.rs` — User credential queries, session store, activation/reset-token queries.
- `crates/web/src/middleware/session.rs` — PostgreSQL-backed session middleware with flash data.

## Module Dependencies

- 01 Platform Foundations (config for secrets, email settings).
- 02 Database and PostgreSQL (player/auth tables, pool).
- 03 HTTP Routing and Middleware (request context, guards).
- 04 Rendering, Assets, and Localization (form components, templates).

## Risks and Notes

- Legacy passwords are MD5. A compatibility check with transparent Argon2 rehash on login is required.
- PHP session state is used by chat, combat, and missions. The Rust session must support mutable server-side state.
- `mailer/mailerconfig.php` provides email settings for activation and password reset; this must be mapped to Rust config.

## Tasks

### MP-05-01: Port login and logout with compatibility hashing ✅

- Description: Implement the login and logout flow, including compatibility with legacy MD5 password hashes and a transparent upgrade path to Argon2.
- Estimate: 2h
- Depends on: MP-01-02, MP-02-02, MP-03-03.
- Functional acceptance criteria:
  - Existing accounts can log in with legacy credentials.
  - Successful MD5 logins trigger an Argon2 rehash and stored-password upgrade.
  - Logout clears all server-side session state.
- Technical notes: Keep the compatibility branch short-lived and observable.
- In scope: Authentication check, session start, logout.
- Out of scope: Registration and account recovery.

### MP-05-02: Port registration validation and account creation ✅

- Description: Recreate the registration form, email validation, username uniqueness checks, and activation record creation.
- Estimate: 1.5h
- Depends on: MP-04-05, MP-05-01.
- Functional acceptance criteria:
  - Validation rules match current behavior closely enough to avoid accidental account rejection or acceptance drift.
  - Pending registrations are stored in PostgreSQL.
  - The flow records referral linkage when present.
- Technical notes: Prefer explicit server-side validation over recreating legacy helper functions blindly.
- In scope: Registration inputs and persistence.
- Out of scope: Activation email delivery.

### MP-05-03: Port activation and lost-password flows ✅

- Description: Rebuild account activation, password reset request, reset token verification, and password replacement.
- Estimate: 2h
- Depends on: MP-05-02.
- Functional acceptance criteria:
  - Activation links can confirm a pending account.
  - Password reset uses time-bounded tokens rather than reusing the legacy table semantics unchanged.
  - Success and failure pages match expected user behavior.
- Technical notes: Preserve the route behavior; improve token safety under the hood.
- In scope: Activation and password recovery flows.
- Out of scope: Account profile editing.

### MP-05-04: Port account settings and profile management ✅

- Description: Migrate the authenticated account page, profile edits, contact fields, preferences, and avatar-related metadata handling.
- Estimate: 1.5h
- Depends on: MP-05-01, MP-06-02.
- Functional acceptance criteria:
  - A logged-in player can view and update supported account fields.
  - Preference changes persist in the new settings model.
  - Validation errors render without partial state corruption.
- Technical notes: Keep user-uploaded asset storage outside the embedded asset system.
- In scope: Account and profile settings.
- Out of scope: Public profile presentation.

### MP-05-05: Port preset, reset, and referral screens ✅

- Description: Recreate the ancillary account routes for presets, character reset mechanics, and referral visibility.
- Estimate: 1.5h
- Depends on: MP-05-01, MP-06-01.
- Functional acceptance criteria:
  - Preset and reset actions are gated by the same conditions as the current PHP flows.
  - Referral data is viewable in the account area.
  - Each action is auditable in logs.
- Technical notes: Keep destructive actions behind explicit confirmation pages.
- In scope: Preset/reset/referral web flows.
- Out of scope: Era-wide reset tooling.

### MP-05-06: Replace PHP session semantics with explicit Rust session handling ✅

- Description: Implement server-side session storage that preserves the app's reliance on mutable session state for chat tabs, combat flags, and mission state.
- Estimate: 1.5h
- Depends on: MP-05-01, MP-03-03.
- Functional acceptance criteria:
  - Session storage survives normal application restarts if configured to use PostgreSQL.
  - Flash messages and page transitions work without global mutable PHP state.
  - Session fixation defenses exist on login.
- Technical notes: This is a foundational compatibility task for later modules.
- In scope: Session middleware, cookies, flash data.
- Out of scope: Feature-specific session payloads.