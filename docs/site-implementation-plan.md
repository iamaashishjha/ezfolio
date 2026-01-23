# Site Implementation Plan

## What Will Be Changed
- Update seed content to backend engineer messaging, skills, experiences, and project case studies.
- Enhance hero sections with explicit role title, subtitle, and CTAs.
- Add About highlights (chips) and backend-focused description.
- Group skills into Backend / Databases & Caching / Frontend / Tools in templates.
- Render project summaries on cards using structured details text.
- Add Resume download buttons for PDF + DOCX; add resume files to public assets.
- Add LinkedIn contact detail and honeypot spam protection.
- Add canonical + Twitter meta tags; add sitemap and update robots.
- Improve accessibility (H1 consistency, alt text, aria labels).

## Files To Modify/Add
- `database/seeders/PortfolioSeeder.php` (update default content and assets).
- `resources/views/frontend/theme/procyon.blade.php` (hero/about/skills/resume/contact/SEO/a11y).
- `resources/views/frontend/theme/rigel.blade.php` (hero/about/skills/resume/contact/SEO/a11y).
- `resources/views/frontend/theme/vega.blade.php` (hero/about/skills/resume/contact/SEO/a11y).
- `resources/js/client/frontend/roots/projects.js` (card summaries).
- `resources/js/client/frontend/components/ProjectPopup.js` (details formatting if needed).
- `resources/js/client/common/assets/css/projects.css` (summary styles).
- `public/assets/common/cv/` (resume files).
- `public/assets/common/default/cv/` (default resume files for seeding).
- `public/robots.txt` and `public/sitemap.xml`.

## Risks & Rollbacks
- Risk: Changing project detail formatting could affect existing content display.
  - Rollback: revert project summary parsing and show title-only cards.
- Risk: Resume links may break if assets are moved or renamed.
  - Rollback: point buttons back to `$about->cv` only.
- Risk: New grouping logic may hide skills not mapped.
  - Rollback: add “Other” group or render unmapped skills below.

## Screens/Sections Affected
- Home/Hero, About, Skills, Experience, Projects, Resume downloads, Contact.
- SEO meta tags in all themes.
