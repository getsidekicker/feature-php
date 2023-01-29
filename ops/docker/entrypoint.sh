#!/bin/bash
set -e

composer install
composer dump-autoload

if [[ "${FEATURE_FLAGR_URL-false}" != "false" ]]; then
  await "${FEATURE_FLAGR_URL}/api/v1/health"
fi

exec "$@"
