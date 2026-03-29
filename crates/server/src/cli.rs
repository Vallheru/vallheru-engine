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

    /// Import data from a legacy database or dump.
    Import,

    /// Run a scheduled job (energy-tick, daily-reset).
    Job {
        /// The job to run (energy-tick | daily-reset).
        name: String,
    },

    /// Reset the game era (wipe player progress, keep accounts).
    ResetEra,

    /// Run data reconciliation checks between legacy and new schema.
    Reconcile,
}
