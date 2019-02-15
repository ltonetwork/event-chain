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
tests.

Do a full analysis and run tests with

    composer test

## Serve

To serve the project on localhost run

```
php -S localhost:4000 -t www
```

_Note, it's preferable to work TDD and use tests when developing. This means you would hardly ever need to run this
service localy._

