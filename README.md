LTO Network -  Event Chain Service
---

See https://legalthings.github.io/livecontracts-specs/01-event-chain/

## Installation

```
git clone git@github.com:legalthings/legalevents.git
cd legalevents
composer install
```

## Tests

The code is analysed using [PHPStan](https://phpstan) (static code analyses) and
[PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) (coding statyle).

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
