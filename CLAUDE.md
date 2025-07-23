# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SvcVersioningBundle is a Symfony bundle that provides automated semantic versioning, git operations, and deployment capabilities. It handles version increments (major/minor/patch), updates version files, manages git commits/tags, and can trigger deployments.

## Core Commands

### Development Commands
- `composer install` - Install dependencies
- `composer run-script phpstan` - Run static analysis (PHPStan level 5)
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
- **Service Layer**:
  - `VersionHandling`: Core version logic and file operations
  - `VersionString`: Version string parsing/formatting utilities
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
- PHP 7.3+ or 8.x
- Symfony 6 or 7 (console, http-kernel, dependency-injection, config, yaml)
- Optional: easycorp/easy-deploy-bundle for deployment
- PHPStan for static analysis
- PHPUnit for testing

## Testing
Comprehensive test suite covering all components:
- **Service Tests**: All service classes have unit tests with file system isolation
- **Command Tests**: VersioningCommand tested with Symfony CommandTester
- **Bundle Tests**: Configuration and dependency injection testing
- **Test Configuration**: PHPUnit XML configuration with proper test structure