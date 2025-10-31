# SvcVersioningBundle

[![CI](https://github.com/Sven-Ve/svc-versioning-bundle/actions/workflows/php.yml/badge.svg)](https://github.com/Sven-Ve/svc-versioning-bundle/actions/workflows/php.yml)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue)](https://php.net/)
[![Symfony](https://img.shields.io/badge/symfony-6%20%7C%207-green)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A Symfony bundle that provides **automated semantic versioning**, **git operations**, and **deployment capabilities** for your applications. Streamline your release process with a single command.

## 🚀 Quick Start

```bash
# Install the bundle
composer require svc/versioning-bundle

# Create your first version
bin/console svc:versioning:new --init

# Increment versions easily
bin/console svc:versioning:new --patch  # 1.0.0 → 1.0.1
bin/console svc:versioning:new --minor  # 1.0.1 → 1.1.0
bin/console svc:versioning:new --major  # 1.1.0 → 2.0.0
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
- **📊 Sentry Integration**: Automatic release tracking
- **🔄 CI/CD Ready**: Perfect for automated pipelines
- **💎 Modern PHP**: Built with PHP 8.2+ features (readonly properties, match expressions)
- **🛡️ Type Safe**: Immutable value objects with PHPStan level 6 compliance

## 🎯 How It Works

When you run `bin/console svc:versioning:new`, the bundle:

1. **Validates** - Runs optional pre-commands (tests, linting)
2. **Increments** - Updates version number using semantic versioning
3. **Updates Files** - Modifies `.version`, `CHANGELOG.md`, and Twig templates
4. **Git Operations** - Commits changes, pushes, and creates tags
5. **Deploys** - Triggers deployment via your chosen method
6. **Tracks** - Optional Sentry release creation

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

## 🏗️ Requirements

- **PHP**: 8.2+ (for readonly properties and modern features)
- **Symfony**: 6.x or 7.x
- **Git**: For version control operations (optional)

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
    create_sentry_release: false     # Sentry integration
```

## 🚦 Usage Examples

```bash
# Initialize versioning (creates version 0.0.1)
bin/console svc:versioning:new --init

# Patch release (bug fixes)
bin/console svc:versioning:new --patch "Fix critical bug"

# Minor release (new features, backward compatible)
bin/console svc:versioning:new --minor "Add user management"

# Major release (breaking changes)
bin/console svc:versioning:new --major "New API version"

# Quick patch with default message
bin/console svc:versioning:new
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