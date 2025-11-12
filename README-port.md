# Vallheru Rust Workspace

This directory hosts the `vallheru-rs` Rust workspace organised into a few lightweight crates instead of a strict domain-driven structure:

- `crates/engine` – core game logic and shared functionality that can be reused by other crates.
- `crates/storage` – helpers that will eventually integrate with databases or files while reusing the engine crate.
- `crates/web` – a placeholder web entry point that depends on the other crates.

## Prerequisites

- Rust toolchain with `cargo` (the latest stable release is recommended).

## Running

1. Enter the workspace directory:

   ```bash
   cd vallheru-rs
   ```

2. Launch the web crate:

   ```bash
   cargo run -p web
   ```

The command builds the required crates and starts the demo web binary.

## Branching Model

The default development flow happens on the `work` branch, and changes are
reviewed by opening pull requests that target the newly created `develop`
branch. Create your feature branch from `work`, implement the required changes,
and open a pull request with `develop` as the base branch.
