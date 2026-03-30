//! City dashboard handler.
//!
//! Ported from `city.php`. The city page is the main authenticated hub
//! with navigation links to all available game features, grouped by
//! district. Altara and Ardulith have different district layouts.

use axum::{Extension, extract::State, response::IntoResponse, response::Response};

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;
use vallheru_domain::location::Location;

// ---------------------------------------------------------------------------
// District navigation model
// ---------------------------------------------------------------------------

/// A group of navigation links within a city district.
#[derive(serde::Serialize)]
pub struct District {
    pub title: &'static str,
    pub links: Vec<NavLink>,
}

/// A single navigation link in a district.
#[derive(serde::Serialize)]
pub struct NavLink {
    pub href: &'static str,
    pub label: &'static str,
}

/// View model for the city page template.
#[derive(serde::Serialize)]
pub struct CityView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub city_name: &'static str,
    pub city_description: &'static str,
    pub districts: Vec<District>,
}

// ---------------------------------------------------------------------------
// Handler
// ---------------------------------------------------------------------------

/// GET /city — main city dashboard hub.
pub async fn show(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row =
        match vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await {
            Ok(Some(row)) => row,
            Ok(None) => return crate::page::redirect("/login"),
            Err(e) => {
                tracing::error!(error = %e, "city: failed to load player");
                return (
                    axum::http::StatusCode::INTERNAL_SERVER_ERROR,
                    "Internal error",
                )
                    .into_response();
            }
        };

    let Some(location) = Location::from_db(&player_row.location) else {
        return crate::page::redirect("/login");
    };

    if !location.is_city() {
        let meta = PageMeta::titled("Błąd").with_flash(crate::page::Flash {
            kind: crate::page::FlashKind::Error,
            message: "Nie znajdujesz się w mieście.".to_owned(),
        });
        let base = state.templates.build_context(&ctx, &meta);
        return state.templates.render("error.html", &base);
    }

    let (city_name, city_description, districts) = match location {
        Location::Altara => (
            "Altara",
            ALTARA_DESCRIPTION,
            districts_from(ALTARA_DISTRICTS),
        ),
        Location::Ardulith => (
            "Ardulith",
            ARDULITH_DESCRIPTION,
            districts_from(ARDULITH_DISTRICTS),
        ),
        _ => unreachable!(),
    };

    let meta = PageMeta::titled(city_name);
    let base = state.templates.build_context(&ctx, &meta);

    let view = CityView {
        base,
        city_name,
        city_description,
        districts,
    };

    state.templates.render_value("city.html", &view)
}

// ---------------------------------------------------------------------------
// Static district data
// ---------------------------------------------------------------------------

const ALTARA_DESCRIPTION: &str = "\
Do miasta prowadzi tylko jedna brama, znajdująca się w zachodniej części miasta. \
Główne ulice miasta są szerokie, wybrukowane i oświetlone. \
Miasto podzielone jest na 8 dzielnic, z których każda spełnia określoną rolę.";

const ARDULITH_DESCRIPTION: &str = "\
Miasto jest gajem ogromnych drzew, na gałęziach których znajdują się domy \
i pomieszczenia zbudowane za pomocą magii. Wygląd jest iście baśniowy.";

/// `(title, &[(href, label)])` tuples for each district.
type DistrictSpec = (&'static str, &'static [(&'static str, &'static str)]);

fn districts_from(specs: &[DistrictSpec]) -> Vec<District> {
    specs
        .iter()
        .map(|(title, links)| District {
            title,
            links: links
                .iter()
                .map(|(href, label)| NavLink { href, label })
                .collect(),
        })
        .collect()
}

static ALTARA_DISTRICTS: &[DistrictSpec] = &[
    (
        "Wojenne Pola",
        &[
            ("/battle", "Arena Walk"),
            ("/armor", "Płatnerz"),
            ("/weapons", "Zbrojmistrz"),
            ("/bows", "Łucznik"),
            ("/outposts", "Strażnica"),
            ("/hunters", "Gildia Łowców"),
            ("/guilds2", "Aula Gladiatorów"),
            ("/outpost", "Prefektura Gwardii"),
        ],
    ),
    (
        "Społeczność",
        &[
            ("/news", "Plotki"),
            ("/forums?view=categories", "Forum"),
            ("/chat", "Karczma"),
            ("/mail", "Poczta"),
            ("/tribes", "Klany"),
            ("/newspaper", "Redakcja gazety"),
        ],
    ),
    (
        "Podgrodzie",
        &[
            ("/train", "Szkoła"),
            ("/mines", "Kopalnia"),
            ("/farm", "Farma"),
            ("/core", "Polana Chowańców"),
        ],
    ),
    (
        "Zachodnia Strona",
        &[
            ("/grid", "Labirynt"),
            ("/tower", "Magiczna Wieża"),
            ("/temple", "Świątynia"),
            ("/hospital", "Szpital"),
            ("/magic-shop", "Alchemik"),
            ("/jeweller-shop", "Jubiler"),
        ],
    ),
    (
        "Dzielnica mieszkalna",
        &[
            ("/house", "Domy"),
            ("/memberlist", "Spis mieszkańców"),
            ("/hof2", "Galeria Machin"),
            ("/library", "Biblioteka"),
            ("/chronicle", "Kronika"),
        ],
    ),
    (
        "Zamek",
        &[
            ("/updates", "Wieści"),
            ("/tower-clock", "Zegar miejski"),
            ("/jail", "Lochy"),
            ("/court", "Gmach Sądu"),
            ("/polls", "Hala zgromadzeń"),
            ("/alley", "Aleja Zasłużonych"),
            ("/stafflist", "Sala audiencyjna"),
        ],
    ),
    (
        "Praca",
        &[
            ("/landfill", "Oczyszczanie miasta"),
            ("/smelter", "Huta"),
            ("/blacksmith", "Kuźnia"),
            ("/alchemy", "Pracownia alchemiczna"),
            ("/guilds", "Gildia Rzemieślników"),
            ("/crafts", "Cześnik"),
        ],
    ),
    (
        "Dzielnica południowa",
        &[
            ("/market", "Rynek"),
            ("/warehouse", "Magazyn Królewski"),
            ("/travel", "Stajnia"),
            ("/thieves", "Złodziejska Spelunka"),
        ],
    ),
];

static ARDULITH_DISTRICTS: &[DistrictSpec] = &[
    (
        "Święty kasztanowiec",
        &[
            ("/temple", "Świątynia"),
            ("/hospital", "Szpital"),
            ("/library", "Biblioteka"),
            ("/chronicle", "Kronika"),
            ("/jeweller", "Jubiler"),
        ],
    ),
    (
        "Północny sad",
        &[
            ("/bows", "Łucznik"),
            ("/magic-shop", "Alchemik"),
            ("/tower", "Magiczna Wieża"),
            ("/forums?view=categories", "Forum"),
            ("/chat", "Karczma"),
        ],
    ),
    (
        "Stary buk",
        &[
            ("/jail", "Lochy"),
            ("/maze", "Labirynt"),
            ("/mail", "Poczta"),
            ("/tribes", "Klany"),
        ],
    ),
    (
        "Zachodnie wierzby",
        &[
            ("/alchemy", "Pracownia alchemiczna"),
            ("/lumbermill", "Tartak"),
            ("/train", "Szkoła"),
            ("/jeweller-shop", "Jubiler"),
            ("/guilds", "Gildia Rzemieślników"),
            ("/crafts", "Cześnik"),
        ],
    ),
    (
        "Centralna polana",
        &[
            ("/landfill", "Oczyszczanie miasta"),
            ("/warehouse", "Magazyn Królewski"),
            ("/market", "Rynek"),
            ("/battle", "Arena Walk"),
            ("/core", "Polana Chowańców"),
            ("/polls", "Hala zgromadzeń"),
            ("/guilds2", "Aula Gladiatorów"),
            ("/outpost", "Prefektura Gwardii"),
        ],
    ),
    (
        "Królewski Dąb",
        &[
            ("/updates", "Wieści"),
            ("/tower-clock", "Zegar miejski"),
            ("/news", "Plotki"),
            ("/newspaper", "Redakcja gazety"),
            ("/alley", "Aleja Zasłużonych"),
            ("/stafflist", "Sala audiencyjna"),
            ("/court", "Gmach Sądu"),
        ],
    ),
    (
        "Zagajnik domów",
        &[
            ("/house", "Domy"),
            ("/memberlist", "Spis mieszkańców"),
            ("/outposts", "Strażnica"),
            ("/farm", "Farma"),
            ("/hunters", "Gildia Łowców"),
        ],
    ),
    (
        "Południowa droga",
        &[
            ("/travel", "Stajnia"),
            ("/forest", "Las"),
            ("/thieves", "Złodziejska Spelunka"),
        ],
    ),
];
