# Guardian
A [Symfony 7](https://symfony.com/) project running on [docker](https://www.docker.com/)
on a PHP 8.3 server.

Guardian is made to prevent running production application using outdated dependencies.
It will check the composer.lock file and the package.json file to see if there are outdated dependencies.
If there are, alerts will be sent.
It also checks CVE database to see if there are vulnerabilities in the dependencies.

## Starting

Before all install [just](https://github.com/casey/just)

### Requirments
- [Docker](https://www.docker.com/)

### Install
1. `just install` to install the project.
2. `docker compose up -d` then go on `http://guardian.localhost`, to see the index page.


### Git best practices
1. Respect [conventional commits](https://www.conventionalcommits.org/fr/v1.0.0/) for the commit messages.
2. Work on a branch named with a prefix (like your commit messages) : `docs`, `feat`, `fix`, `test`, `chore`, etc.
3. Make [atomic commits](https://www.codeheroes.fr/2021/10/25/git-pourquoi-ecrire-des-commits-atomiques/).

## 🚀 Deployment
1. Deploy the sources in your server (app folder).
2. Run `composer install --no-interaction --no-dev --no-progress --no-scripts` to install dependencies
3. Run `composer dump-autoload --no-dev --classmap-authoritative` to optimize autoloading.
4. Build the assets running the following commands:
   - `php bin/console importmap:install`
   - `php bin/console assets:install public`
   - `php bin/console asset-map:compile`
5. Run the database migration : `php bin/console doctrine:migrations:migrate --no-interaction`
6. Reset the messenger workers : `php bin/console messenger:stop-workers`
7. Set the crontab to enable tasks execution:
   ```bash
   * * * * * /usr/bin/php bin/console messenger:consume async --memory-limit=128M --time-limit=60 >> /var/log/guardian/tasks.log 2>&1
   ```
   
## 🛠️ Cron tasks

### Using Supervisor & Scheduler
You can rely on the Scheduler symfony component to run cron tasks.
To do that, you need to create a worker that will run the following command: 
`php bin/console messenger:consume scheduler_guardian`

See https://symfony.com/doc/current/messenger.html#messenger-supervisor for more information.

### Using system cron
You can also use system cron to run the tasks.
To do that, you need to add the following line to your crontab:
```bash
5 * * * * /usr/bin/php bin/console messenger:consume scheduler_guardian --time-limit=3600 >> /var/log/guardian/schedule.log 2>&1
```
