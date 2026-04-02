use clap::{Parser, Subcommand};
use std::path::PathBuf;

#[derive(Parser)]
#[command(name = "vallheru", version, about = "Vallheru game engine")]
pub struct Cli {
    /// Path to configuration file (default: vallheru.toml if present).
    #[arg(short, long, global = true)]
    pub config: Option<PathBuf>,

    #[command(subcommand)]
    pub command: Command,
}

#[derive(Subcommand)]
pub enum Command {
    /// Start the HTTP game server.
    Serve,

    /// Run pending database migrations.
    Migrate,

    /// Import reference data from embedded seed files.
    Import,

    /// Run a scheduled job (energy-tick, daily-reset).
    Job {
        /// The job to run (energy-tick | daily-reset).
        name: String,
    },

    /// Bootstrap a new game: run migrations, import seeds, create admin.
    Bootstrap {
        /// Admin username.
        #[arg(long)]
        admin_user: String,
        /// Admin email.
        #[arg(long)]
        admin_email: String,
        /// Admin password.
        #[arg(long)]
        admin_password: String,
    },

    /// Reset the game era (wipe player progress, keep accounts).
    ResetEra {
        /// Required confirmation flag to prevent accidental resets.
        #[arg(long)]
        confirm_reset: bool,
    },
}
