# Releases

Folio is released through two channels: Composer/Packagist for the PHP library and GitHub Releases for source archives and extension assets.

## Versioning

Folio follows [Semantic Versioning](https://semver.org/):

- `MAJOR` — incompatible API or template-language changes
- `MINOR` — new features, backwards compatible
- `PATCH` — bug fixes, backwards compatible

Release tags for the PHP package use the `v*` prefix, for example `v1.2.3`.

## Install via Composer

```bash
composer require mohammadraufzahed/folio
```

To install a specific release:

```bash
composer require mohammadraufzahed/folio:^1.0
```

Composer reads the package metadata from [Packagist](https://packagist.org/packages/mohammadraufzahed/folio).

## GitHub Releases

Source archives and release notes are published to the [GitHub Releases](https://github.com/mohammadraufzahed/folio/releases) page for every `v*` tag. GitHub auto-attaches `Source code (zip/tar.gz)` archives to each release.

## VS Code Extension Releases

The extension is released separately with `vscode-v*` tags. See the [VS Code Extension guide](../tooling/vscode.md) for how to install the `.vsix` from a release.

## Releasing a new version

Only maintainers can cut releases. The process is fully automated through GitHub Actions.

1. Make sure the target commit is on `main` and all CI checks pass.
2. Update `CHANGELOG.md` if needed.
3. Create and push a tag:

   ```bash
   git checkout main
   git pull origin main
   git tag v1.2.3
   git push origin v1.2.3
   ```

4. The [`Release`](https://github.com/mohammadraufzahed/folio/actions/workflows/release.yml) workflow:
   - runs the full test matrix on PHP 8.3 and 8.4,
   - validates `composer.json`,
   - runs static analysis and code-style checks,
   - creates a GitHub Release with auto-generated notes,
   - notifies Packagist to refresh its metadata.

## Packagist integration

The release workflow can notify Packagist automatically. Add these repository secrets at `Settings → Secrets and variables → Actions`:

- `PACKAGIST_USERNAME` — your Packagist username
- `PACKAGIST_API_TOKEN` — an API token from [packagist.org/profile](https://packagist.org/profile)

If the secrets are not set, the Packagist step is skipped and Packagist will still update through the GitHub webhook if you configured it on packagist.org.

## GitHub Packages

GitHub Packages does not natively support Composer packages today. Folio therefore uses:

- Packagist for Composer distribution.
- GitHub Releases for source archives and binary assets like `.vsix` files.

If GitHub Packages adds Composer support in the future, the release workflow can be extended to publish there as well.

## Release checklist

- [ ] Version bump in `composer.json` if you version the package there explicitly
- [ ] `CHANGELOG.md` updated
- [ ] All CI checks green on `main`
- [ ] Tag pushed as `v<major>.<minor>.<patch>`
- [ ] Packagist metadata updated
- [ ] GitHub Release published
