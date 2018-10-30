<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Event;
use EventChain;
use EventFactory;
use AnchorClient;
use ResourceStorage;
use Jasny\DB\EntitySet;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use LTO\Account;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \AddEventStep\ValidateInput
 */
class ValidateInputTest extends \Codeception\Test\Unit
{
    /**
     * @var SyncChains
     */
    protected $step;

    /**
     * @var EventChain
     */
    protected $chain;

    public function setUp()
    {
        $this->chain = $this->createMock(EventChain::class);
        $this->step = new ValidateInput($this->chain);
    }

    public function test()
    {
        $validationResult = $this->createMock(ValidationResult::class);

        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->once())->method('add')->with($validationResult);

        $newEvents = $this->createMock(EventChain::class);
        $newEvents->id = 'foo';
        $this->chain->id = 'foo';

        $newEvents->expects($this->once())->method('validate')->willReturn($validationResult);
               
        $result = i\function_call($this->step, $newEvents, $validation);
        $this->assertSame($newEvents, $result);
    }

    public function exceptionProvider()
    {
        return [
            [12, '12'],
            ['foo', 'bar']
        ];
    }

    /**
     * @dataProvider exceptionProvider
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Can't add events of a different chain
     */
    public function testException($id, $newId)
    {
        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->never())->method('add');

        $newEvents = $this->createMock(EventChain::class);
        $newEvents->id = $newId;
        $this->chain->id = $id;
               
        $result = i\function_call($this->step, $newEvents, $validation);        
    }
}
