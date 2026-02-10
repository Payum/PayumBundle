# Upgrade to 3.0

## XML Configuration Removed

All XML configuration files have been removed in version 3.0. You must migrate to PHP/YAML formats.

### Service Configuration: XML → PHP

**Before (removed in 3.0):**
```yaml
imports:
    - { resource: '@PayumBundle/Resources/config/payum.xml' }
    - { resource: '@PayumBundle/Resources/config/storage/doctrine.orm.xml' }
```

**After (required in 3.0):**
```yaml
imports:
    - { resource: '@PayumBundle/Resources/config/payum.php' }
    - { resource: '@PayumBundle/Resources/config/storage/doctrine.orm.php' }
```

### Routing Configuration: XML → YAML

**Before (removed in 3.0):**
```yaml
_payum:
    resource: '@PayumBundle/Resources/config/routing/all.xml'
```

**After (required in 3.0):**
```yaml
_payum:
    resource: '@PayumBundle/Resources/config/routing/all.yaml'
```

## File Mappings

### Service Configuration Files
- `payum.xml` → `payum.php`
- `commands.xml` → `commands.php`
- `controller.xml` → `controller.php`
- `debug.xml` → `debug.php`
- `form.xml` → `form.php`
- `storage/doctrine.mongodb.xml` → `storage/doctrine.mongodb.php`
- `storage/doctrine.orm.xml` → `storage/doctrine.orm.php`
- `storage/filesystem.xml` → `storage/filesystem.php`
- `storage/propel1.xml` → `storage/propel1.php`
- `storage/propel2.xml` → `storage/propel2.php`

### Routing Files
- `routing/all.xml` → `routing/all.yaml`
- `routing/authorize.xml` → `routing/authorize.yaml`
- `routing/cancel.xml` → `routing/cancel.yaml`
- `routing/capture.xml` → `routing/capture.yaml`
- `routing/notify.xml` → `routing/notify.yaml`
- `routing/payout.xml` → `routing/payout.yaml`
- `routing/refund.xml` → `routing/refund.yaml`
- `routing/sync.xml` → `routing/sync.yaml`

## Quick Migration

1. Search for XML imports in your configuration:
```bash
grep -r "PayumBundle.*\.xml" config/
```

2. Replace `.xml` with `.php` for service configs
3. Replace `.xml` with `.yaml` for routing configs
4. Clear cache and test:
```bash
php bin/console cache:clear
php bin/console debug:container payum
php bin/console debug:router | grep payum
```
