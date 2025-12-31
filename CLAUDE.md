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
- `bin/console svc:versioning:new` - Create new version (prompts for commit message, defaults to patch increment)
- `bin/console svc:versioning:new "Commit message"` - Provide commit message as argument
- `bin/console svc:versioning:new "Message" --major` - Increment major version
- `bin/console svc:versioning:new "Message" --minor` or `-m` - Increment minor version
- `bin/console svc:versioning:new "Message" --patch` or `-p` - Increment patch version
- `bin/console svc:versioning:new "Message" --init` or `-i` - Initialize versioning (0.0.1)
- `bin/console svc:versioning:new "Message" --ignore-audit` - Override composer audit check (emergency use only)

**Note**: If no commit message is provided as argument, the command will prompt you interactively (v8.0.0+)

## Architecture

### Bundle Structure
- **SvcVersioningBundle** (`src/SvcVersioningBundle.php`): Main bundle class with configuration definition
- **VersioningCommand** (`src/Command/VersioningCommand.php`): Invokable console command for version management (Symfony 7.4+ pattern)
- **Service Configuration** (`config/services.php`): PHP-based service container configuration (migrated from YAML in v6.1.0)
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

### Workflow Process
1. Run optional pre-command (tests, linting, etc.)
2. Run composer audit to check for security vulnerabilities (enabled by default, can be overridden with `--ignore-audit`)
3. Optional: Check production cache clear
4. Calculate new version based on current version and increment type
5. Write version to `.version` file
6. Generate Twig template (`templates/_version.html.twig`)
7. Append version entry to `CHANGELOG.md`
8. Optional: Git operations (add, commit, push, tag)
9. Optional: Deploy via EasyDeploy bundle, custom command, or Ansible

### Configuration
Bundle configuration is defined in `config/packages/svc_versioning.yaml` with options for:
- Git operations (`run_git`)
- Deployment settings (`run_deploy`, `deploy_command`, `ansible_deploy`)
- Pre-execution commands (`pre_command`)
- Security checks (`run_composer_audit` - default: true)

### Key Files Generated
- `.version` - Current version number
- `templates/_version.html.twig` - Twig template with version display
- `CHANGELOG.md` - Version history with timestamps

## Dependencies
- PHP 8.2+ (required for readonly properties and modern features)
- Symfony 7.4+ or 8+ (console, http-kernel, dependency-injection, config, yaml)
  - **Breaking Change in v8.0.0**: Requires Symfony 7.4+ for invokable command pattern
- Optional: easycorp/easy-deploy-bundle for deployment
- PHPStan 2.1+ for static analysis (level 6)
- PHPUnit 12.4+ for testing

## Modern PHP Features Used
- **Readonly properties** (PHP 8.2): Immutable value objects
- **Match expressions** (PHP 8.0): Clean version increment logic
- **Constructor property promotion** (PHP 8.0): Concise value object definitions
- **Named arguments** (PHP 8.0): Improved readability
- **Typed properties** (PHP 7.4+): Strict type safety
- **PHP-based service configuration** (Symfony 6.3+): Type-safe service definitions with IDE support
- **Invokable commands** (Symfony 7.3+): Modern command pattern with `__invoke()` method
- **#[Argument] and #[Option] attributes** (Symfony 7.3+): Clean parameter definitions directly in method signature
- **#[Ask] attribute** (Symfony 7.3+): Interactive prompts for missing arguments

## Code Quality & Architecture Patterns

### Invokable Command Pattern (v8.0.0+)
The `VersioningCommand` uses Symfony 7.4's invokable command pattern:
- **`__invoke()` method**: Replaces traditional `execute()` for cleaner code
- **Attribute-based configuration**: `#[Argument]` and `#[Option]` attributes define parameters inline
- **Interactive prompts**: `#[Ask]` attribute prompts for commitMessage if not provided
- **Required arguments**: `commitMessage` can be passed as argument or will be prompted interactively
- **Explicit naming**: Uses `#[Argument(name: 'commitMessage')]` for clarity
- **SymfonyStyle**: Preferred over OutputInterface for modern output handling
- **Parameter order**: Service dependencies → Arguments → Options

Example:
```php
public function __invoke(
    SymfonyStyle $io,
    #[Argument(name: 'commitMessage', description: 'Commit message')]
    #[Ask('Please enter the commit message:')]
    string $commitMessage,
    #[Option(shortcut: 'm', description: 'Add minor version')] bool $minor = false,
): int { /* ... */ }
```

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
- **Test Coverage**: 80 tests, 200 assertions
- bitte changelog.md nicht direkt updaten. bin/release.php wird für den Release-prozess benutzt und dort wird der Release-Kommentar gepflegt