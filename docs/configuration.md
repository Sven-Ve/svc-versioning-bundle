# Configuration

The SvcVersioningBundle can be configured through the `config/packages/svc_versioning.yaml` file. All configuration options are optional and have sensible defaults.

## Configuration Reference

```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    # Git Operations
    run_git: true                    # Enable git operations (commit, push, tag)

    # Pre-execution Commands
    pre_command: ~                   # Command to run before versioning (e.g., tests, linting)

    # Security Checks
    run_composer_audit: true         # Run composer audit to check for security vulnerabilities

    # Cache Validation
    check_cache_clear: false         # Check if production cache clear runs without errors
    cleanup_cache_dir: false         # Delete var/cache/prod directory after successful check

    # Deployment Options
    run_deploy: true                 # Enable deployment after versioning
    deploy_command: ~                # Custom deployment command (overrides EasyDeploy)

    # Ansible Deployment
    ansible_deploy: false            # Use Ansible for deployment
    ansible_inventory: inventory.yaml # Ansible inventory file name
    ansible_playbook: ~              # Ansible playbook to execute
```

## Configuration Options Explained

### Git Operations (`run_git`)

**Default:** `true`

Controls whether git operations are performed after version increment:
- Adds modified files to git staging area
- Commits changes with version message
- Pushes to remote repository
- Creates and pushes version tag

```yaml
svc_versioning:
    run_git: false  # Disable git operations entirely
```

### Pre-execution Command (`pre_command`)

**Default:** `null`

Command to execute before starting the versioning process. If this command fails, versioning is aborted.

**Common use cases:**
- Running tests
- Static code analysis
- Code formatting checks
- Build processes

```yaml
svc_versioning:
    pre_command: "composer run-script phpstan"
    # or
    pre_command: "vendor/bin/phpunit && composer run-script phpstan"
```

### Security Check (`run_composer_audit`)

**Default:** `true`

Runs `composer audit` to check for known security vulnerabilities in your dependencies before creating a new version. If vulnerabilities are detected, the versioning process is aborted.

**Why it's enabled by default:**
- Security-first approach: catches vulnerabilities before release
- Prevents accidentally deploying applications with known security issues
- Complies with security best practices for production releases

```yaml
svc_versioning:
    run_composer_audit: true  # Default, can be omitted
```

**To disable (not recommended):**
```yaml
svc_versioning:
    run_composer_audit: false
```

**How it works:**
- Executes `composer audit` after pre_command
- Checks composer.lock for packages with known security advisories
- If vulnerabilities found (exit code > 0), versioning is aborted
- Displays vulnerability details from composer audit output
- Shows clear error message with remediation instructions

**Requirements:**
- Composer 2.4+ (audit command introduced in Composer 2.4)
- composer.lock file must exist and be up to date

**Example output on failure:**
```
Running composer audit to check for security vulnerabilities...
[composer audit output showing vulnerabilities]
Error: Security vulnerabilities detected by composer audit. Versioning canceled.
Error: Please review and fix vulnerabilities before creating a new version.
Error: Use --ignore-audit to override this check (not recommended).
```

**Emergency override:**

In exceptional circumstances (e.g., critical hotfix when no patch is available yet), you can bypass the audit check:

```bash
bin/console svc:versioning:new "Emergency hotfix" --patch --ignore-audit
```

**Warning:** Using `--ignore-audit` will:
- Still run composer audit and display vulnerabilities
- Show warnings about detected issues
- Continue with the release despite vulnerabilities
- This should only be used in emergency situations

### Cache Validation

#### Check Cache Clear (`check_cache_clear`)

**Default:** `false`

Checks if the production cache can be cleared without errors after the pre_command has run. This ensures that your application's production cache is working correctly before proceeding with the release.

```yaml
svc_versioning:
    check_cache_clear: true
```

**How it works:**
- Executes `bin/console cache:clear --env=prod --no-debug`
- If the command fails, the entire versioning process is aborted
- Useful for catching cache warmup issues before deployment

#### Cleanup Cache Directory (`cleanup_cache_dir`)

**Default:** `false`

When enabled (and `check_cache_clear` is also enabled), deletes the `var/cache/prod` directory after a successful cache clear check. This ensures the cache directory doesn't consume unnecessary disk space in your repository.

```yaml
svc_versioning:
    check_cache_clear: true
    cleanup_cache_dir: true
```

**Note:** This option only takes effect when `check_cache_clear` is `true` and the cache clear was successful.

### Deployment Settings

#### Basic Deployment (`run_deploy`)

**Default:** `true`

When enabled, triggers deployment after successful versioning. The default behavior uses the EasyCorp EasyDeploy bundle if installed.

```yaml
svc_versioning:
    run_deploy: false  # Disable deployment
```

#### Custom Deployment Command (`deploy_command`)

**Default:** `null`

Override the default EasyDeploy deployment with a custom command.

```yaml
svc_versioning:
    deploy_command: "bin/console app:deploy"
    # or
    deploy_command: "./deploy.sh production"
```

#### Ansible Deployment

**Default:** `false`

Use Ansible for deployment instead of EasyDeploy or custom commands.

```yaml
svc_versioning:
    ansible_deploy: true
    ansible_inventory: "production.yml"
    ansible_playbook: "deploy.yml"
```

**Requirements for Ansible deployment:**
- Ansible must be installed and accessible via command line
- Inventory and playbook files must exist in your project root
- Proper SSH keys and permissions configured

## Configuration Examples

### Development Environment
```yaml
# config/packages/dev/svc_versioning.yaml
svc_versioning:
    run_git: false
    run_deploy: false
    pre_command: "vendor/bin/phpunit --testsuite=unit"
    run_composer_audit: false  # Optional: skip audit in dev for faster iterations
```

### Production Environment
```yaml
# config/packages/prod/svc_versioning.yaml
svc_versioning:
    run_git: true
    run_deploy: true
    pre_command: "composer run-script phpstan && vendor/bin/phpunit"
    run_composer_audit: true  # Ensure no vulnerabilities in production
    check_cache_clear: true
    cleanup_cache_dir: true
    ansible_deploy: true
    ansible_inventory: "production.yml"
    ansible_playbook: "deploy.yml"
```

### Minimal Configuration
```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    run_git: true
    run_deploy: false
    pre_command: "vendor/bin/phpunit"
    # run_composer_audit defaults to true (recommended)
```

## Environment-Specific Configuration

You can override configuration for specific environments by creating environment-specific configuration files:

- `config/packages/dev/svc_versioning.yaml` - Development overrides
- `config/packages/test/svc_versioning.yaml` - Test environment overrides  
- `config/packages/prod/svc_versioning.yaml` - Production overrides

## Validation

The bundle validates your configuration at runtime. Common validation errors:

- **Missing Ansible files**: If `ansible_deploy: true` but inventory/playbook files don't exist
- **Invalid pre_command**: If the specified pre-command is not executable