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
    
    # Deployment Options
    run_deploy: true                 # Enable deployment after versioning
    deploy_command: ~                # Custom deployment command (overrides EasyDeploy)
    
    # Ansible Deployment
    ansible_deploy: false            # Use Ansible for deployment
    ansible_inventory: inventory.yaml # Ansible inventory file name
    ansible_playbook: ~              # Ansible playbook to execute
    
    # Sentry Integration
    create_sentry_release: false     # Create Sentry release
    sentry_app_name: ~               # Sentry application name
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

### Sentry Integration

#### Create Sentry Release (`create_sentry_release`)

**Default:** `false`

Automatically create a new release in Sentry when versioning.

```yaml
svc_versioning:
    create_sentry_release: true
    sentry_app_name: "my-application"
```

**Requirements:**
- Sentry must be configured in your application
- Sentry configuration file must exist at `config/packages/sentry.yaml`

## Configuration Examples

### Development Environment
```yaml
# config/packages/dev/svc_versioning.yaml
svc_versioning:
    run_git: false
    run_deploy: false
    pre_command: "vendor/bin/phpunit --testsuite=unit"
```

### Production Environment
```yaml
# config/packages/prod/svc_versioning.yaml
svc_versioning:
    run_git: true
    run_deploy: true
    pre_command: "composer run-script phpstan && vendor/bin/phpunit"
    create_sentry_release: true
    sentry_app_name: "my-app"
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
- **Missing Sentry config**: If `create_sentry_release: true` but Sentry is not configured