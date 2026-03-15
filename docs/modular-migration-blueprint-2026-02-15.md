# EZFolio Modular Migration Blueprint

Date: February 15, 2026

## 1. Target Architecture (Strict Modular + Repository Rule)

Goal: Split current monolith into two dedicated apps while preserving current design and behavior.

- Backend: `ezfolio-api` (Laravel, API-only)
- Frontend: `ezfolio-web` (React, SPA)

### 1.1 Core Rules

- All DB access must go through repositories only.
- No DB calls in controllers, services, traits, facades, helpers, jobs, commands, listeners, policies, or requests.
- Eloquent models are allowed as persistence entities, but only repositories can query/persist them.
- Business logic can live in services/traits/facades/helpers, but those units must consume repository interfaces.
- Data flow must use typed DTOs/adapters/resources/collections; no raw model or unstructured array leakage across layers.
- Backend must be strict-typed (`declare(strict_types=1);`) and use typed properties/arguments/returns.
- Frontend must be strict TypeScript (`\"strict\": true`, `\"noImplicitAny\": true`, `\"strictNullChecks\": true`).

### 1.2 Recommended Backend Folder Structure

```txt
app/
  Domain/
    Shared/
      Contracts/
        Repository/
          BaseRepositoryInterface.php
      DTO/
      Exceptions/
      Traits/
    Admin/
      About/
        Contracts/
          AboutRepositoryInterface.php
        DTO/
        Services/
          UpdateAboutService.php
        Repositories/
          AboutRepository.php
      Blog/
      Portfolio/
      Messages/
      Settings/
    Frontend/
      Portfolio/
      Blog/
      Contact/

  Infrastructure/
    Persistence/
      Eloquent/
        BaseRepository.php
        Criteria/
        Pagination/
      Models/
    Providers/
      RepositoryServiceProvider.php

  Application/
    Http/
      Controllers/
        Api/
          V1/
            Admin/
            Frontend/
      Requests/
      Resources/
      Middleware/
```

### 1.3 Base Repository Pattern

Use interface + abstract base class + concrete repository per aggregate.

```php
<?php

namespace App\Domain\Shared\Contracts\Repository;

interface BaseRepositoryInterface
{
    public function findById(int|string $id);
    public function findOneBy(array $filters);
    public function findMany(array $filters = [], array $sort = []);
    public function paginate(int $perPage = 15, array $filters = [], array $sort = []);
    public function create(array $data);
    public function update(int|string $id, array $data);
    public function delete(int|string $id): bool;
}
```

```php
<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use App\Domain\Shared\Contracts\Repository\BaseRepositoryInterface;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function findById(int|string $id)
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data)
    {
        return $this->model->newQuery()->create($data);
    }

    // implement other methods with query scopes / criteria
}
```

```php
<?php

namespace App\Domain\Admin\Blog\Contracts;

use App\Domain\Shared\Contracts\Repository\BaseRepositoryInterface;

interface BlogPostRepositoryInterface extends BaseRepositoryInterface
{
    public function findPublished(array $filters = []);
}
```

```php
<?php

namespace App\Domain\Admin\Blog\Repositories;

use App\Models\BlogPost;
use App\Infrastructure\Persistence\Eloquent\BaseRepository;
use App\Domain\Admin\Blog\Contracts\BlogPostRepositoryInterface;

final class BlogPostRepository extends BaseRepository implements BlogPostRepositoryInterface
{
    public function __construct(BlogPost $model)
    {
        parent::__construct($model);
    }

    public function findPublished(array $filters = [])
    {
        return $this->model->newQuery()
            ->where('status', 'published')
            ->when(isset($filters['q']), fn ($q) => $q->where('title', 'like', '%'.$filters['q'].'%'))
            ->orderByDesc('published_at')
            ->get();
    }
}
```

Binding in `RepositoryServiceProvider`:

```php
$this->app->bind(BlogPostRepositoryInterface::class, BlogPostRepository::class);
```

### 1.4 Service Layer and Other Logic

- Services: orchestrate use-cases, transactions, validations spanning multiple repos.
- Traits/helpers/facades: utility, formatting, integrations, and cross-cutting concerns.
- Rule: these layers can call repositories only for persistence access.
- Adapter layer: map repository/domain output into API-facing DTOs and resources.
- API output layer: always return Laravel `JsonResource` / `ResourceCollection` (or typed wrappers), never free-form controller arrays.

### 1.5 Auth Choice

Preferred for React SPA + API-only Laravel:

- Laravel Sanctum with token-based SPA auth (or JWT if you need strict stateless mobile parity).
- If continuing JWT for admin (current app uses `jwt.verify`), keep JWT in v1 for compatibility, then evaluate migration to Sanctum in v2.

### 1.6 Frontend Structure (React)

```txt
src/
  app/
    router/
    store/
    providers/
  modules/
    admin/
      about/
      settings/
      blog/
      portfolio/
      messages/
    public/
      home/
      blog/
      contact/
  shared/
    api/
      client.ts
      interceptors.ts
    ui/
    hooks/
    utils/
  styles/
    tokens.css
```

- Preserve existing visual system (spacing, colors, typography, component behavior).
- Migrate page-by-page using existing DOM/CSS contracts where possible.

### 1.7 Data Adapters, Resources, and Collections (Mandatory)

- Request -> FormRequest -> DTO -> Service -> Repository -> Adapter -> Resource/Collection -> JSON response.
- Introduce per-module adapters, for example:
  - `AboutDataAdapter`
  - `ProjectDataAdapter`
  - `BlogPostDataAdapter`
- Use API Resources for single entities and Resource Collections for lists with pagination meta.
- Standard response contract:
  - `message: string`
  - `payload: object|array|null`
  - `status: int`
  - `meta: object` (pagination, filters, trace id)

### 1.8 Strict Typing Standards

Backend (Laravel/PHP):

- Add `declare(strict_types=1);` in all new PHP files.
- No mixed/untyped public APIs in repositories/services/controllers.
- Use typed DTO classes for request/response boundaries.
- Use enums/value objects for constrained fields (status, role, publish state).

Frontend (React/TypeScript):

- Use TypeScript only for new app code.
- Enforce strict mode in `tsconfig.json`.
- Define module contracts with interfaces/types for API payloads.
- Centralize API schemas and runtime validation (`zod` or equivalent) for untrusted responses.

### 1.9 Security, Optimization, and Efficiency Baseline

Security baseline:

- Auth hardening (short-lived access token + refresh strategy, secure storage rules).
- Strict CORS allowlist and environment-specific origins.
- Input validation + output encoding + file upload mime/size checks.
- Rate limiting per endpoint category (auth/contact/admin).
- Authorization policy checks in every admin mutation endpoint.
- Security headers and transport hardening (HSTS, CSP where applicable).
- Secrets management via env/vault only; no plaintext secrets in repo.
- Dependency and SAST scanning in CI.

Performance/efficiency baseline:

- Repository query optimization (select columns, eager loading, avoid N+1).
- Pagination defaults and max limits for list endpoints.
- Caching strategy for public read-heavy endpoints (tags/invalidation defined).
- Queue heavy tasks (mail, image processing, analytics aggregation).
- Frontend code splitting + route-based lazy loading.
- Asset optimization and long-term caching headers.
- Observability with structured logs, metrics, and tracing IDs.

## 2. API Endpoint Map from Current Routes/Controllers

Source routes analyzed:

- `routes/api.php`
- `routes/web.php`

### 2.1 Current Admin API (`/api/v1/admin`)

Auth:

- `POST /login` -> `Admin\\Api\\AdminController@login`
- `POST /forget-password` -> `Admin\\Api\\AdminController@forgetPassword`
- `POST /reset-password` -> `Admin\\Api\\AdminController@resetPassword`
- `POST /refresh-token` -> `Admin\\Api\\AdminController@refreshToken` (jwt)
- `GET /me` -> `Admin\\Api\\AdminController@me`
- `GET /stats` -> `Admin\\Api\\AdminController@stats`
- `GET|POST /login-credentials` -> `Admin\\Api\\AdminController@loginCredentials`

Settings/Branding:

- `GET|POST /settings` -> `SettingController@index`
- `POST|DELETE /logo` -> `SettingController@logo`
- `POST|DELETE /favicon` -> `SettingController@favicon`
- `POST /mail-settings` -> `SettingController@storeMailSettings`

Portfolio/About/SEO:

- `GET|POST /portfolio-configs` -> `PortfolioController@index`
- `GET|POST /about` -> `PortfolioController@about`
- `POST /seo` -> `PortfolioController@seo`
- `POST|DELETE /avatar` -> `PortfolioController@avatar`
- `POST|DELETE /cv` -> `PortfolioController@cv`
- `POST|DELETE /cover` -> `PortfolioController@cover`
- `GET|DELETE /visitors/stats` -> `PortfolioController@visitorsStats`

Blog CMS:

- Categories: `GET/POST /blog/categories`, `GET/PUT /blog/categories/{id}`, `DELETE /blog/categories`
- Tags: `GET/POST /blog/tags`, `GET/PUT /blog/tags/{id}`, `DELETE /blog/tags`
- Posts: `GET/POST /blog/posts`, `GET/PUT /blog/posts/{id}`, `DELETE /blog/posts`
- Comments: `GET /blog/comments`, `PUT /blog/comments/{id}`, `DELETE /blog/comments`

Portfolio Content:

- Skills: `GET/POST /skills`, `GET/PUT /skills/{id}`, `DELETE /skills`
- Education: `GET/POST /education`, `GET/PUT /education/{id}`, `DELETE /education`
- Experiences: `GET/POST /experiences`, `GET/PUT /experiences/{id}`, `DELETE /experiences`
- Projects: `GET/POST /projects`, `GET/PUT /projects/{id}`, `DELETE /projects`
- Services: `GET/POST /services`, `GET/PUT /services/{id}`, `DELETE /services`
- Messages: `GET/POST /messages`, `GET/PUT /messages/{id}`, `DELETE /messages`

### 2.2 Current Frontend/Public APIs

- `GET /api/v1/frontend/projects` -> `Frontend\\Api\\GeneralController@getProjects`
- `POST /api/v1/messages` -> `Frontend\\Api\\GeneralController@store` (contact)
- `GET /api/v1/status` -> health

### 2.3 Current Server-rendered Frontend Routes (to replace by React app routes)

- `/` -> `FrontendController@index`
- `/blog` -> `BlogController@index`
- `/blog/{slug}` -> `BlogController@show`
- `POST /blog/{slug}/comment` -> `BlogController@storeComment`
- `/blog/rss.xml` -> `BlogController@rss`
- `/pixel-tracker` -> `FrontendController@pixelTracker`
- `/media/{path}` -> `MediaController@show`
- `/sitemap.xml` closure

### 2.4 Proposed API V1 Modules in New Backend

- `AuthModule`
- `AdminSettingsModule`
- `PortfolioModule` (about/skills/education/experience/projects/services)
- `BlogModule` (categories/tags/posts/comments/rss)
- `ContactModule`
- `AssetsModule` (media/logo/avatar/cv/cover)
- `AnalyticsModule` (visitors/pixel)

Each module owns:

- Controller(s)
- Request(s)
- Resource(s)
- Service(s)
- Repository interface(s)
- Repository implementation(s)
- Tests

## 3. Week-by-Week Execution Plan (with Risk Controls)

### Week 1: Foundation + Architecture Guardrails

- Create `ezfolio-api` and `ezfolio-web` repositories.
- Add coding standard that forbids direct model queries outside repositories.
- Implement `BaseRepositoryInterface`, `BaseRepository`, `RepositoryServiceProvider`.
- Establish CI checks (PHPStan/Psalm + ESLint + test pipelines).
- Add strict typing rules: PHP strict types and TS strict config.
- Add standard DTO/adapter/resource/collection scaffolding templates.
- Add security CI checks (dependency audit, SAST, secret scanning).

Risk controls:

- Add static rule checks and PR checklist item: "No DB outside repository".
- Add architecture tests (e.g., deptrac or custom rule) to fail if `Model::` appears in forbidden layers.

### Week 2: Auth + Shared Contracts

- Implement admin auth endpoints first with current response compatibility.
- Introduce API Resource DTO contracts for all auth/settings responses.
- Implement frontend API client auth handling (token storage, refresh strategy).

Risk controls:

- Snapshot tests for response JSON keys/status codes.
- Backward-compatible route aliases during transition.

### Week 3: Settings + About + Assets

- Migrate settings/logo/favicon/mail-settings.
- Migrate about/avatar/cv/cover and portfolio-config endpoints.
- Wire media handling behind dedicated asset repository/services.

Risk controls:

- File upload integration tests (size/type/path).
- Rollback switch to old endpoints using env-based proxy routing.

### Week 4: Portfolio Content Module

- Migrate skills, education, experiences, projects, services CRUD.
- Move validation into form requests; orchestration into services.
- Frontend admin pages start consuming new API routes.

Risk controls:

- CRUD contract tests for each endpoint.
- Data parity checks against monolith DB before/after writes.

### Week 5: Blog Module

- Migrate categories/tags/posts/comments + publish flow + RSS support.
- Migrate public blog list/detail/comment APIs for React frontend.

Risk controls:

- SEO parity checks (slug/canonical/meta fields).
- RSS snapshot comparison between old and new output.

### Week 6: Contact + Analytics + Public APIs

- Migrate `/messages`, `/frontend/projects`, `/pixel-tracker`, visitor stats.
- Add anti-spam/rate-limit controls for public contact endpoints.

Risk controls:

- Abuse tests (throttle + payload validation).
- Observe error rates and queue failure alarms.

### Week 7: React UI Parity Sprint

- Complete page-by-page migration preserving current design.
- Validate visual parity for admin and public pages.

Risk controls:

- Screenshot regression testing (critical pages).
- UX checklist for typography/spacing/component states.

### Week 8: Cutover + Hardening

- Enable production traffic to `ezfolio-web` + `ezfolio-api`.
- Keep monolith in read-only fallback mode for rollback window.
- Finalize observability dashboards and runbook.

Risk controls:

- Canary rollout (5% -> 25% -> 100%).
- Automated rollback trigger thresholds (5xx, auth failures, latency).

## 4. Non-Negotiable Engineering Standards

- Repository-only persistence access.
- Service layer for use-case orchestration.
- Controllers stay thin (request -> service -> resource).
- No business logic in controllers/repositories.
- Traits/helpers only for reusable non-persistence logic.
- 80%+ test coverage on critical modules (auth, blog publish, contact).
- Typed DTO/adapters/resources/collections for all data flow boundaries.
- Strict typing in backend and frontend is mandatory.
- Security-by-default controls are required before production cutover.
- Performance budgets and query efficiency checks are required in CI/review.

## 5. First Build Order (Practical)

1. Build backend scaffolding with repository rule enforcement.
2. Implement auth + settings + about APIs.
3. Move React admin shell and connect to new APIs.
4. Migrate portfolio CRUD modules.
5. Migrate blog modules.
6. Migrate public pages and switch traffic.

## 6. Immediate Next Actions

1. Approve auth direction: keep JWT in v1 or switch to Sanctum now.
2. Approve module boundaries listed in section 2.4.
3. Start Week 1 by scaffolding `ezfolio-api` with base repository contracts and CI architecture tests.
