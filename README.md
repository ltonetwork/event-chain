LegalThings Event Chain Service (POC)
---

See https://legalthings.github.io/livecontracts-specs/01-event-chain/

This is a Proof of concept written in PHP. The final version will be in Java.

## Installation

```
git clone git@github.com:legalthings/legalevents-poc.git
cd legalevents-poc
composer install
bin/codecept build
```

_Due to a dependency on the MongoDB extension, this service currently requires PHP 5.6._

## Tests

Test use the [Codeception test framework](https://codeception.com/). The project contains unit and api tests. Code in the
controllers is only covered by the api tests.

### Run tests

    bin/codecept run

To run only a single test use

    bin/codecept run api Default/100-InfoCept

For more options see https://codeception.com/docs/reference/Commands#run

### HTTP Mock

External services MUST be mocked. For api tests use `$I->expectHttpRequest()` to mock and assert external http calls done by Guzzle.

```php
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://example.com', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));

    $I->assertJsonStringEqualsJsonString('{"foo": "bar"}', (string)$request->getBody());
    
    return new Response(200);
});
```

## Serve

To serve the project on localhost run

```
php -S localhost:4000 -t www
```

_Note, it's preferable to work TDD and use tests when developing. This means you would hardly ever need to run this service
localy.
