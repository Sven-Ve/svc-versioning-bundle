# Usage

## Customization

adapt your settings in the configuration file

```yaml
#config/packages/svc_versioning.yaml
svc_versioning:
    # should git checkin and push runs? Have to be configured first.
    run_git: true

    # should easycorp/easy-deploy-bundle runs? Have to be installed and configured first.
    run_deploy: true

    # run this command before start versioning, stop on error (e.q. phpstan, tests, ...)'
    # e.q. pre_command: composer run-script phpstan
    pre_command: ~

    # run this command for deployment, disable default deployment with easycorp/easy-deploy-bundle
    deploy_command: ~

    # Create a new release in config/packages/sentry.yaml (if you use sentry)
    create_sentry_release: false

    # Sentry application name (included in release)
    sentry_app_name:      ~
```

## Call

```console
$ bin/console svc_versioning:new
```

## Parameter

```console
$ bin/console svc_versioning:new --help
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
...
```

## Twig template

You can include the generate twig template in your templates to display the version number.

Example:

```twig
{# templates/_version.html.twig #}
<span title='Release 29.06.2021 19:55:36 UTC'>Version: 0.2.10</span>
```
