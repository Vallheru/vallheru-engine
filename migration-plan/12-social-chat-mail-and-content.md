# 12 Social, Chat, Mail, and Content

## Source Surface

- `chat.php`
- `chatmsgs.php`
- `room.php`
- `roommsgs.php`
- `mail.php`
- `forums.php`
- `tforums.php`
- `news.php`
- `newspaper.php`
- `proposals.php`
- `polls.php`
- `roleplay.php`
- `rss.php`
- `library.php`
- `notatnik.php`
- `chronicle.php`
- `includes/bbcode.php`
- `class/bot_class.php`

## Goal

Port the communication and content subsystems while preserving the current polling-heavy, server-rendered interaction model.

## Tasks

### MP-12-01: Port global chat, whispers, and inn bot integration

- Description: Rebuild the tavern chat flow, private whispers, chat tabs, and the innkeeper bot behavior.
- Estimated time: 2h
- Dependencies: MP-05-06, MP-04-06.
- Acceptance criteria:
  - Public and private chat messages can be sent and fetched in Rust.
  - Session-backed chat tab behavior still works.
  - Bot-triggered replies preserve current command patterns.
- Technical notes: Keep the first version long-poll or polling-based; do not force WebSockets during parity work.
- In scope: Chat handlers, message fetch API, bot hook.
- Out of scope: Forum or mail behavior.

### MP-12-02: Port room chat and tavern room state

- Description: Rebuild room entry, room message polling, room ownership checks, and room-specific chat behavior.
- Estimated time: 1.5h
- Dependencies: MP-12-01, MP-07-05.
- Acceptance criteria:
  - Players can enter rooms and fetch/send room messages.
  - Room access checks and owner checks match current rules.
  - Pagination or message-window size rules are preserved.
- Technical notes: Keep room state out of ad hoc raw session arrays where possible.
- In scope: Room chat and room page flows.
- Out of scope: Team or tribe-only messaging.

### MP-12-03: Port mail, contacts, and unread counters

- Description: Rebuild private mail, contact lists, and per-player unread message indicators.
- Estimated time: 1.5h
- Dependencies: MP-05-01, MP-04-05.
- Acceptance criteria:
  - Sending, reading, and listing mail works end to end.
  - Contact lookups and unread counters are accurate.
  - Mail restrictions such as bans can be enforced later by the admin module.
- Technical notes: Keep message body formatting explicit and safe.
- In scope: Mail and contacts.
- Out of scope: Staff moderation tooling.

### MP-12-04: Port forums and discussion formatting

- Description: Rebuild public forums and the BBCode formatting pipeline used in chat, mail, forums, and newspaper pages.
- Estimated time: 1.5h
- Dependencies: MP-04-04, MP-04-05.
- Acceptance criteria:
  - Forum threads and posts can be rendered and posted.
  - BBCode or equivalent rendering is centralized.
  - Pagination and posting restrictions are preserved.
- Technical notes: Implement only the BBCode subset actually used by the current repo.
- In scope: Forum posting and formatting pipeline.
- Out of scope: Tribe forums if they diverge materially.

### MP-12-05: Port news, newspaper, proposals, polls, and RSS outputs

- Description: Migrate the publishing and player-voting features that surface news and game content.
- Estimated time: 1.5h
- Dependencies: MP-12-04.
- Acceptance criteria:
  - News and newspaper pages render from Rust data sources.
  - Proposal and poll submission flows work.
  - RSS output is preserved where it still has consumers.
- Technical notes: Keep staff-only publishing hooks minimal until admin tooling is ported.
- In scope: Public content publishing and reading.
- Out of scope: Staff moderation back office.

### MP-12-06: Port notes, library, roleplay, and chronicle content pages

- Description: Rebuild the personal notebook, library content, roleplay text, and chronicle-style narrative pages.
- Estimated time: 2h
- Dependencies: MP-12-04, MP-06-05.
- Acceptance criteria:
  - Players can read and update notebook-like content where currently allowed.
  - Library and roleplay content render through MiniJinja.
  - Narrative pages use reusable content view models rather than raw SQL in handlers.
- Technical notes: This also provides support pages used by later world and quest modules.
- In scope: Content-centric pages and their storage.
- Out of scope: Quest state transitions.