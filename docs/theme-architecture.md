# Portfolio theme architecture

The active portfolio theme is stored in `portfolio_configs` under `CoreConstants::PORTFOLIO_CONFIG__TEMPLATE`. The admin portfolio settings screen reads `Utils.templates`, posts the selected ID to the portfolio-config API, and the frontend controller resolves the matching `resources/views/frontend/theme/{id}.blade.php` view.

The original Procyon, Rigel, and Vega themes remain self-contained. Kernel, Blueprint, Datastream, Endpoint, Mainframe, Cluster, Schema, Uptime, Pipeline, and Cloudline use lightweight entry views that set the visual identity and include `backend-engineer.blade.php`. That shared view owns semantic markup, visibility rules, user-content escaping, intentionally rich descriptions, navigation, contact behavior, and portfolio data binding.

Each backend theme has `public/assets/themes/{id}/css/styles.css`, `css/custom.css`, and `js/main.js`. Shared responsive/accessibility rules live in `backend-common.css`; shared dependency-free navigation/contact behavior lives in `backend-common.js`. Theme-specific CSS changes layout, type, geometry, surface treatment, and information hierarchy. Blog pages load the active theme assets and use `nav-backend.blade.php` for compatible portfolio anchors.

Theme IDs are allow-listed in `CoreConstants::PORTFOLIO_THEMES`. This list protects dynamic view resolution and validates admin writes. Adding another theme requires updating that list and `Utils.templates`, then adding its entry view, assets, and selector preview.

## New concepts

- Kernel: Linux runtime and server console
- Blueprint: architecture drawing and connected service blocks
- Datastream: event flow and message streams
- Endpoint: REST reference and operation rows
- Mainframe: enterprise terminal dossier
- Cluster: distributed service nodes
- Schema: relational tables and structured records
- Uptime: SLO and observability panels
- Pipeline: build, test, deploy, and observe stages
- Cloudline: cloud service topology and soft infrastructure surfaces
