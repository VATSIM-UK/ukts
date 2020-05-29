# VATSIM UK Training System (UKTS)
[![Commitizen friendly](https://img.shields.io/badge/commitizen-friendly-brightgreen.svg)](http://commitizen.github.io/cz-cli/)

## Git Workflow
As the banner above indicates, this repo is commitizen friendly. To activate this you can run `git commit` or `git cz`.
This allows us to generate an automatic changelog and bring greater clarity to the intentions of a commit.

## Front End Development
To install the VATSIM UK UI kit [consilio](https://github.com/VATSIM-UK/consilio), you need to ensure you are authenticated against the package registry here on GitHub.

To do this, you don't need any additional permissions, but you do need a personal access token to authenticate against
the VATSIM UK npm/yarn [registry](https://github.com/orgs/VATSIM-UK/packages).

1. Generate Token from GitHub -> Settings -> Developer Settings -> Personal Access Tokens. Scope to `repo.*` & `read:packages` permissions.
2. Run `yarn login --registry=https://npm.pkg.github.com`. When prompted, insert your GitHub username and the token you 
have just generated.
3. Run `yarn` to install all packages.

For more information, visit 
[this](https://help.github.com/en/packages/using-github-packages-with-your-projects-ecosystem/configuring-npm-for-use-with-github-packages#authenticating-to-github-packages) guide
for more information.

## Docker Development Environment
A [docker-compose](https://docs.docker.com/compose/) file is provided to facilitate the use of a docker container for an integrated development environment.
This includes an nginx container, php environment and MariaDB database bundled in a docker-compose container.

### Usage
Any 19.* version of Docker should be able to run the container. 

To build the container, run the following command: 

```docker-compose up```

To execute commands, fun the follow command

```docker-compose exec app <YOUR COMMAND HERE>```

E.g. 
```docker-compose exec app vendor/bin/phpunit```

After the container has built, composer will install dependencies and run the database
migrations.
