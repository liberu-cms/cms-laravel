# OWASP Top 10 Security Audit — Liberu CMS

**Scope:** the existing application (auth, Filament admin panel, tenancy, public
content rendering, uploads, configuration) as of branch `feature/cms-features`.
**Method:** manual source review mapped to the OWASP Top 10 (2021), plus
`composer audit`. This is a **report-first** deliverable; remediation follows in
separate PRs. Severities are the auditor's assessment for a production deployment.

## Summary

| # | Category | Highest severity | Status |
|---|----------|------------------|--------|
| A01 | Broken Access Control | **High** | Findings — tenancy not enforced; sparse policies |
| A02 | Cryptographic Failures | Low | Mostly sound |
| A03 | Injection (XSS) | **High** | Stored XSS via unescaped content |
| A04 | Insecure Design | Medium | Rich-content trust model; open registration |
| A05 | Security Misconfiguration | Medium | `APP_DEBUG=true` shipped; audit blocking off |
| A06 | Vulnerable & Outdated Components | Medium | 1 prod advisory; audit gate weakened |
| A07 | Identification & Auth Failures | Low | Strong (2FA, passkeys, throttling) |
| A08 | Software & Data Integrity Failures | Medium | Upload validation to confirm |
| A09 | Logging & Monitoring Failures | Medium | No security audit log |
| A10 | Server-Side Request Forgery | Info | No user-controlled fetches found |

**Top priorities:** (1) fix stored XSS in content templates [A03], (2) enforce
tenant isolation on the panel [A01] — being addressed in the multitenancy PR,
(3) ship `APP_DEBUG=false` defaults and a production checklist [A05].

---

## A01 — Broken Access Control · **High**

**Finding 1.1 — Tenancy is scaffolded but not enforced (High).**
`AppPanelProvider` wires `->tenantMiddleware()`, Shield's
`tenantOwnershipRelationshipName('teams')`, and `IsTenantModel` on models, but
never calls `->tenant(Team::class)`. Filament's tenant auto-scoping therefore
never engages, `SetPermissionsTeam` (which gates on `Filament::getTenant()`) is a
no-op, and `IsTenantModel` adds only a `team()` relationship — no query scope. If
tenancy is enabled, an authenticated user sees **every team's** Pages,
Categories, Collections, Tags, and Menus.
*Evidence:* `app/Providers/Filament/AppPanelProvider.php`,
`app/Traits/IsTenantModel.php`, `app/Http/Middleware/SetPermissionsTeam.php`,
`app/Filament/Resources/Pages/PageResource.php` (no `getEloquentQuery` scope).
*Remediation:* enable `->tenant()`, add a tenant global scope, wire
`HasTenants` on `User`, and add cross-tenant isolation tests. **In progress** in
the multitenancy PR.

**Finding 1.2 — Sparse authorization policies (Medium).**
Only `ConnectedAccountPolicy` exists; CMS models (Page, Category, Collection,
CollectionItem, Tag, Menu) rely entirely on Shield-generated permissions.
*Remediation:* confirm Shield permission coverage for every resource and add
model policies (or `->authorizeRecordsUsing`) so record-level checks are explicit,
not implicit. Add feature tests asserting a low-privilege user is forbidden.

**Finding 1.3 — Public routes are correctly unauthenticated (Info).**
`routes/web.php` exposes `/`, `/{slug}`, `/{collection:slug}/{item:slug}` for
public content — appropriate, but see A03 (their output is unescaped).

---

## A02 — Cryptographic Failures · **Low**

Passwords are hashed with the framework default (bcrypt) via `Hash::make`;
`Password::default()` governs strength; app data encryption uses `APP_KEY`.
*Recommendations:* add `->uncompromised()` to password rules (HaveIBeenPwned
check); enforce HTTPS in production (`URL::forceScheme('https')` / HSTS at the
edge); confirm `SESSION_SECURE_COOKIE=true` in production.

---

## A03 — Injection · **High (Stored XSS)**

**Finding 3.1 — Stored XSS via unescaped content (High).**
Public templates render editor-authored content as raw HTML:
`{!! $page->content !!}` in `resources/views/templates/default.blade.php:4` and
`templates/home.blade.php:8`, and `{!! $item->content !!}` in
`resources/views/item.blade.php:25`. Content is authored through the panel and
served to unauthenticated visitors, so a malicious or compromised author can
inject `<script>` that runs in every visitor's browser (session theft, defacement)
— and in a multi-tenant deployment, across tenants.
*Remediation:* sanitize rich HTML on save/render (e.g. `mews/purifier` or an
allow-list), or store structured/Markdown content and render through a safe
converter with escaping. Never `{!! !!}` untrusted content.

**Finding 3.2 — No SQL injection observed (Info).**
Data access is Eloquent throughout; the only `DB::` use is
`DB::transaction(...)` in `CreateUserFromProvider`. No `whereRaw`/string-built
queries found.

---

## A04 — Insecure Design · **Medium**

- Rich content is trusted implicitly (root cause of A03) — the design lacks a
  sanitization boundary between authoring and rendering.
- Open self-registration (`->registration()` + Fortify) combined with unscoped
  tenancy means new accounts’ default access needs an explicit model. Define what
  team/role a self-registered user receives.
*Remediation:* add a content-sanitization policy; document the registration →
team/role assignment flow; consider gating registration behind invite for
multi-tenant mode.

---

## A05 — Security Misconfiguration · **Medium**

**Finding 5.1 — Debug enabled in shipped env (Medium).**
`.env.example` sets `APP_DEBUG=true` and `APP_ENV=local`. A deployment copied
from the example leaks stack traces and configuration in error pages.
*Remediation:* ship `APP_DEBUG=false`, `APP_ENV=production` defaults for
non-local, and add a production readiness checklist. `.env` is correctly
gitignored.

**Finding 5.2 — Advisory blocking disabled (Low).** `config.audit.block-insecure`
is `false` (needed for the module `composer update` workflow). Acceptable given
`composer audit` remains the reporting gate (see A06), but revisit once upstream
advisories clear.

---

## A06 — Vulnerable & Outdated Components · **Medium**

`composer audit --no-dev` reports **1 production advisory**:
`phpseclib/phpseclib` (CVE-2026-55599, GHSA-m557-wrgg-6rp4). The full tree
(including dev tooling) reports more. Because `block-insecure` is off, an insecure
transitive dep will not fail `composer update`.
*Remediation:* bump `phpseclib` to the patched release (via the requiring
package); keep `composer audit` in `.github/workflows/security.yml` as a blocking
CI gate; enable Dependabot (already present) auto-PRs.

---

## A07 — Identification & Authentication Failures · **Low**

Strong posture: Fortify login throttling (5/min default, `config/fortify.php`),
two-factor authentication (`stephenjude/filament-two-factor-authentication`),
passkeys (`spatie/laravel-passkeys`), email verification support, and
`AuthenticateSession` in the panel middleware.
*Recommendations:* enforce 2FA for admin/privileged roles; add `->uncompromised()`
to password rules; confirm password-reset and email-verification throttles.

---

## A08 — Software & Data Integrity Failures · **Medium**

Uploads exist via Filament `FileUpload` in `CategoryForm` (`image`) and
`UserForm` (`profile_photo_path`).
*Remediation:* confirm each upload constrains MIME type and size
(`->image()`, `->maxSize()`, `->acceptedFileTypes()`), stores outside the webroot
or on a non-executable disk, and randomizes filenames. When the Media module
lands (Phase 2), centralize secure-upload rules there. No insecure deserialization
found.

---

## A09 — Security Logging & Monitoring Failures · **Medium**

No audit logging of security-relevant events (logins, permission/role changes,
content publish/unpublish, tenant switches). Part B §18 requires audit logging.
*Remediation:* add an audit-log listener on auth and content events (the Phase 0
`EventBus` gives a natural seam), ship with tamper-evident storage, and alert on
anomalies.

---

## A10 — Server-Side Request Forgery · **Info**

No user-controlled outbound URL fetching found. Socialstream performs
provider-side OAuth calls to fixed endpoints. Re-audit when integrations (webhooks,
remote media import, WordPress migration in Phase 6) are added.

---

## Remediation backlog (priority order)

1. **[A03] Sanitize/escape content rendering** — remove `{!! $content !!}` XSS.
2. **[A01] Enforce tenant isolation on the panel** — *in progress (multitenancy PR)*.
3. **[A05] Production config defaults + checklist** — `APP_DEBUG=false`, HTTPS/HSTS.
4. **[A01] Resource policies + Shield coverage tests.**
5. **[A06] Patch `phpseclib`; make `composer audit` a blocking CI gate.**
6. **[A09] Audit-logging via the EventBus.**
7. **[A08] Confirm/enforce upload validation.**
8. **[A02/A07] `->uncompromised()` passwords; enforce 2FA for admins.**
