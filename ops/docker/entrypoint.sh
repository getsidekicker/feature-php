#!/bin/bash
set -e

composer install
composer dump-autoload

if [[ "${DB_HOST-false}" != "false" ]]; then
    await "tcp4://${DB_HOST}:3306"
fi

if [[ "${FEATURE_FLAGR_URL-false}" != "false" ]]; then
    await "${FEATURE_FLAGR_URL}"
fi

exec "$@"
