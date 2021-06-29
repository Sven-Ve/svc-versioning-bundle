# SvcVersioningBundle


This bundle supports versioning, (optional) git commit, pull and tagging and (optional) deployment.
Semantic versioning with major, minor and patch versions is used.

<br />

## Steps
  * Create a new version (using parameters on call)
  * Write the new version to the .version file
  * Write the new version to a Twig template (templates/_version.html.twig) (to display the version within the application)
  * Append the version and optional parameter to CHANGELOG.md file
  * Committing the changes
  * Push the changes
  * Create and push a tag with the new version number
  * (optional) Deploy the application (if [easycorp/easy-deploy-bundle](https://github.com/EasyCorp/easy-deploy-bundle) is installed and configured)


## Documentation

* [Installation](docs/installation.md)
* [Usage](docs/usage.md)