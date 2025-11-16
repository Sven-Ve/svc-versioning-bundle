# Contributing to SvcVersioningBundle

Thank you for your interest in contributing to SvcVersioningBundle! This document provides guidelines and information for contributors.

## 🚀 Getting Started

### Prerequisites

- **PHP**: 7.3+ or 8.x
- **Composer**: Latest version
- **Git**: For version control
- **Symfony**: 6.x or 7.x knowledge

### Development Setup

1. **Fork and clone the repository**
   ```bash
   git clone https://github.com/your-username/svc-versioning-bundle.git
   cd svc-versioning-bundle
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Run tests to ensure everything works**
   ```bash
   composer run-script test
   # or
   vendor/bin/phpunit
   ```

4. **Run static analysis**
   ```bash
   composer run-script phpstan
   ```

## 🏗️ Project Structure

```
src/
├── Command/              # Console commands
│   └── VersioningCommand.php
├── Service/              # Core business logic
│   ├── VersionHandling.php
│   ├── VersionString.php
│   └── VersionFile.php
├── SvcVersioningBundle.php  # Main bundle class
└── config/
    └── services.yaml     # Service definitions

tests/                    # Test suite
├── Command/              # Command tests
├── Service/              # Service tests
└── SvcVersioningBundleTest.php

docs/                     # Documentation
├── installation.md
├── usage.md
├── configuration.md
├── examples.md
├── deployment.md
└── troubleshooting.md
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
composer run-script test

# Run specific test class
vendor/bin/phpunit tests/Service/VersionHandlingTest.php

# Run tests with coverage (requires xdebug)
vendor/bin/phpunit --coverage-html coverage/
```

### Writing Tests

- **Unit Tests**: Test individual classes in isolation
- **Integration Tests**: Test command execution and service interaction
- **File System Tests**: Use temporary directories for file operations
- **Mock External Dependencies**: Git operations

#### Test Example

```php
<?php

namespace Svc\VersioningBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Svc\VersioningBundle\Service\VersionString;

class VersionStringTest extends TestCase
{
    private VersionString $versionString;

    protected function setUp(): void
    {
        $this->versionString = new VersionString();
    }

    public function testIncrementPatch(): void
    {
        $result = $this->versionString->incrementVersion('1.2.3', 'patch');
        $this->assertEquals('1.2.4', $result);
    }
}
```

## 🔍 Code Quality

### Static Analysis

We use PHPStan at level 5 for static analysis:

```bash
composer run-script phpstan
```

### Code Style

Follow PSR-12 coding standards. Key points:

- Use strict types: `declare(strict_types=1);`
- Type hint all parameters and return types
- Use meaningful variable and method names
- Add docblocks for complex methods

### Pre-commit Checks

Before committing, ensure:

1. **Tests pass**: `composer run-script test`
2. **Static analysis passes**: `composer run-script phpstan`
3. **No syntax errors**: `php -l src/**/*.php`

## 🐛 Reporting Issues

### Before Reporting

1. **Search existing issues** to avoid duplicates
2. **Test with latest version** to ensure issue still exists
3. **Prepare minimal reproduction case**

### Issue Template

```markdown
**Bug Description**
Clear description of the bug

**Steps to Reproduce**
1. Run command: `bin/console svc:versioning:new`
2. Expected: ...
3. Actual: ...

**Environment**
- PHP version: 8.2
- Symfony version: 6.4
- Bundle version: 5.3.0
- OS: Ubuntu 22.04

**Additional Context**
Any relevant configuration or logs
```

## 🔧 Feature Requests

### Before Requesting

1. **Check if feature already exists** in documentation
2. **Consider if it fits the bundle's scope**
3. **Think about backward compatibility**

### Feature Template

```markdown
**Feature Description**
Clear description of the proposed feature

**Use Case**
Why is this feature needed? What problem does it solve?

**Proposed Implementation**
How do you envision this working?

**Alternatives Considered**
What other approaches were considered?
```

## 📝 Pull Requests

### Before Submitting

1. **Create an issue** to discuss the change (for non-trivial changes)
2. **Fork the repository** and create a feature branch
3. **Write tests** for new functionality
4. **Update documentation** if needed
5. **Ensure all checks pass**

### Pull Request Process

1. **Create feature branch**
   ```bash
   git checkout -b feature/my-new-feature
   ```

2. **Make changes and commit**
   ```bash
   git add .
   git commit -m "Add new feature: description"
   ```

3. **Push to your fork**
   ```bash
   git push origin feature/my-new-feature
   ```

4. **Create pull request** with:
   - Clear title and description
   - Link to related issue
   - Screenshots/examples if applicable

### Pull Request Template

```markdown
## Description
Brief description of changes

## Related Issue
Fixes #123

## Type of Change
- [ ] Bug fix (non-breaking change)
- [ ] New feature (non-breaking change)
- [ ] Breaking change (fix or feature that would cause existing functionality to change)
- [ ] Documentation update

## Testing
- [ ] Tests pass locally
- [ ] New tests added for new functionality
- [ ] Manual testing completed

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No breaking changes (or properly documented)
```

## 🏷️ Versioning Strategy

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Process

1. **Update CHANGELOG.md** with new version
2. **Tag release** with version number
3. **Create GitHub release** with release notes
4. **Update documentation** if needed

## 📚 Documentation

### Writing Documentation

- **Use clear, concise language**
- **Provide practical examples**
- **Keep formatting consistent**
- **Test all code examples**

### Documentation Structure

- **Installation**: Getting started quickly
- **Configuration**: All available options
- **Usage**: Basic commands and workflows
- **Examples**: Real-world scenarios
- **Deployment**: Integration patterns
- **Troubleshooting**: Common issues

## 🎯 Development Guidelines

### Design Principles

1. **Single Responsibility**: Each class has one clear purpose
2. **Dependency Injection**: Use Symfony's DI container
3. **Testability**: Write testable code with clear interfaces
4. **Configuration**: Make behavior configurable
5. **Error Handling**: Provide clear error messages

### Adding New Features

1. **Service Layer**: Implement business logic in services
2. **Command Layer**: Add commands for user interaction
3. **Configuration**: Add configuration options if needed
4. **Tests**: Write comprehensive tests
5. **Documentation**: Update docs with examples

### Breaking Changes

- **Avoid when possible**
- **Document clearly** in CHANGELOG.md
- **Provide migration path**
- **Bump major version**

## 🤝 Community

### Getting Help

- **GitHub Issues**: For bugs and feature requests
- **Discussions**: For questions and general discussion
- **Email**: Contact maintainer for sensitive issues

### Code of Conduct

We follow the Symfony Code of Conduct:

- **Be respectful** and inclusive
- **Be collaborative** and constructive
- **Focus on what's best** for the community
- **Show empathy** towards other community members

## 📄 License

By contributing, you agree that your contributions will be licensed under the MIT License.

## 🙏 Recognition

Contributors will be recognized in:

- **GitHub contributors list**
- **CHANGELOG.md** for significant contributions
- **README.md** credits section (for major contributors)

Thank you for contributing to SvcVersioningBundle! 🎉