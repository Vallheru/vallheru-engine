# Vallheru Rust Port Guidelines

- Use Rust 1.75.0 with the `clippy` and `rustfmt` components (see `rust-toolchain.toml`).
- Format code with `cargo fmt --all` before committing and prefer a 100 character line width.
- Run `cargo clippy --all-targets --all-features` to lint the project; wildcard imports are
  reported as warnings via `.clippy.toml`.
- The Rust code now lives in a single crate under `src/`. The structure mirrors the future
  modular-monolith layout but keeps only the `players` module alongside shared web
  utilities for now.
- Placeholder handlers and models exist under `src/players/`; expand them gradually as
  gameplay features are implemented.
- Shared HTTP wiring lives in `src/web/` and general configuration helpers in
  `src/config.rs`. The `/health` route exposes a simple JSON response.
- Please expand this file with any additional project structure notes, standards, or other
  information that could help future agents work effectively.
