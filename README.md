LTO Network -  Event Chain Service
---

See https://legalthings.github.io/livecontracts-specs/01-event-chain/

## Requirements

- [PHP](http://www.php.net) >= 7.2.0
- [MongoDB](http://www.mongodb.org/) >= 3.2
- [Git](http://git-scm.com)

_Required PHP extensions are marked by composer_

## Installation

The LTO full node contains the workflow engine. See how to [setup a node](https://github.com/legalthings/lto).

Alternatively; clone from GitHub for development

```
git clone git@github.com:legalthings/event-chain.git
cd event-chain
composer install
bin/codecept build
```

## Tests

The code is analysed using [PHPStan](https://phpstan) (static code analyses) and
[PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) (coding style).

Before running the test for the first time run `bin/codecept build`.

Testing is done using the [Codeception test framework](https://codeception.com/). The project contains unit and api
tests, plus tests for integration with workflow engine.

Do a full analysis and run tests with

    composer test

### Workflow Integration tests

These tests go through all the working process from event-chain service to workflow-engine service, exluding anchoring and queueing. For them to work, servers should be run for both services. It can be done either manually, or by specifying commands in config file. It's preferable to use `config/settings.local.yml` for this, as commands will probably differ on different machines.

Servers should use `tests.workflow` environment. Example of commands:

```
workflow_integration:
  commands:
    - 'APPLICATION_ENV=tests.workflow php -S localhost:4000 -t www'
    - 'APPLICATION_ENV=tests.workflow php -S localhost:4002 -t www'
    - 'APPLICATION_ENV=tests.workflow php -S localhost:4001 -t ../workflow-engine.localhost/www'  
  wait: 5
```

Note, that if you use php built-in server for this purpose, two processes should be run for event-chain service, as in given example. This is due to the fact, that php server can run only one process per time. So second process is needed to respond to back queries from workflow-engine.

`wait` is an optional time needed for servers to initialize.

If servers are launched manually, config should look like:

```
workflow_integration:
  commands: manual
```

If `commands` config is not set, test suite is skipped.

## Serve

To serve the project on localhost run

```
php -S localhost:4000 -t www
```

_Note, it's preferable to work TDD and use tests when developing. This means you would hardly ever need to run this
service localy._

