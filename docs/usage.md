# Usage

## Execution Flow

When you run `bin/console svc:versioning:new`, the following steps are executed in order:

1. **Pre-command** (if configured) - Runs custom validation commands (tests, linting, etc.)
2. **Composer Audit** (enabled by default) - Checks for security vulnerabilities in dependencies
3. **Cache Clear Check** (if enabled) - Validates production cache clearing
4. **Version Increment** - Calculates and updates the new version number
5. **File Updates** - Writes `.version`, `CHANGELOG.md`, and Twig template
6. **Git Operations** (if enabled) - Commits, tags, and pushes changes
7. **Deployment** (if enabled) - Triggers deployment process

> **Important:** If any step fails, the entire process is aborted to prevent incomplete releases.

## Customization

Adapt your settings in the configuration file:

```yaml
#config/packages/svc_versioning.yaml
svc_versioning:
    # should git checkin and push runs? Have to be configured first.
    run_git: true

    # run this command before start versioning, stop on error (e.q. phpstan, tests, ...)'
    # e.q. pre_command: composer run-script phpstan
    pre_command: ~

    # check for security vulnerabilities in dependencies (default: true)
    run_composer_audit: true

    # should easycorp/easy-deploy-bundle runs? Have to be installed and configured first.
    run_deploy: true

    # run this command for deployment, disable default deployment with easycorp/easy-deploy-bundle
    deploy_command: ~

    # Deploy via Ansible
    ansible_deploy:       false

    # if ansible_deploy==true the name of the inventory file (default="inventory.yaml")
    ansible_inventory:    inventory.yaml

    # if ansible_deploy==true the name of the ansible playbook
    ansible_playbook:     ~
```

## Call

```console
$ bin/console svc:versioning:new
```

## Parameter

```console
$ bin/console svc:versioning:new --help
Description:
  Create a new application version, prepare and release it to prod.

Usage:
  svc_versioning:new [options] [--] [<commitMessage>]

Arguments:
  commitMessage         Commit message

Options:
      --major           Add major version
  -m, --minor           Add minor version
  -p, --patch           Add patch version
  -i, --init            Init versioning (set to 0.0.1)
      --ignore-audit    Ignore composer audit vulnerabilities and continue with release
...
```

### Emergency Override

The `--ignore-audit` option allows you to bypass security vulnerability checks in emergency situations:

```bash
# Use only when absolutely necessary (e.g., critical hotfix)
bin/console svc:versioning:new "Emergency fix" --patch --ignore-audit
```

**Warning:** This option:
- Should only be used in exceptional circumstances
- Still runs composer audit and displays vulnerabilities
- Shows warnings but continues with the release
- Requires you to address vulnerabilities as soon as possible

## Twig template

You can include the generate twig template in your templates to display the version number.

Example:

```twig
{# templates/_version.html.twig #}
<span title='Release 29.06.2021 19:55:36 UTC'>Version: 0.2.10</span>
```
