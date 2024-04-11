# Execute php on phpfpm container
php +ARGS:
    @docker compose exec -u $(id -u) phpfpm php {{ARGS}}

# Execute composer on phpfpm container
composer +ARGS:
    @docker compose exec -u $(id -u) phpfpm composer {{ARGS}}

# Shortcut for console on DEV environment
console +ARGS:
    just php bin/console {{ARGS}}

migration:
    just console doctrine:migrations:migrate

install:
    @docker compose pull
    @docker compose build
    @docker compose up -d
    just composer install
    just console doctrine:migrations:migrate --no-interaction

static:
    @docker compose exec phpfpm ./vendor/bin/phpstan analyse ./src -c phpstan.neon
    @docker compose exec phpfpm ./vendor/bin/php-cs-fixer fix
    just console lint:twig templates

# Install asset
require-asset +ARGS:
    just console importmap:require {{ARGS}}

# Shortcut for importmap:require
npm install +ARGS:
    just require-asset {{ARGS}}

