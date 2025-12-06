# Breaking Changes

This document tracks all breaking changes in the SvcVersioningBundle.

## Version 8.0.0

### Changed: Minimum Symfony Version & Invokable Command Pattern

**Date:** 2025-12-06

The bundle now requires **Symfony 7.4+ or 8.x** and uses the modern invokable command pattern.

#### What changed:

- Minimum Symfony version: `6.x/7.x` → `7.4+`
- `VersioningCommand` now uses `__invoke()` instead of `execute()`
- Command parameters defined with `#[Argument]` and `#[Option]` attributes
- `commitMessage` can be provided as argument or will be prompted interactively via `#[Ask]`
- Uses `SymfonyStyle` for output handling

#### Migration Guide:

**For Bundle Users:**

1. **Upgrade Symfony to 7.4+ or 8.x:**

   ```bash
   # Check current Symfony version
   composer show symfony/console

   # Upgrade to Symfony 7.4 or 8.x
   composer require "symfony/console:^7.4" # or ^8.0
   ```

2. **Command usage now supports interactive prompts:**

   ```bash
   # Provide commit message as argument (works as before)
   bin/console svc:versioning:new "commit message" --patch

   # NEW: Interactive prompt if no message provided
   bin/console svc:versioning:new --patch
   # → "Please enter the commit message:" [waits for input]
   ```

3. **If you need Symfony 6.x or 7.0-7.3 support:**

   Use v7.x of this bundle:
   ```bash
   composer require "svc/versioning-bundle:^7.0"
   ```

**For Bundle Developers/Contributors:**

The command now uses the invokable pattern:

```php
// Before (v7.x):
protected function execute(InputInterface $input, OutputInterface $output): int
{
    $io = new SymfonyStyle($input, $output);
    $commitMessage = $input->getArgument('commitMessage');
    // ...
}

// After (v8.x):
public function __invoke(
    SymfonyStyle $io,
    #[Argument(name: 'commitMessage', description: 'Commit message')]
    #[Ask('Please enter the commit message:')]
    string $commitMessage,
    #[Option(shortcut: 'p', description: 'Add patch version')] bool $patch = false,
): int {
    // Direct parameter access, no need for $input->getArgument()
    // Interactive prompt via #[Ask] if commitMessage not provided
    // ...
}
```

#### Why was it changed?

- Leverages Symfony 7.4's modern command features
- Cleaner, more maintainable code
- Better IDE support and type safety
- Automatic parameter extraction from method signature
- Aligns with modern Symfony best practices

#### Benefits:

- 🎯 Type-safe parameters with full IDE autocomplete
- 🧹 Cleaner code without manual argument extraction
- 📝 Self-documenting command signatures
- ✨ Modern attribute-based configuration
- 💬 Interactive prompts improve user experience when running commands manually

---

## Version 7.0.0

### Removed: Sentry Integration

**Date:** 2025-11-16

The Sentry integration has been completely removed from the bundle.

#### What was removed:

- `SentryReleaseHandling` service class
- Configuration parameters:
  - `create_sentry_release`
  - `sentry_app_name`
- Automatic Sentry release creation during versioning
- All Sentry-related dependencies and code

#### Migration Guide:

If you were using the Sentry integration (`create_sentry_release: true`), you need to:

1. **Remove Sentry configuration from your bundle config:**

   ```yaml
   # config/packages/svc_versioning.yaml
   svc_versioning:
       # Remove these lines:
       # create_sentry_release: true
       # sentry_app_name: "my-app"
   ```

2. **Alternative: Use deploy hooks or custom commands**

   If you still need Sentry release tracking, implement it via:

   **Option A: Custom deployment command**
   ```yaml
   svc_versioning:
       deploy_command: "./deploy.sh"  # Include Sentry release in your deploy script
   ```

   **Option B: Ansible playbook**
   ```yaml
   svc_versioning:
       ansible_deploy: true
       ansible_playbook: "deploy.yml"  # Include Sentry release in playbook
   ```

   **Option C: Manual Sentry release**
   ```bash
   # After versioning
   bin/console svc:versioning:new --minor "New features"

   # Manually create Sentry release
   VERSION=$(cat .version)
   sentry-cli releases new "$VERSION"
   sentry-cli releases finalize "$VERSION"
   ```

#### Why was it removed?

- Reduces bundle complexity and dependencies
- Sentry integration is better handled in deployment scripts
- Follows single responsibility principle
- Most users don't use this feature

---

### Changed: Service Configuration Format

**Date:** 2025-11-16 (already in v6.1.0)

The bundle now uses PHP-based service configuration instead of YAML.

#### What changed:

- `config/services.yaml` → `config/services.php`
- Service definitions now use modern PHP 8.2+ syntax
- Better IDE support and type safety

#### Migration Guide:

**No action required** - this is an internal change that doesn't affect bundle users. Your application configuration remains in YAML format (`config/packages/svc_versioning.yaml`).

This change only affects bundle contributors who work on the codebase.

---

## Version 6.0.0

### Changed: Minimum PHP Version

**Date:** 2025-01

The bundle now requires **PHP 8.2 or higher**.

#### What changed:

- Minimum PHP version: `8.1` → `8.2`
- Introduction of readonly properties
- Modern PHP 8.2+ features

#### Migration Guide:

Upgrade your PHP version to 8.2 or higher:

```bash
# Check current PHP version
php -v

# Upgrade PHP (example for Homebrew on macOS)
brew upgrade php

# Or use Docker/your package manager
```

### Changed: Immutable Version Value Object

**Date:** 2025-01

The `Version` class is now an immutable value object with readonly properties.

#### What changed:

- All version operations return new instances
- Properties are readonly (cannot be modified after creation)
- Stricter type safety with PHPStan level 6

#### Migration Guide:

If you were extending or modifying version objects directly, you need to use the provided methods:

```php
// Before (no longer possible):
$version->major = 2;

// After (use increment methods):
$newVersion = $version->incrementMajor();
```

---

## Version 3.0.0

### Changed: Minimum Symfony and PHP Versions

**Date:** 2022-05

The bundle now requires **Symfony 5.4/6.0+** and **PHP 8.0+**.

#### Migration Guide:

Upgrade to Symfony 5.4 or 6.0 and PHP 8.0 or higher.
