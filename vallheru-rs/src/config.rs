use std::net::{IpAddr, Ipv4Addr, SocketAddr};

/// Basic server configuration helpers.
pub struct ServerConfig;

impl ServerConfig {
    /// Returns the socket address used to bind the HTTP server.
    pub fn address() -> SocketAddr {
        SocketAddr::new(IpAddr::V4(Ipv4Addr::UNSPECIFIED), 8080)
    }
}
