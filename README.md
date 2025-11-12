# PoolPHP Guardian
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


## Git best practices
1. Respect [conventional commits](https://www.conventionalcommits.org/fr/v1.0.0/) for the commit messages.
2. Work on a branch named with a prefix (like your commit messages) : `docs`, `feat`, `fix`, `test`, `chore`, etc.
3. Make [atomic commits](https://www.codeheroes.fr/2021/10/25/git-pourquoi-ecrire-des-commits-atomiques/).
