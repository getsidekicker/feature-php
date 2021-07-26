#!/bin/bash
set -e

if [[ "${DB_HOST-false}" != "false" ]]; then
    await "tcp4://${DB_HOST}:3306"
fi

exec "$@"
