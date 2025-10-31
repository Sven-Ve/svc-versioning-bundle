# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SvcVersioningBundle is a Symfony bundle that provides automated semantic versioning, git operations, and deployment capabilities. It handles version increments (major/minor/patch), updates version files, manages git commits/tags, and can trigger deployments.

## Core Commands

### Development Commands
- `composer install` - Install dependencies
- `composer run-script phpstan` - Run static analysis (PHPStan level 6)
- `composer run-script test` or `vendor/bin/phpunit` - Run test suite
- `composer validate --strict` - Validate composer.json and composer.lock

### Bundle Usage
- `bin/console svc:versioning:new` - Create new version (defaults to patch increment)
- `bin/console svc:versioning:new --major` - Increment major version
- `bin/console svc:versioning:new --minor` or `-m` - Increment minor version  
- `bin/console svc:versioning:new --patch` or `-p` - Increment patch version
- `bin/console svc:versioning:new --init` or `-i` - Initialize versioning (0.0.1)
- `bin/console svc:versioning:new "Custom commit message"` - Use custom commit message

## Architecture

### Bundle Structure
- **SvcVersioningBundle** (`src/SvcVersioningBundle.php`): Main bundle class with configuration definition
- **VersioningCommand** (`src/Command/VersioningCommand.php`): Console command for version management
- **Value Objects**:
  - `Version`: Immutable value object representing semantic versions (major.minor.patch)
    - Readonly properties with PHP 8.2+ features
    - Self-validating (no negative version numbers)
    - Increment methods: `incrementMajor()`, `incrementMinor()`, `incrementPatch()`
    - Comparison methods: `isGreaterThan()`, `equals()`
    - Factory methods: `fromString()`, `initial()`
- **Service Layer**:
  - `VersionHandling`: Core version logic and file operations
  - `VersionString`: Version string parsing/formatting (delegates to Version value object)
  - `VersionFile`: File I/O operations for version files
  - `SentryReleaseHandling`: Optional Sentry integration

### Workflow Process
1. Run optional pre-command (tests, linting, etc.)
2. Calculate new version based on current version and increment type
3. Write version to `.version` file
4. Generate Twig template (`templates/_version.html.twig`)
5. Append version entry to `CHANGELOG.md`
6. Optional: Create Sentry release
7. Optional: Git operations (add, commit, push, tag)
8. Optional: Deploy via EasyDeploy bundle, custom command, or Ansible

### Configuration
Bundle configuration is defined in `config/packages/svc_versioning.yaml` with options for:
- Git operations (`run_git`)
- Deployment settings (`run_deploy`, `deploy_command`, `ansible_deploy`)
- Pre-execution commands (`pre_command`)
- Sentry integration (`create_sentry_release`, `sentry_app_name`)

### Key Files Generated
- `.version` - Current version number
- `templates/_version.html.twig` - Twig template with version display
- `CHANGELOG.md` - Version history with timestamps

## Dependencies
- PHP 8.2+ (required for readonly properties and modern features)
- Symfony 6 or 7 (console, http-kernel, dependency-injection, config, yaml)
- Optional: easycorp/easy-deploy-bundle for deployment
- PHPStan 2.1+ for static analysis (level 6)
- PHPUnit 12.4+ for testing

## Modern PHP Features Used
- **Readonly properties** (PHP 8.2): Immutable value objects
- **Match expressions** (PHP 8.0): Clean version increment logic
- **Constructor property promotion** (PHP 8.0): Concise value object definitions
- **Named arguments** (PHP 8.0): Improved readability
- **Typed properties** (PHP 7.4+): Strict type safety

## Code Quality & Architecture Patterns

### Domain-Driven Design (DDD)
The bundle uses DDD principles with clear separation of concerns:
- **Value Objects**: `Version` represents domain concepts with business rules
- **Services**: Orchestrate operations without holding state
- **Immutability**: Value objects cannot be modified after creation

### Type Safety
- PHPStan level 6 with strict rules
- Full PHPDoc annotations for complex types
- Readonly properties prevent accidental mutations
- No mixed types or suppressed errors

### Best Practices
- **Immutable Value Objects**: All version operations return new instances
- **Self-Validating Objects**: Invalid states are impossible to create
- **Single Responsibility**: Each class has one clear purpose
- **Explicit over Implicit**: Clear method names like `incrementMajor()` instead of cryptic operations

## Testing
Comprehensive test suite covering all components:
- **Value Object Tests**: Version value object with 20 tests covering immutability, validation, and comparisons
- **Service Tests**: All service classes have unit tests with file system isolation
- **Command Tests**: VersioningCommand tested with Symfony CommandTester
- **Bundle Tests**: Configuration and dependency injection testing
- **Test Configuration**: PHPUnit XML configuration with proper test structure
- **Test Coverage**: 71 tests, 182 assertions
- bitte changelog.md nicht direkt updaten. bin/release.php wird für den Release-prozess benutzt und dort wird der Release-Kommentar gepflegt