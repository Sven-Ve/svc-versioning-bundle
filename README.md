# SvcVersioningBundle

[![CI](https://github.com/Sven-Ve/svc-versioning-bundle/actions/workflows/php.yml/badge.svg)](https://github.com/Sven-Ve/svc-versioning-bundle/actions/workflows/php.yml)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue)](https://php.net/)
[![Symfony](https://img.shields.io/badge/symfony-7.4%2B%20%7C%208-green)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A Symfony bundle that provides **automated semantic versioning**, **git operations**, and **deployment capabilities** for your applications. Streamline your release process with a single command.

> **⚠️ Breaking Changes in v8.0.0**
>
> - **Symfony 7.4+ required**: The bundle now uses Symfony's invokable command pattern introduced in v7.3/7.4
> - If you need Symfony 6.x or 7.0-7.3 support, use v7.x of this bundle
> - See [BREAKING_CHANGES.md](BREAKING_CHANGES.md) for migration instructions

## 🚀 Quick Start

```bash
# Install the bundle
composer require svc/versioning-bundle

# Create your first version (prompts for commit message)
bin/console svc:versioning:new --init

# Or provide commit message directly
bin/console svc:versioning:new "Initial version" --init

# Increment versions easily
bin/console svc:versioning:new "Bug fixes" --patch  # 1.0.0 → 1.0.1
bin/console svc:versioning:new "New features" --minor  # 1.0.1 → 1.1.0
bin/console svc:versioning:new "Breaking changes" --major  # 1.1.0 → 2.0.0

# Without message argument, you'll be prompted interactively
bin/console svc:versioning:new --patch
# → "Please enter the commit message:"
```

## ✨ Features

- **🏷️ Semantic Versioning**: Automatic major/minor/patch version increments
- **📝 File Generation**: Updates `.version`, `CHANGELOG.md`, and Twig templates
- **🔧 Git Integration**: Automatic commits, pushes, and tag creation
- **🚀 Multiple Deployment Options**:
  - EasyCorp EasyDeploy Bundle integration
  - Custom deployment commands
  - Ansible automation
  - Docker deployments
- **🧪 Pre-deployment Validation**: Run tests, linting, or custom commands before release
- **🔄 CI/CD Ready**: Perfect for automated pipelines
- **💎 Modern PHP**: Built with PHP 8.2+ features (readonly properties, match expressions)
- **🛡️ Type Safe**: Immutable value objects with PHPStan level 6 compliance
- **⚡ Invokable Commands**: Uses Symfony 7.4's modern invokable command pattern
- **💬 Interactive Prompts**: Asks for commit message if not provided (v8.0.0+)

## 🎯 How It Works

When you run `bin/console svc:versioning:new`, the bundle:

1. **Validates** - Runs optional pre-commands (tests, linting)
2. **Cache Check** - Optionally verifies production cache can be cleared without errors
3. **Increments** - Updates version number using semantic versioning
4. **Updates Files** - Modifies `.version`, `CHANGELOG.md`, and Twig templates
5. **Git Operations** - Commits changes, pushes, and creates tags
6. **Deploys** - Triggers deployment via your chosen method

## 📖 Documentation

### Getting Started
- **[Installation](docs/installation.md)** - Setup and bundle registration
- **[Configuration](docs/configuration.md)** - Detailed configuration options
- **[Usage](docs/usage.md)** - Basic usage and commands

### Advanced Topics
- **[Examples & Workflows](docs/examples.md)** - Real-world usage scenarios
- **[Deployment Guide](docs/deployment.md)** - All deployment options explained
- **[Troubleshooting](docs/troubleshooting.md)** - Common issues and solutions

### Development
- **[Contributing](CONTRIBUTING.md)** - Development guidelines and setup
- **[Breaking Changes](BREAKING_CHANGES.md)** - Version migration guides

## 🏗️ Requirements

- **PHP**: 8.2+ (for readonly properties and modern features)
- **Symfony**: 7.4+ or 8.x (for invokable command pattern)
- **Git**: For version control operations (optional)

> **Note**: For Symfony 6.x or 7.0-7.3 support, use v7.x of this bundle

## 📦 Installation

```bash
composer require svc/versioning-bundle
```

For non-Flex projects, register the bundle manually:
```php
// config/bundles.php
return [
    // ...
    Svc\VersioningBundle\SvcVersioningBundle::class => ['all' => true],
];
```

## ⚙️ Basic Configuration

```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    run_git: true                    # Enable git operations
    run_deploy: true                 # Enable deployment
    pre_command: "vendor/bin/phpunit" # Run tests before versioning
    check_cache_clear: false         # Check if production cache clear works
    cleanup_cache_dir: false         # Delete var/cache/prod after check
```

## 🚦 Usage Examples

```bash
# Initialize versioning (creates version 0.0.1)
bin/console svc:versioning:new "Initial version" --init

# Patch release (bug fixes)
bin/console svc:versioning:new "Fix critical bug" --patch

# Minor release (new features, backward compatible)
bin/console svc:versioning:new "Add user management" --minor

# Major release (breaking changes)
bin/console svc:versioning:new "New API version" --major

# Interactive mode - prompts for commit message
bin/console svc:versioning:new --patch
# → "Please enter the commit message:" [waits for input]

# Quick patch without specifying type (defaults to patch)
bin/console svc:versioning:new "Quick bugfix"
```

## 🔧 Deployment Options

### EasyDeploy (Default)
```bash
composer require easycorp/easy-deploy-bundle --dev
```

### Custom Commands
```yaml
svc_versioning:
    deploy_command: "./deploy.sh production"
```

### Ansible Automation
```yaml
svc_versioning:
    ansible_deploy: true
    ansible_inventory: "inventory.yml"
    ansible_playbook: "deploy.yml"
```

## 🎨 Generated Files

The bundle automatically creates and maintains:

- **`.version`** - Current version number
- **`CHANGELOG.md`** - Version history with timestamps
- **`templates/_version.html.twig`** - Twig template for displaying version

```twig
{# Example generated template #}
<span title='Release 2024-01-15 14:30:25 UTC'>Version: 1.2.3</span>
```

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 📄 License

This bundle is released under the MIT License. See [LICENSE](LICENSE) for details.

## 🙏 Credits

Created and maintained by [Sven Vetter](https://github.com/Sven-Ve).