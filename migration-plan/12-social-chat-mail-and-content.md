# 12 Social, Chat, Mail, and Content

## Current State

- `chat.php`
- `chatmsgs.php`
- `room.php`
- `roommsgs.php`
- `mail.php`
- `forums.php`
- `tforums.php` (tribe forums entry point, see also module 13)
- `news.php`
- `newspaper.php`
- `proposals.php`
- `polls.php`
- `roleplay.php`
- `rss.php`
- `library.php`
- `notatnik.php` (personal notebook)
- `chronicle.php`
- `includes/bbcode.php` (BBCode formatting)
- `includes/comments.php` (comments on news, updates, polls)
- `class/bot_class.php` (inn chat bot)

## Why This Module Exists

Port the communication and content subsystems while preserving the current polling-heavy, server-rendered interaction model.

## Target Rust Shape

- `crates/domain/src/social/chat.rs` — Chat message creation, whisper rules, bot hook.
- `crates/domain/src/social/room.rs` — Room entry, ownership, room-specific chat.
- `crates/domain/src/social/mail.rs` — Private mail, contacts, unread counters.
- `crates/domain/src/social/forum.rs` — Forum thread/post creation, pagination.
- `crates/domain/src/social/bbcode.rs` — BBCode parsing and safe HTML rendering.
- `crates/domain/src/social/content.rs` — News, newspaper, proposals, polls, RSS, library, chronicle, notebook.
- `crates/data/src/social.rs` — Chat, mail, forum, content queries.
- `crates/web/src/handlers/chat.rs` — Chat/room page handlers plus message polling endpoints.
- `crates/web/src/handlers/mail.rs` — Mail handlers.
- `crates/web/src/handlers/forums.rs` — Forum and tribe forum handlers.
- `crates/web/src/handlers/content.rs` — News, newspaper, polls, proposals, library, chronicle, notebook handlers.

## Module Dependencies

- 05 Auth, Accounts, and Sessions (session for chat tabs, user identity for mail/forum).
- 04 Rendering, Assets, and Localization (BBCode rendering, page templates).
- 06 Player State and Progression (player info displayed in chat/mail/forum contexts).

## Risks and Notes

- Chat relies on session-backed tab state and polling-based message fetch. Do not force WebSockets during parity work.
- `includes/comments.php` adds comments to news, updates, and polls — this is a shared subsystem that must be available to content pages.
- BBCode subset must be identified from actual usage rather than implementing a full spec.
- `class/bot_class.php` implements automated innkeeper responses; bot commands must be mapped.

## Tasks

### MP-12-01: Port global chat, whispers, and inn bot integration ✅

- Description: Rebuild the tavern chat flow, private whispers, chat tabs, and the innkeeper bot behavior.
- Estimate: 2h
- Depends on: MP-05-06, MP-04-06.
- Status: **Done**.
- Functional acceptance criteria:
  - Public and private chat messages can be sent and fetched in Rust.
  - Session-backed chat tab behavior still works.
  - Bot-triggered replies preserve current command patterns.
- Technical notes: Keep the first version long-poll or polling-based; do not force WebSockets during parity work.
- In scope: Chat handlers, message fetch API, bot hook.
- Out of scope: Forum or mail behavior.

### MP-12-02: Port room chat and tavern room state

- Description: Rebuild room entry, room message polling, room ownership checks, and room-specific chat behavior.
- Estimate: 1.5h
- Depends on: MP-12-01, MP-07-05.
- Functional acceptance criteria:
  - Players can enter rooms and fetch/send room messages.
  - Room access checks and owner checks match current rules.
  - Pagination or message-window size rules are preserved.
- Technical notes: Keep room state out of ad hoc raw session arrays where possible.
- In scope: Room chat and room page flows.
- Out of scope: Team or tribe-only messaging.

### MP-12-03: Port mail, contacts, and unread counters

- Description: Rebuild private mail, contact lists, and per-player unread message indicators.
- Estimate: 1.5h
- Depends on: MP-05-01, MP-04-05.
- Functional acceptance criteria:
  - Sending, reading, and listing mail works end to end.
  - Contact lookups and unread counters are accurate.
  - Mail restrictions such as bans can be enforced later by the admin module.
- Technical notes: Keep message body formatting explicit and safe.
- In scope: Mail and contacts.
- Out of scope: Staff moderation tooling.

### MP-12-04: Port forums and discussion formatting

- Description: Rebuild public forums and the BBCode formatting pipeline used in chat, mail, forums, and newspaper pages.
- Estimate: 1.5h
- Depends on: MP-04-04, MP-04-05.
- Functional acceptance criteria:
  - Forum threads and posts can be rendered and posted.
  - BBCode or equivalent rendering is centralized.
  - Pagination and posting restrictions are preserved.
- Technical notes: Implement only the BBCode subset actually used by the current repo.
- In scope: Forum posting and formatting pipeline.
- Out of scope: Tribe forums if they diverge materially.

### MP-12-05: Port news, newspaper, proposals, polls, and RSS outputs

- Description: Migrate the publishing and player-voting features that surface news and game content.
- Estimate: 1.5h
- Depends on: MP-12-04.
- Functional acceptance criteria:
  - News and newspaper pages render from Rust data sources.
  - Proposal and poll submission flows work.
  - RSS output is preserved where it still has consumers.
- Technical notes: Keep staff-only publishing hooks minimal until admin tooling is ported.
- In scope: Public content publishing and reading.
- Out of scope: Staff moderation back office.

### MP-12-06: Port notes, library, roleplay, and chronicle content pages

- Description: Rebuild the personal notebook, library content, roleplay text, and chronicle-style narrative pages.
- Estimate: 2h
- Depends on: MP-12-04, MP-06-05.
- Functional acceptance criteria:
  - Players can read and update notebook-like content where currently allowed.
  - Library and roleplay content render through MiniJinja.
  - Narrative pages use reusable content view models rather than raw SQL in handlers.
- Technical notes: This also provides support pages used by later world and quest modules.
- In scope: Content-centric pages and their storage.
- Out of scope: Quest state transitions.