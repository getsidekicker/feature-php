# feature-php

## Usages in projects

### Block execution

```
feature_eval('sk-1234-temp-flag',
    on: fn(?array $attachment) => // do stuff when feature is on,
    _: fn(?array $attachment) => // do feature when feature is off
)
```

### Conditional

```
if (feature_match('sk-1234-temp-flag')) {
    // do feature when feature is off
} else  {
    // do feature when feature is off
}
```

## Creating new feature flag

```
# php artisan feature:create-flag {--name} {--description} [{--tags=*}]
php artisan feature:create-flag --name="sk-1234-temp-flag" --description="Create temp flag for feature"
```
