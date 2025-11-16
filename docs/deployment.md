# Deployment Guide

The SvcVersioningBundle supports various deployment strategies. This guide covers all available options and how to configure them.

## Deployment Options Overview

The bundle supports four deployment strategies:

1. **EasyDeploy Bundle** (default) - Symfony-based deployment
2. **Custom Commands** - Execute custom deployment scripts
3. **Ansible Deployment** - Infrastructure automation
4. **No Deployment** - Version only, deploy manually

## 1. EasyDeploy Bundle Integration

The default deployment method uses the [EasyCorp EasyDeploy Bundle](https://github.com/EasyCorp/easy-deploy-bundle).

### Installation

```bash
composer require easycorp/easy-deploy-bundle --dev
```

### Configuration

Create EasyDeploy configuration:

```php
// config/packages/dev/easy_deploy.php
<?php

use EasyCorp\Bundle\EasyDeployBundle\Deployer\DefaultDeployer;

return static function (DefaultDeployer $deployer) {
    $deployer
        ->server('production')
            ->host('your-server.com')
            ->user('deploy')
            ->identityFile('~/.ssh/id_rsa')
            ->repositoryUrl('git@github.com:user/repo.git')
            ->repositoryBranch('main')
            ->deployDir('/var/www/html')
        ;
        
    $deployer
        ->usePHP('8.2')
        ->runTests(false)  // Tests already run by versioning bundle
        ->runComposer(true)
        ->runWebpackEncore(false)  // Configure as needed
    ;
};
```

### Bundle Configuration

```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    run_deploy: true  # Enable EasyDeploy (default)
```

### Usage

```bash
# This will automatically deploy via EasyDeploy after versioning
bin/console svc:versioning:new --minor "New feature release"
```

## 2. Custom Deployment Commands

Override the default EasyDeploy behavior with custom deployment scripts.

### Configuration

```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    run_deploy: true
    deploy_command: "./deploy.sh production"
```

### Example Deployment Scripts

#### Basic Shell Script
```bash
#!/bin/bash
# deploy.sh

ENVIRONMENT=${1:-staging}
VERSION=$(cat .version)

echo "Deploying version $VERSION to $ENVIRONMENT..."

# Copy files to server
rsync -avz --exclude='.git' \
      --exclude='var/cache' \
      --exclude='var/log' \
      ./ user@server:/var/www/app/

# Run deployment commands on server
ssh user@server "cd /var/www/app && \
    composer install --no-dev --optimize-autoloader && \
    bin/console cache:clear --env=prod && \
    bin/console doctrine:migrations:migrate --no-interaction"

echo "Deployment completed successfully!"
```

#### Docker Deployment
```bash
#!/bin/bash
# docker-deploy.sh

VERSION=$(cat .version)
IMAGE_NAME="myapp:$VERSION"

echo "Building Docker image $IMAGE_NAME..."

# Build and tag image
docker build -t $IMAGE_NAME .
docker tag $IMAGE_NAME myapp:latest

# Push to registry
docker push $IMAGE_NAME
docker push myapp:latest

# Deploy to production
kubectl set image deployment/myapp app=$IMAGE_NAME
kubectl rollout status deployment/myapp

echo "Docker deployment completed!"
```

#### Multi-Server Deployment
```bash
#!/bin/bash
# multi-server-deploy.sh

SERVERS=("web1.example.com" "web2.example.com" "web3.example.com")
VERSION=$(cat .version)

for server in "${SERVERS[@]}"; do
    echo "Deploying to $server..."
    
    # Copy files
    rsync -avz ./ user@$server:/var/www/app/
    
    # Update on server
    ssh user@$server "cd /var/www/app && \
        composer install --no-dev && \
        bin/console cache:clear --env=prod"
    
    echo "Deployment to $server completed"
done

# Update load balancer or perform health checks
./health-check.sh
```

### Environment-Specific Commands

```yaml
# config/packages/staging/svc_versioning.yaml
svc_versioning:
    deploy_command: "./deploy.sh staging"

# config/packages/prod/svc_versioning.yaml  
svc_versioning:
    deploy_command: "./deploy.sh production"
```

## 3. Ansible Deployment

Use Ansible for infrastructure automation and deployment.

### Prerequisites

Install Ansible:
```bash
# macOS
brew install ansible

# Ubuntu/Debian
apt install ansible

# pip
pip install ansible
```

### Configuration

```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    ansible_deploy: true
    ansible_inventory: "inventory.yml"
    ansible_playbook: "deploy.yml"
```

### Inventory File

```yaml
# inventory.yml
all:
  children:
    webservers:
      hosts:
        web1.example.com:
          ansible_user: deploy
        web2.example.com:
          ansible_user: deploy
    databases:
      hosts:
        db1.example.com:
          ansible_user: admin

  vars:
    ansible_ssh_private_key_file: ~/.ssh/deploy_key
    app_dir: /var/www/myapp
```

### Playbook Examples

#### Basic Deployment Playbook
```yaml
# deploy.yml
---
- name: Deploy Application
  hosts: webservers
  vars:
    app_version: "{{ lookup('file', '.version') }}"
    app_repo: "https://github.com/user/myapp.git"
  
  tasks:
    - name: Ensure app directory exists
      file:
        path: "{{ app_dir }}"
        state: directory
        owner: www-data
        group: www-data
    
    - name: Clone/update repository
      git:
        repo: "{{ app_repo }}"
        dest: "{{ app_dir }}"
        version: "v{{ app_version }}"
        force: yes
      notify: restart php-fpm
    
    - name: Install Composer dependencies
      composer:
        command: install
        working_dir: "{{ app_dir }}"
        no_dev: true
        optimize_autoloader: true
    
    - name: Clear Symfony cache
      command: bin/console cache:clear --env=prod
      args:
        chdir: "{{ app_dir }}"
    
    - name: Run database migrations
      command: bin/console doctrine:migrations:migrate --no-interaction
      args:
        chdir: "{{ app_dir }}"
  
  handlers:
    - name: restart php-fpm
      systemd:
        name: php8.2-fpm
        state: restarted
```

#### Advanced Deployment with Rolling Updates
```yaml
# rolling-deploy.yml
---
- name: Rolling Deployment
  hosts: webservers
  serial: 1  # Deploy one server at a time
  vars:
    app_version: "{{ lookup('file', '.version') }}"
  
  pre_tasks:
    - name: Remove server from load balancer
      uri:
        url: "http://loadbalancer/api/servers/{{ inventory_hostname }}/disable"
        method: POST
      delegate_to: localhost
    
    - name: Wait for connections to drain
      wait_for:
        timeout: 30
  
  tasks:
    - name: Deploy application
      include_tasks: deploy-tasks.yml
    
    - name: Run health check
      uri:
        url: "http://{{ inventory_hostname }}/health"
        method: GET
        status_code: 200
      retries: 5
      delay: 10
  
  post_tasks:
    - name: Add server back to load balancer
      uri:
        url: "http://loadbalancer/api/servers/{{ inventory_hostname }}/enable"
        method: POST
      delegate_to: localhost
```

### Environment-Specific Inventories

```yaml
# config/packages/staging/svc_versioning.yaml
svc_versioning:
    ansible_inventory: "inventories/staging.yml"
    ansible_playbook: "deploy-staging.yml"

# config/packages/prod/svc_versioning.yaml
svc_versioning:
    ansible_inventory: "inventories/production.yml"
    ansible_playbook: "deploy-production.yml"
```

## 4. Disable Deployment

For development or manual deployment scenarios:

```yaml
# config/packages/svc_versioning.yaml
svc_versioning:
    run_deploy: false
```

## Deployment Best Practices

### Security
- Use SSH keys instead of passwords
- Limit deployment user permissions
- Use secure file transfer (rsync over SSH, SFTP)
- Validate SSL certificates in production

### Reliability
- Always backup before deployment
- Use health checks to verify deployment
- Implement rollback procedures
- Test deployments in staging first

### Performance
- Use deployment strategies that minimize downtime
- Consider blue-green or rolling deployments
- Optimize asset compilation and caching
- Monitor application performance post-deployment

### Monitoring
- Log deployment activities
- Set up alerts for deployment failures
- Monitor application metrics after deployment

## Rollback Strategies

### Git-based Rollback
```bash
# Find previous version
git tag --sort=-version:refname | head -5

# Rollback to previous version
git checkout v1.2.3
bin/console svc:versioning:new --patch "Rollback to v1.2.3"
```

### Ansible Rollback Playbook
```yaml
# rollback.yml
---
- name: Rollback Application
  hosts: webservers
  vars:
    rollback_version: "{{ rollback_to | default('1.2.3') }}"
  
  tasks:
    - name: Checkout previous version
      git:
        repo: "{{ app_repo }}"
        dest: "{{ app_dir }}"
        version: "v{{ rollback_version }}"
        force: yes
```

```bash
# Execute rollback
ansible-playbook -i inventory.yml rollback.yml -e rollback_to=1.2.3
```

## CI/CD Integration

### GitHub Actions
```yaml
# .github/workflows/deploy.yml
name: Deploy
on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Install dependencies
        run: composer install --no-dev
      
      - name: Deploy
        run: bin/console svc:versioning:new --patch "Automated deployment"
        env:
          APP_ENV: prod
```

### GitLab CI
```yaml
# .gitlab-ci.yml
deploy:
  stage: deploy
  image: php:8.2
  script:
    - composer install --no-dev
    - bin/console svc:versioning:new --patch "GitLab deployment"
  only:
    - main
  environment:
    name: production
    url: https://myapp.com
```

## Troubleshooting Deployment Issues

See the [Troubleshooting Guide](troubleshooting.md#5-deployment-issues) for common deployment problems and solutions.