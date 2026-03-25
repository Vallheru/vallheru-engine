# 04 Rendering, Assets, and Localization

## Source Surface

- `templates/`
- `templates/layout1/`
- `templates_c/`
- `css/`
- `js/`
- `images/`
- `languages/`
- `main.css`
- `temporary.css`

## Goal

Recreate the current server-rendered UI in MiniJinja with embedded assets and explicit localization loading.

## Tasks

### MP-04-01: Build the MiniJinja environment

- Description: Set up MiniJinja with template loading, shared globals, shared partials, and helper functions equivalent to the current layout system.
- Estimated time: 1.5h
- Dependencies: MP-01-01, MP-03-02.
- Acceptance criteria:
  - The Rust app can render a basic HTML page using MiniJinja.
  - Shared layout, header, footer, and message partials are registered.
  - Template errors surface clearly in development.
- Technical notes: Keep helpers narrow; avoid rebuilding Smarty features wholesale.
- In scope: Templating runtime and helper registration.
- Out of scope: Full template conversion.

### MP-04-02: Port the base layouts and theme selection

- Description: Recreate the default and `layout1` theme structures so page migrations can attach to stable shared templates.
- Estimated time: 1h
- Dependencies: MP-04-01.
- Acceptance criteria:
  - The default theme renders from MiniJinja.
  - Theme selection is request-driven rather than file-path-global state.
  - Layout-specific asset references are resolved through one helper path.
- Technical notes: Preserve current markup first; visual refactoring can wait.
- In scope: Base templates and theme switching.
- Out of scope: CSS redesign.

### MP-04-03: Embed shipped assets into the binary

- Description: Embed templates, CSS, JS, and images at compile time and serve them from memory.
- Estimated time: 1h
- Dependencies: MP-01-01, MP-03-02.
- Acceptance criteria:
  - The app serves CSS, JS, and images without reading shipped files from disk.
  - Asset URLs include a version or content hash.
  - No writable template cache directory is required.
- Technical notes: Keep user-uploaded files separate from shipped assets.
- In scope: Embedded static asset pipeline.
- Out of scope: CDN integration.

### MP-04-04: Define localization catalog loading

- Description: Replace PHP constant files under `languages/` with structured Rust-loaded message catalogs.
- Estimated time: 1h
- Dependencies: MP-04-01.
- Acceptance criteria:
  - Language strings can be loaded per route/module.
  - Missing translations fail visibly in development.
  - Current Polish content is preserved.
- Technical notes: Start with one locale and keep the loader format boring.
- In scope: Localization file format and loader.
- Out of scope: Translation tooling.

### MP-04-05: Rebuild common form and message components

- Description: Port recurring UI fragments such as errors, status messages, confirmation forms, tables, and pagination stubs.
- Estimated time: 1h
- Dependencies: MP-04-01, MP-04-02.
- Acceptance criteria:
  - Shared components are reusable across multiple pages.
  - HTML pages can render success, warning, and error states consistently.
  - Table-heavy pages do not need bespoke markup for every migration.
- Technical notes: This task exists to reduce repetition across later modules.
- In scope: Common view components.
- Out of scope: Feature-specific templates.

### MP-04-06: Port page-level JS and CSS loading rules

- Description: Define how legacy page-specific JS and CSS are associated with migrated handlers and templates.
- Estimated time: 1.5h
- Dependencies: MP-04-02, MP-04-03.
- Acceptance criteria:
  - A page can declare the exact JS and CSS assets it needs.
  - Existing scripts such as chat, market, bank, and battle JS can be served unchanged at first.
  - No template depends on writing compiled assets to disk.
- Technical notes: Keep the first version static; bundling is unnecessary for parity.
- In scope: Asset inclusion rules and registration.
- Out of scope: JS framework migration.