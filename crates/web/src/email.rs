//! Outbound email service.
//!
//! When SMTP is configured, emails are sent asynchronously via `lettre`.
//! When SMTP is not configured, emails are logged at `info` level for
//! development/testing.

use std::sync::Arc;

/// Configuration for the email service, mirroring the server SMTP config.
#[derive(Debug, Clone)]
pub struct EmailConfig {
    pub host: String,
    pub port: u16,
    pub username: Option<String>,
    pub password: Option<String>,
    pub from: String,
}

/// Email sending service that can be cheaply cloned (shared via `Arc`).
#[derive(Clone)]
pub struct EmailService {
    inner: Arc<Inner>,
}

enum Inner {
    Smtp {
        transport: Box<lettre::AsyncSmtpTransport<lettre::Tokio1Executor>>,
        from: lettre::message::Mailbox,
    },
    Log,
}

impl EmailService {
    /// Create a real SMTP-backed service.
    ///
    /// # Errors
    ///
    /// Returns an error if the SMTP transport cannot be built (e.g. bad host).
    pub fn from_config(config: &EmailConfig) -> Result<Self, String> {
        use lettre::AsyncSmtpTransport;
        use lettre::transport::smtp::authentication::Credentials;

        let mut builder =
            AsyncSmtpTransport::<lettre::Tokio1Executor>::starttls_relay(&config.host)
                .map_err(|e| format!("SMTP relay error: {e}"))?
                .port(config.port);

        if let (Some(user), Some(pass)) = (&config.username, &config.password) {
            builder = builder.credentials(Credentials::new(user.clone(), pass.clone()));
        }

        let transport = builder.build();

        let from: lettre::message::Mailbox = config
            .from
            .parse()
            .map_err(|e| format!("invalid 'from' address: {e}"))?;

        Ok(Self {
            inner: Arc::new(Inner::Smtp {
                transport: Box::new(transport),
                from,
            }),
        })
    }

    /// Create a no-op service that logs emails instead of sending them.
    #[must_use]
    pub fn log_only() -> Self {
        Self {
            inner: Arc::new(Inner::Log),
        }
    }

    /// Send a plain-text email.
    ///
    /// Returns `Ok(())` on success, or logs and returns an error message.
    pub async fn send(&self, to: &str, subject: &str, body: &str) -> Result<(), String> {
        match self.inner.as_ref() {
            Inner::Smtp { transport, from } => {
                use lettre::{AsyncTransport, Message};

                let to_mailbox: lettre::message::Mailbox =
                    to.parse().map_err(|e| format!("invalid recipient: {e}"))?;

                let email = Message::builder()
                    .from(from.clone())
                    .to(to_mailbox)
                    .subject(subject)
                    .body(body.to_owned())
                    .map_err(|e| format!("failed to build email: {e}"))?;

                transport
                    .send(email)
                    .await
                    .map_err(|e| format!("SMTP send failed: {e}"))?;

                tracing::info!(%to, %subject, "email sent");
                Ok(())
            }
            Inner::Log => {
                tracing::info!(
                    %to,
                    %subject,
                    %body,
                    "email not sent (SMTP not configured)"
                );
                Ok(())
            }
        }
    }
}
