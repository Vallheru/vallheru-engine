# Vallheru Engine

This repository hosts the legacy PHP version of the Vallheru browser RPG as well as an
in-progress Rust port located in [`vallheru-rs/`](./vallheru-rs). The Rust code currently
lives in a single crate that provides an Axum-based HTTP server with placeholder gameplay
modules.

## Getting started (Rust crate)

1. Install the Rust toolchain specified in [`vallheru-rs/rust-toolchain.toml`](./vallheru-rs/rust-toolchain.toml).
   The stable toolchain will be installed automatically when running commands via
   `rustup`.
2. Change into the Rust project directory:

   ```bash
   cd vallheru-rs
   ```

3. Fetch the project dependencies:

   ```bash
   cargo fetch
   ```

4. Run the formatting and linting tools to ensure the environment is ready:

   ```bash
   cargo fmt --all
   cargo clippy --all-targets --all-features
   ```

5. Start the development server:

   ```bash
   cargo run
   ```

   The Axum application listens on `http://127.0.0.1:8080` and exposes a `/health` route
   that responds with a JSON health check payload.

## Current Rust module layout

The Rust port mirrors a modular-monolith layout while remaining inside a single crate.
At the moment only the `players` feature module is present and its handlers return
placeholder data so the HTTP routing can be exercised. Additional gameplay areas should
follow the same pattern—group models, actions, and controllers within a dedicated
folder—and can be introduced gradually as the port progresses.

## Legacy PHP application

The original PHP application remains in the repository for reference. Legacy installation
instructions are preserved below for convenience:

1. Visit <http://localhost:8080/install/install.php>.
2. Follow the on-screen instructions.
3. Use the following database connection parameters:
   - Host: `mysql`
   - User: `root`
   - Password: `vallheru123`
   - Database: `vallheru`
