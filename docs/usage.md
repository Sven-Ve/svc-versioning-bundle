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
```

## Call

```
bin/console app:versioning
```

## Parameter

```
bin/console app:versioning --help
Description:
  Versioning application, prepare releasing to prod

Usage:
  app:versioning [options] [--] [<commitMessage>]

Arguments:
  commitMessage         Commit message

Options:
      --major           Add major version
      --minor           Add minor version
      --patch           Add patch version
      --init            Init versioning (set to 0.0.1)
...
```

## Twig template

You can include the generate twig template in your templates to display the version number.

Example:

```twig
{# templates/_version.html.twig #}
<span title='Release 29.06.2021 19:55:36 UTC'>Version: 0.2.10</span>
```