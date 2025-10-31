# Upgrade Guide: 5.x to 6.0

## Overview

Version 6.0 introduces significant improvements to code quality, type safety, and architecture. The most notable change is the introduction of an immutable `Version` value object to replace array-based version handling.

## Breaking Changes

### PHP Version Requirement

**Previous:** PHP 7.3+ or 8.x
**Now:** PHP 8.2+

**Action Required:** Ensure your project runs on PHP 8.2 or higher.

```bash
# Check your PHP version
php -v
```

### Internal Architecture Changes

The following internal changes were made. **If you only use the bundle's console commands**, no action is required.

#### Version Handling

**Previous (v5.x):** Versions were handled as associative arrays:
```php
$versionArray = [
    'major' => 1,
    'minor' => 2,
    'patch' => 3,
];
```

**Now (v6.0):** Versions use an immutable value object:
```php
use Svc\VersioningBundle\ValueObject\Version;

$version = new Version(1, 2, 3);
// or
$version = Version::fromString('1.2.3');
```

#### VersionString Service API

The `VersionString` service API has changed:

**Previous:**
```php
// Old methods (removed in v6.0)
$versionArray = $versionString->versionStringToVersionArray('1.2.3');
$string = $versionString->versionArrayToVersionString($versionArray);
$initial = $versionString->getInitial(); // returns string '0.0.1'
```

**Now:**
```php
// New methods (v6.0)
$version = $versionString->parse('1.2.3'); // returns Version object
$string = $versionString->toString($version); // or just $version->toString()
$initial = $versionString->getInitial(); // returns Version object
```

## Migration Steps

### For Bundle Users (Console Commands Only)

**No action required!** The command-line interface remains unchanged:

```bash
# All commands work exactly as before
bin/console svc:versioning:new
bin/console svc:versioning:new --major
bin/console svc:versioning:new --minor
bin/console svc:versioning:new --patch
```

### For Developers Extending the Bundle

If you have custom code that directly uses bundle services:

#### 1. Update PHP Version

Update your `composer.json`:
```json
{
    "require": {
        "php": "^8.2",
        "svc/versioning-bundle": "^6.0"
    }
}
```

Run:
```bash
composer update svc/versioning-bundle
```

#### 2. Update Custom Code Using VersionString

**Before:**
```php
use Svc\VersioningBundle\Service\VersionString;

$versionString = new VersionString();
$array = $versionString->versionStringToVersionArray('1.2.3');
$major = $array['major'];
```

**After:**
```php
use Svc\VersioningBundle\Service\VersionString;
use Svc\VersioningBundle\ValueObject\Version;

$versionString = new VersionString();
$version = $versionString->parse('1.2.3');
$major = $version->major; // Direct property access
```

#### 3. Version Manipulation

**Before:**
```php
$array = ['major' => 1, 'minor' => 2, 'patch' => 3];
$array['major']++;
$array['minor'] = 0;
$array['patch'] = 0;
```

**After:**
```php
$version = new Version(1, 2, 3);
$newVersion = $version->incrementMajor(); // Returns new Version(2, 0, 0)

// Original version is unchanged (immutable)
echo $version->toString();    // "1.2.3"
echo $newVersion->toString(); // "2.0.0"
```

#### 4. Version Comparison

New comparison methods are available:

```php
$v1 = new Version(1, 2, 3);
$v2 = new Version(1, 2, 4);

if ($v2->isGreaterThan($v1)) {
    echo "v2 is newer";
}

if ($v1->equals($v1)) {
    echo "Versions are identical";
}
```

## New Features in v6.0

### Immutable Value Objects

All version operations return new instances:

```php
$version = Version::fromString('1.0.0');
$next = $version->incrementPatch();

// $version remains unchanged
assert($version->patch === 0);
assert($next->patch === 1);
```

### Self-Validating

Invalid versions cannot be created:

```php
try {
    $invalid = new Version(-1, 0, 0);
} catch (\InvalidArgumentException $e) {
    // "Version numbers must be non-negative"
}
```

### Factory Methods

```php
// From string
$version = Version::fromString('2.5.10');

// Initial version (0.0.1)
$initial = Version::initial();

// Direct construction
$version = new Version(1, 2, 3);
```

### Convenient String Conversion

```php
$version = new Version(1, 2, 3);

// Explicit
echo $version->toString(); // "1.2.3"

// Magic method
echo $version;             // "1.2.3"
```

## Benefits of Version 6.0

### Type Safety
- No more array typos (`$version['maojr']` vs `$version->major`)
- IDE autocomplete for all properties and methods
- PHPStan level 6 compliance

### Immutability
- Versions cannot be accidentally modified
- Thread-safe operations
- No hidden side effects

### Modern PHP
- Readonly properties (PHP 8.2)
- Match expressions (PHP 8.0)
- Constructor property promotion (PHP 8.0)

### Better Testing
- Value objects are easier to test
- Clear, explicit assertions
- No array structure dependencies

## Troubleshooting

### "Class Version not found"

**Solution:** Clear your cache and ensure autoloading is updated:
```bash
composer dump-autoload
bin/console cache:clear
```

### "Call to undefined method versionStringToVersionArray()"

**Solution:** Update your code to use the new `parse()` method (see migration steps above).

### PHP Version Errors

**Error:** `"syntax error, unexpected 'readonly' (T_READONLY)"`

**Solution:** Upgrade to PHP 8.2+:
```bash
# Check current version
php -v

# Update PHP (example for Homebrew on macOS)
brew upgrade php
```

## Need Help?

- **Documentation:** [CLAUDE.md](CLAUDE.md)
- **Issues:** [GitHub Issues](https://github.com/Sven-Ve/svc-versioning-bundle/issues)
- **Examples:** See [tests/ValueObject/VersionTest.php](tests/ValueObject/VersionTest.php) for usage examples

## Summary

✅ **Bundle users:** No changes required
⚠️ **Custom integrations:** Update to use `Version` value object
📦 **PHP requirement:** 8.2+ (was 7.3+)
🎯 **Benefits:** Better type safety, immutability, modern PHP features
