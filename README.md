# flagr-feature-laravel

## Prerequisites

To use this package, you will need to have [Flagr](https://checkr.github.io/flagr) installed and accessible

## Usage

### Block execution

```php
//function
feature_eval('flag',
    on: fn(object $attachment) => // do stuff when feature is on,
    otherwise: fn(object $attachment) => // do stuff when any other variant isn't matched
)

//alias
app('feature')->eval('flag',
    on: fn(object $attachment) => // do stuff when feature is on,
    otherwise: fn(object $attachment) => // do stuff when any other variant isn't matched
);
```

### Conditional

```php
//function
if (feature_match('flag')) {
    // do feature when feature variant is 'on'
} else  {
    // do otherwise
}

//alias
//function
if (app('feature')->match('flag')) {
    // do feature when feature variant is 'on'
} else  {
    // do otherwise
}
```

## Creating new feature flag

Flags can be created in the format `php artisan feature:create-flag {--name} {--description} [{--tags=*}]`. This will use the simple boolean flag type within Flagr

e.g.

```
php artisan feature:create-flag --name="temp-flag" --description="Create temp flag for feature"
```
