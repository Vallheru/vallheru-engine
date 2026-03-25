# 05 Auth, Accounts, and Sessions

## Source Surface

- `index.php`
- `register.php`
- `aktywacja.php`
- `logout.php`
- `account.php`
- `preset.php`
- `reset.php`
- `referrals.php`
- `includes/sessions.php`
- `includes/verifymail.php`
- `includes/verifypass.php`
- `mailer/mailerconfig.php`

## Goal

Port all identity and account lifecycle flows while preserving legacy behavior where needed for a staged cutover.

## Tasks

### MP-05-01: Port login and logout with compatibility hashing

- Description: Implement the login and logout flow, including compatibility with legacy MD5 password hashes and a transparent upgrade path to Argon2.
- Estimated time: 2h
- Dependencies: MP-01-02, MP-02-02, MP-03-03.
- Acceptance criteria:
  - Existing accounts can log in with legacy credentials.
  - Successful MD5 logins trigger an Argon2 rehash and stored-password upgrade.
  - Logout clears all server-side session state.
- Technical notes: Keep the compatibility branch short-lived and observable.
- In scope: Authentication check, session start, logout.
- Out of scope: Registration and account recovery.

### MP-05-02: Port registration validation and account creation

- Description: Recreate the registration form, email validation, username uniqueness checks, and activation record creation.
- Estimated time: 1.5h
- Dependencies: MP-04-05, MP-05-01.
- Acceptance criteria:
  - Validation rules match current behavior closely enough to avoid accidental account rejection or acceptance drift.
  - Pending registrations are stored in PostgreSQL.
  - The flow records referral linkage when present.
- Technical notes: Prefer explicit server-side validation over recreating legacy helper functions blindly.
- In scope: Registration inputs and persistence.
- Out of scope: Activation email delivery.

### MP-05-03: Port activation and lost-password flows

- Description: Rebuild account activation, password reset request, reset token verification, and password replacement.
- Estimated time: 2h
- Dependencies: MP-05-02.
- Acceptance criteria:
  - Activation links can confirm a pending account.
  - Password reset uses time-bounded tokens rather than reusing the legacy table semantics unchanged.
  - Success and failure pages match expected user behavior.
- Technical notes: Preserve the route behavior; improve token safety under the hood.
- In scope: Activation and password recovery flows.
- Out of scope: Account profile editing.

### MP-05-04: Port account settings and profile management

- Description: Migrate the authenticated account page, profile edits, contact fields, preferences, and avatar-related metadata handling.
- Estimated time: 1.5h
- Dependencies: MP-05-01, MP-06-02.
- Acceptance criteria:
  - A logged-in player can view and update supported account fields.
  - Preference changes persist in the new settings model.
  - Validation errors render without partial state corruption.
- Technical notes: Keep user-uploaded asset storage outside the embedded asset system.
- In scope: Account and profile settings.
- Out of scope: Public profile presentation.

### MP-05-05: Port preset, reset, and referral screens

- Description: Recreate the ancillary account routes for presets, character reset mechanics, and referral visibility.
- Estimated time: 1.5h
- Dependencies: MP-05-01, MP-06-01.
- Acceptance criteria:
  - Preset and reset actions are gated by the same conditions as the current PHP flows.
  - Referral data is viewable in the account area.
  - Each action is auditable in logs.
- Technical notes: Keep destructive actions behind explicit confirmation pages.
- In scope: Preset/reset/referral web flows.
- Out of scope: Era-wide reset tooling.

### MP-05-06: Replace PHP session semantics with explicit Rust session handling

- Description: Implement server-side session storage that preserves the app's reliance on mutable session state for chat tabs, combat flags, and mission state.
- Estimated time: 1.5h
- Dependencies: MP-05-01, MP-03-03.
- Acceptance criteria:
  - Session storage survives normal application restarts if configured to use PostgreSQL.
  - Flash messages and page transitions work without global mutable PHP state.
  - Session fixation defenses exist on login.
- Technical notes: This is a foundational compatibility task for later modules.
- In scope: Session middleware, cookies, flash data.
- Out of scope: Feature-specific session payloads.