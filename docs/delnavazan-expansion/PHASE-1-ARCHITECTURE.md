# Phase 1 Plugin Architecture

## Scope

Phase 1 establishes the Delnavazan Expansion plugin and initialises Hamnavaz as its first module. It deliberately contains no teacher directory, teacher data model, search, public profile, submission, verification, notification, payment, map, calendar, analytics, or homepage functionality.

The canonical documents in this directory define the persistent product and engineering direction. Their future-facing requirements inform module boundaries without authorising functionality beyond the approved Phase 1 scope.

## Bootstrap flow

1. WordPress loads `delnavazan-expansion.php`.
2. The bootstrap defines the version, plugin file, path, URL, and basename constants.
3. The bootstrap loads the shared lifecycle, coordinator, module loader, and Hamnavaz module classes.
4. Activation and deactivation callbacks are registered.
5. The shared `Plugin` coordinator registers translation/version hooks and passes enabled modules to `Module_Loader`.
6. Each module registers its own hooks. In Phase 1, Hamnavaz adds only a minimal administrator status page.

The bootstrap intentionally contains wiring rather than business logic.

## Module loading

Modules implement `Delnavazan\Expansion\Module`, whose single responsibility is registering that module's WordPress hooks. `Module_Loader` receives explicit module instances and registers them. Explicit registration makes enabled code reviewable and avoids file-system discovery, dynamic class names, or global state.

Future modules can be added as separate directories and registered in the coordinator. Shared services should remain under `includes/`; module-specific behaviour belongs under the relevant module directory.

## Versioning

The plugin header and `DELNAVAZAN_EXPANSION_VERSION` use semantic versioning and must be updated together. Activation stores the current version in the non-autoloaded `delnavazan_expansion_version` option. An `admin_init` check also updates it after code upgrades, providing a bounded future upgrade-orchestration point without running writes on public requests. Once schema migrations are necessary, they must use a separate data/schema version as required by `DATA-MODEL.md`.

Phase 1 is version `0.1.0`.

## Compatibility floor

The plugin header declares WordPress 6.0 and PHP 7.4 as minimum versions. Phase 1 uses syntax compatible with that PHP floor and only established WordPress APIs. These values favour compatibility with the existing, presently unverified production environment; they should be reviewed against the actual hosting runtime before deployment. The hosting platform should independently run a vendor-supported PHP release.

## Lifecycle and uninstall

- Activation stores only the plugin version. It creates no tables, roles, capabilities, content, schedules, or rewrite rules.
- Deactivation intentionally preserves persistent state and performs no work because Phase 1 creates no runtime resources that require cleanup.
- Uninstall runs only when invoked by WordPress (`WP_UNINSTALL_PLUGIN`) and removes the single plugin-owned version option.

The lifecycle is consequently reversible and does not affect Amelia, themes, WordPress core, or third-party plugins.

## Security baseline

- Every PHP entry point rejects direct access.
- The admin screen requires `manage_options`, with a second capability check at render time.
- Admin output is escaped at the point of output.
- Phase 1 has no forms or state-changing admin requests, so no nonce flow is needed yet.
- Namespaced classes and prefixed constants/options/asset handles reduce collision risk.
- No secrets, credentials, remote requests, uploads, SQL, or user input are introduced.
- The admin stylesheet is enqueued only for the plugin's exact admin hook suffix.

Future state-changing actions must add capability checks, intent-specific nonces, input validation/sanitisation, and escaped output as appropriate.

## Internationalisation and interface baseline

The `delnavazan-expansion` text domain is loaded from `/languages`, and all administrator-facing strings use WordPress translation functions. The minimal stylesheet uses CSS logical properties so its layout remains suitable for RTL interfaces. No public-facing assets or output are registered.

## Important decisions

- No custom database tables are justified for the foundation.
- Hamnavaz remains independent of Amelia.
- Module registration is explicit rather than automatic for predictability and auditability.
- No dependency manager or third-party runtime package is introduced.
- The administrator page is diagnostic only; it is not a settings or management interface.

## Known limitations and deferred work

- A runnable WordPress installation is required to validate activation, deactivation, reactivation, capability behaviour, translations, and the rendered admin screen end to end.
- Multisite-specific activation and uninstall orchestration is not included because no multisite requirement is specified.
- Translation catalog files are not generated in Phase 1.
- All teacher, public UI, automation, integration, commercial, and analytics capabilities are deferred.
