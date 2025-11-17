# Execute php on phpfpm container
php +ARGS:
    @docker compose exec -u $(id -u) app php {{ARGS}}

# Execute composer on phpfpm container
composer +ARGS:
    @docker compose exec -u $(id -u) app composer {{ARGS}}

# Shortcut for console on DEV environment
console +ARGS:
    just php bin/console {{ARGS}}

install:
    @docker compose pull
    @docker compose build
    @docker compose up -d
    just composer install
    just console doctrine:migrations:migrate --no-interaction

static:
    just php ./vendor/bin/phpstan analyse ./src -c phpstan.neon
    just php ./vendor/bin/php-cs-fixer fix
    just security

lint:
	just console lint:twig templates
	just console lint:yaml translations config

security:
    just composer audit
    just console importmap:audit

db-reset:
    just php bin/console doc:fix:load -n

# Install asset
require-asset +ARGS:
    just console importmap:require {{ARGS}}
