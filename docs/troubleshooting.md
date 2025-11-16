# Troubleshooting

This guide helps you resolve common issues when using the SvcVersioningBundle.

## Common Issues

### 1. Command Not Found

**Error:**
```
Command "svc:versioning:new" is not defined.
```

**Causes & Solutions:**

#### Bundle Not Registered
Ensure the bundle is properly registered in `config/bundles.php`:
```php
// config/bundles.php
return [
    // ...
    Svc\VersioningBundle\SvcVersioningBundle::class => ['all' => true],
];
```

#### Cache Issue
Clear the Symfony cache:
```bash
bin/console cache:clear
```

#### Bundle Not Installed
Verify the bundle is installed:
```bash
composer show svc/versioning-bundle
```

### 2. Git Operations Fail

**Error:**
```
Git command failed: fatal: not a git repository
```

**Solutions:**

#### Initialize Git Repository
```bash
git init
git remote add origin <your-repository-url>
```

#### Configure Git User
```bash
git config user.name "Your Name"
git config user.email "your.email@example.com"
```

#### Disable Git Operations
If you don't want git operations:
```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    run_git: false
```

### 3. Permission Issues

**Error:**
```
Permission denied when writing to .version file
```

**Solutions:**

#### Fix File Permissions
```bash
chmod 664 .version
# or create if missing
touch .version && chmod 664 .version
```

#### Fix Directory Permissions
```bash
chmod 755 templates/
mkdir -p templates && chmod 755 templates/
```

### 4. Pre-Command Failures

**Error:**
```
Pre-command failed with exit code 1
```

**Debugging Steps:**

#### Run Pre-Command Manually
```bash
# Test your pre-command separately
vendor/bin/phpunit
composer run-script phpstan
```

#### Disable Pre-Command Temporarily
```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    pre_command: ~  # Disable pre-command
```

#### Check Command Path
Ensure the command exists and is executable:
```bash
which phpunit
ls -la vendor/bin/phpunit
```

### 5. Deployment Issues

**Error:**
```
Deployment command failed
```

**EasyDeploy Issues:**
- Ensure `easycorp/easy-deploy-bundle` is installed:
  ```bash
  composer require easycorp/easy-deploy-bundle --dev
  ```
- Check EasyDeploy configuration in `config/packages/dev/easy_deploy.yaml`

**Custom Deployment Issues:**
- Test deployment command manually:
  ```bash
  ./deploy.sh  # or your custom command
  ```
- Check script permissions:
  ```bash
  chmod +x deploy.sh
  ```

**Ansible Deployment Issues:**
- Verify Ansible is installed:
  ```bash
  ansible --version
  ```
- Check inventory file exists:
  ```bash
  ls -la inventory.yaml
  ```
- Test Ansible connectivity:
  ```bash
  ansible all -i inventory.yaml -m ping
  ```

### 6. Version File Issues

**Error:**
```
Could not read current version from .version file
```

**Solutions:**

#### Initialize Version File
```bash
echo "0.0.0" > .version
```

#### Fix Version Format
The `.version` file should contain only the version number:
```bash
# Correct format
echo "1.2.3" > .version

# Wrong format (don't include 'v' prefix)
echo "v1.2.3" > .version  # This will cause issues
```

### 8. Template Generation Issues

**Error:**
```
Could not write to templates/_version.html.twig
```

**Solutions:**

#### Create Templates Directory
```bash
mkdir -p templates
chmod 755 templates
```

#### Fix Template Permissions
```bash
touch templates/_version.html.twig
chmod 664 templates/_version.html.twig
```

### 9. CHANGELOG.md Issues

**Error:**
```
Could not append to CHANGELOG.md
```

**Solutions:**

#### Create CHANGELOG File
```bash
echo "# Changelog" > CHANGELOG.md
```

#### Fix File Permissions
```bash
chmod 664 CHANGELOG.md
```

## Debug Mode

Enable debug output to see detailed information:

```bash
bin/console svc:versioning:new --verbose
# or
bin/console svc:versioning:new -vvv  # Very verbose
```

## Configuration Validation

Validate your configuration:

```bash
bin/console debug:config svc_versioning
```

Check if services are properly registered:

```bash
bin/console debug:container svc
```

## Environment-Specific Issues

### Development Environment
- Disable git and deployment for local testing:
```yaml
# config/packages/dev/svc_versioning.yaml
svc_versioning:
    run_git: false
    run_deploy: false
```

### Production Environment
- Ensure all required tools are installed (git, ansible, etc.)
- Check environment variables are set
- Verify file permissions for web server user

### CI/CD Environment
- Use `--no-interaction` flag for automated runs:
```bash
bin/console svc:versioning:new --no-interaction
```
- Set up proper SSH keys for git operations
- Configure environment variables in CI system

## Performance Issues

### Slow Pre-Commands
If tests or static analysis are slow:
- Run only essential tests in pre-command
- Use parallel execution where possible
- Consider using different pre-commands per environment

### Large Repository Issues
For repositories with large history:
- Use shallow clones in CI: `git clone --depth 1`
- Consider using git worktrees for deployment

## Getting Help

### Enable Logging
Add logging to see what's happening:
```yaml
# config/packages/dev/monolog.yaml
monolog:
    handlers:
        main:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
```

### Common Log Locations
- Symfony logs: `var/log/dev.log` or `var/log/prod.log`
- Web server logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`

### Reporting Issues
When reporting issues, include:
1. Symfony version
2. PHP version  
3. Bundle version
4. Full error message
5. Relevant configuration
6. Steps to reproduce

### Community Support
- GitHub Issues: [svc-versioning-bundle/issues](https://github.com/Sven-Ve/svc-versioning-bundle/issues)
- Stack Overflow: Use tags `symfony` and `versioning`