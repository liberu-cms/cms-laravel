# Open Questions

Ambiguities and deferred decisions, per Part A §9. Each has a chosen default so
work continues; revisit when the owning phase arrives.

## Architecture & dependencies

1. **`internachi/modular` is now unused.** Phase 0 hand-rolls
   `packages/liberu-cms/*` (project decision). The `internachi/modular` package and
   its `app-modules/` autoload/testsuite wiring remain installed but unused.
   **Default:** leave installed for now. **Decision needed:** remove it (and the
   `Modules\` autoload + `app-modules/*` phpunit entry) to avoid two module systems.

2. **Filament Shield vs. `spatie/laravel-permission`.** Both are installed and the
   existing app uses Spatie's `permission_tables` migration plus a `Role` model.
   **Default:** unchanged in Phase 0. **Decision needed (Phase 1):** the Users
   module exposes one permission contract; decide which library backs it and keep
   the other from leaking across module boundaries (Golden Rule 2).

3. **Existing host-app CMS code.** `app/Models` and `app/Filament` already contain
   Page, Menu, Collection, Category, Tag, etc., violating Golden Rule 1 (feature
   code in the host). **Default (agreed):** leave in place; migrate into modules in
   their proper phases (Pages/Posts/Media → Phase 2, Menu/Theme → Phase 3), keeping
   `main` green throughout.

## Quality gates

4. **Pre-existing style debt.** `vendor/bin/pint --test` flags many existing
   `app/`, `config/`, `database/`, and `tests/` files. **Default:** the CI Pint gate
   is scoped to Phase 0 code (`packages/liberu-cms`, `tests/*/Cms`), which is clean.
   **Decision needed:** run repo-wide `pint` in a dedicated formatting commit.

5. **PHPStan scope.** Max level runs on `packages/liberu-cms/*/src` only. **Decision
   needed:** raise the pre-existing `app/` to a baseline (e.g. level 5) then climb.

6. **Infection is non-blocking.** Local Herd PHP has no pcov/xdebug, so MSI could not
   be measured here; the CI step runs with `continue-on-error: true`. **Decision
   needed:** calibrate `minMsi`/`minCoveredMsi` against a real CI run, then make it
   blocking.

## Security

7. **`audit.block-insecure: false`.** Enables the module `composer update` workflow
   on this locked dev stack (upstream transitive advisories on guzzle, psr7,
   composer/composer). `composer install` (CI) does not re-solve and is unaffected.
   **Mitigation:** keep `composer audit` in `.github/workflows/security.yml` as the
   reporting gate. **Decision needed:** revisit once upstream deps clear the
   advisories.

## Dev environment

8. **PostgreSQL not in `docker-compose.yml`.** Compose ships MySQL + Redis +
   Meilisearch + Mailpit. Database code is written portably (schema/query builder),
   but the dev stack only spins up MySQL. **Default:** MySQL for dev. **Decision
   needed:** add a `postgres` service behind a compose profile and run the suite
   against both in CI (Phase 6 portability DoD).

## Framework

9. **Pest 5 vs Pest 4.** Guidelines mention Pest 4; the repo installs Pest 5.x-dev.
   **Default:** build on Pest 5 (installed). No action expected.
