# Examples and Workflows

This document provides practical examples of how to use the SvcVersioningBundle in different scenarios.

## Basic Usage Examples

### Simple Version Increment

```bash
# Increment patch version (1.2.3 → 1.2.4)
bin/console svc:versioning:new

# Increment minor version (1.2.3 → 1.3.0)
bin/console svc:versioning:new --minor

# Increment major version (1.2.3 → 2.0.0)
bin/console svc:versioning:new --major

# Initialize versioning for new project
bin/console svc:versioning:new --init
```

### Custom Commit Messages

```bash
# Use custom commit message
bin/console svc:versioning:new "Add user authentication feature"

# Custom message with version type
bin/console svc:versioning:new --minor "Implement new API endpoints"
```

## Workflow Examples

### 1. Development Workflow

**Scenario:** Small team, feature branch workflow

**Configuration:**
```yaml
# config/packages/dev/svc_versioning.yaml
svc_versioning:
    run_git: false
    run_deploy: false
    pre_command: "vendor/bin/phpunit --testsuite=unit"
```

**Workflow:**
```bash
# 1. Develop feature on branch
git checkout -b feature/user-auth

# 2. Test locally during development
bin/console svc:versioning:new --patch "Test version"

# 3. Merge to main and create release
git checkout main
git merge feature/user-auth
bin/console svc:versioning:new --minor "Add user authentication"
```

### 2. Production Release Workflow

**Scenario:** Automated deployment to production

**Configuration:**
```yaml
# config/packages/prod/svc_versioning.yaml
svc_versioning:
    run_git: true
    run_deploy: true
    pre_command: "composer run-script phpstan && vendor/bin/phpunit"
```

**Workflow:**
```bash
# 1. Run full release process
bin/console svc:versioning:new --minor "Release new features"

# This will:
# - Run PHPStan and tests
# - Increment version
# - Update files (.version, CHANGELOG.md, _version.html.twig)
# - Commit and push changes
# - Create and push git tag
# - Deploy via EasyDeploy
```

### 3. Multi-Environment Deployment

**Scenario:** Deploy to staging first, then production

**Staging Configuration:**
```yaml
# config/packages/staging/svc_versioning.yaml
svc_versioning:
    run_git: true
    run_deploy: true
    deploy_command: "./deploy.sh staging"
    pre_command: "vendor/bin/phpunit"
```

**Production Configuration:**
```yaml
# config/packages/prod/svc_versioning.yaml
svc_versioning:
    run_git: false  # Git operations already done in staging
    run_deploy: true
    deploy_command: "./deploy.sh production"
```

**Workflow:**
```bash
# 1. Deploy to staging
APP_ENV=staging bin/console svc:versioning:new --minor "New feature release"

# 2. Test on staging...

# 3. Deploy same version to production
APP_ENV=prod bin/console svc:versioning:new --patch "Production deployment"
```

## Integration Examples

### 4. CI/CD Pipeline Integration

**GitHub Actions Example:**
```yaml
# .github/workflows/release.yml
name: Release
on:
  push:
    branches: [main]

jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
      
      - name: Create release
        run: bin/console svc:versioning:new --patch "Automated release"
        env:
          APP_ENV: prod
```

### 5. Docker Deployment Workflow

**Configuration:**
```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    run_git: true
    run_deploy: true
    deploy_command: "./docker-deploy.sh"
    pre_command: "docker-compose run --rm app vendor/bin/phpunit"
```

**Custom Deployment Script:**
```bash
#!/bin/bash
# docker-deploy.sh

# Read current version
VERSION=$(cat .version)

# Build Docker image with version tag
docker build -t my-app:$VERSION .
docker tag my-app:$VERSION my-app:latest

# Push to registry
docker push my-app:$VERSION
docker push my-app:latest

# Deploy to production
kubectl set image deployment/my-app app=my-app:$VERSION
```

### 6. Ansible Deployment Example

**Configuration:**
```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    run_git: true
    ansible_deploy: true
    ansible_inventory: "production.yml"
    ansible_playbook: "deploy.yml"
    pre_command: "vendor/bin/phpunit && composer run-script phpstan"
```

**Ansible Playbook:**
```yaml
# deploy.yml
---
- hosts: webservers
  vars:
    app_version: "{{ lookup('file', '.version') }}"
  
  tasks:
    - name: Deploy application version {{ app_version }}
      git:
        repo: https://github.com/user/my-app.git
        dest: /var/www/my-app
        version: "v{{ app_version }}"
      
    - name: Install dependencies
      composer:
        command: install
        working_dir: /var/www/my-app
        no_dev: true
      
    - name: Restart services
      systemd:
        name: php-fpm
        state: restarted
```

## Advanced Examples

### 7. Hotfix Workflow

**Scenario:** Critical bug fix needs immediate deployment

```bash
# 1. Create hotfix branch from main
git checkout -b hotfix/critical-bug main

# 2. Fix the bug and test
# ... make changes ...

# 3. Create patch version
bin/console svc:versioning:new --patch "Fix critical security vulnerability"

# 4. Merge back to main
git checkout main
git merge hotfix/critical-bug
git push origin main

# 5. Deploy immediately
APP_ENV=prod bin/console svc:versioning:new --patch "Hotfix deployment"
```

### 8. Feature Flag Release

**Configuration with feature flag checks:**
```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    pre_command: "./check-feature-flags.sh && vendor/bin/phpunit"
    run_git: true
    run_deploy: true
```

**Feature Flag Check Script:**
```bash
#!/bin/bash
# check-feature-flags.sh

# Ensure no experimental features are enabled in production
if grep -r "FEATURE_EXPERIMENTAL.*true" config/; then
    echo "Error: Experimental features detected in config"
    exit 1
fi

echo "Feature flag check passed"
```

### 9. Multi-Service Versioning

**Scenario:** Microservices that need coordinated versioning

**Service A Configuration:**
```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    run_git: true
    run_deploy: true
    deploy_command: "./deploy-service-a.sh"
    pre_command: "vendor/bin/phpunit && ./notify-services.sh"
```

**Notification Script:**
```bash
#!/bin/bash
# notify-services.sh

VERSION=$(cat .version)

# Notify other services of new version
curl -X POST http://service-b/api/version-update \
     -H "Content-Type: application/json" \
     -d "{\"service\": \"service-a\", \"version\": \"$VERSION\"}"
```

## Best Practices

### Version Strategy
- **Patch**: Bug fixes, security updates, minor improvements
- **Minor**: New features, API additions (backward compatible)  
- **Major**: Breaking changes, API removals

### Git Workflow
- Always test before versioning
- Use meaningful commit messages
- Tag releases for easy rollback
- Keep main branch stable

### Deployment Safety
- Always run tests in pre_command
- Use staging environment for testing
- Monitor deployments
- Have rollback procedures ready

### Team Coordination
- Document version increment decisions
- Use pull request reviews for releases
- Communicate breaking changes early
- Maintain detailed CHANGELOG.md