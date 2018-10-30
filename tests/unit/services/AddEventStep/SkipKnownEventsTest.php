<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Event;
use EventChain;
use EventFactory;
use AnchorClient;
use Jasny\DB\EntitySet;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use LTO\Account;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \AddEventStep\SkipKnownEvents
 */
class SkipKnownEventsTest extends \Codeception\Test\Unit
{
    /**
     * @var SkipKnownEvents
     */
    protected $step;

    public function setUp()
    {
        $this->step = new SkipKnownEvents();
    }

    public function test()
    {
        $keys = [
            $this->createMock(Event::class),
            null,
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $values = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $keys[0]->hash = '12345';
        $keys[2]->hash = 'abcde';
        $keys[3]->hash = 'foo';
        $keys[4]->hash = 'baz';

        $values[0]->hash = '12345';
        $values[1]->hash = 'bar';
        $values[2]->hash = 'abcde';
        $values[3]->hash = 'zoo';
        $values[4]->hash = 'baz';

        $events = $this->getEventsGenerator($keys, $values);
        $validation = $this->createMock(ValidationResult::class);

        $validation->expects($this->once())->method('addError')->withConsecutive(
            ["fork detected; conflict on '%s' and '%s'", 'zoo', 'foo']
        );

        $expected = [$values[1], $values[3], $values[4]];
       
        $pipeline = Pipeline::with($events);
        $ret = i\function_call($this->step, $pipeline, $validation);
        $this->assertSame($ret, $pipeline);

        $result = $pipeline->values()->toArray();
        $this->assertEquals($expected, $result);
    }

    /**
     * Create test generator of events
     *
     * @param  array $keys 
     * @param  array $values 
     * @return Generator
     */
    protected function getEventsGenerator(array $keys, array $values): \Generator
    {
        for ($i=0; $i < count($keys); $i++) { 
            yield $keys[$i] => $values[$i];
        }
    }
}
