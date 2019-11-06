# UK Training System Core API

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
