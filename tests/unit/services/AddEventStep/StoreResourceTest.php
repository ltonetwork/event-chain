<?php declare(strict_types=1);

namespace AddEventStep;

use ArrayObject;
use Improved as i;
use Event;
use Identity;
use Privilege;
use EventChain;
use IdentitySet;
use EventFactory;
use AnchorClient;
use ResourceBase;
use ResourceStorage;
use ResourceFactory;
use ResourceInterface;
use Jasny\DB\EntitySet;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use LTO\Account;
use PHPUnit\Framework\MockObject\MockObject;
use Jasny\DB\Entity\Identifiable;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

/**
 * @covers \AddEventStep\StoreResource
 */
class StoreResourceTest extends \Codeception\Test\Unit
{
    use \Jasny\TestHelper;

    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var ResourceStorage
     */
    protected $resourceStorage;

    public function setUp()
    {
        $this->chain = $this->createMock(EventChain::class);
        $this->resourceFactory = $this->createMock(ResourceFactory::class);
        $this->resourceStorage = $this->createMock(ResourceStorage::class);
    }

    /**
     * Test '__construct' method
     */
    public function testConstruct()
    {
        $step = new StoreResource($this->chain, $this->resourceFactory, $this->resourceStorage);

        $this->assertAttributeSame($this->chain, 'chain', $step);
        $this->assertAttributeSame($this->resourceFactory, 'resourceFactory', $step);
        $this->assertAttributeSame($this->resourceStorage, 'resourceStorage', $step);
    }

    /**
     * Test '__invoke' method, if there're no validation errors
     */
    public function testInvokeNoFails()
    {
        $step = $this->getStep(['applyPrivilegeToResource', 'storeResource']);

        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events[0]->hash = 'a';
        $events[1]->hash = 'b';
        $newEvents = new ArrayObject($events);       

        $resources = [
            $this->createMock(ResourceInterface::class),
            $this->createMock(ResourceInterface::class)
        ];

        $privilegeValidations = [
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class)
        ];

        $storedValidations = [
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class)
        ];

        $resourceValidations = [
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class)
        ];

        $validation = $this->createMock(ValidationResult::class);

        $validation->expects($this->exactly(4))->method('failed')->willReturn(false);
        $this->resourceFactory->expects($this->exactly(2))->method('extractFrom')->will($this->returnValueMap([
            [$events[0], $resources[0]],
            [$events[1], $resources[1]]
        ]));

        $step->expects($this->exactly(2))->method('applyPrivilegeToResource')->will($this->returnValueMap([
            [$resources[0], $events[0], $privilegeValidations[0]],
            [$resources[1], $events[1], $privilegeValidations[1]]
        ]));

        $step->expects($this->exactly(2))->method('storeResource')->withConsecutive(
            [$resources[0], $newEvents], [$resources[1], $newEvents]
        )->willReturnOnConsecutiveCalls($storedValidations[0], $storedValidations[1]);

        $resources[0]->expects($this->once())->method('validate')->willReturn($resourceValidations[0]);
        $resources[1]->expects($this->once())->method('validate')->willReturn($resourceValidations[1]);

        $validation->expects($this->exactly(6))->method('add')->withConsecutive(
            [$privilegeValidations[0], "event 'a': "], [$resourceValidations[0], "event 'a': "], [$storedValidations[0], "event 'a': "], 
            [$privilegeValidations[1], "event 'b': "], [$resourceValidations[1], "event 'b': "], [$storedValidations[1], "event 'b': "]
        );
        
        $pipeline = Pipeline::with($events);        
        $ret = i\function_call($step, $pipeline, $validation, $newEvents);

        $this->assertSame($pipeline, $ret);

        $result = $pipeline->toArray();
        $this->assertEquals($events, $result);
    }    

    /**
     * Test '__invoke' method, if there're a validation error on saving resource
     */
    public function testInvokeStoreFails()
    {
        $step = $this->getStep(['applyPrivilegeToResource', 'storeResource']);
        list($events, $resources, $privilegeValidations, $storedValidations, $resourceValidations) = $this->getItems();

        $newEvents = new ArrayObject($events);       
        $validation = $this->createMock(ValidationResult::class);

        $validation->expects($this->exactly(6))->method('failed')->willReturnOnConsecutiveCalls(
            false, false, false, false, true, true
        );
        $this->resourceFactory->expects($this->exactly(2))->method('extractFrom')->will($this->returnValueMap([
            [$events[0], $resources[0]],
            [$events[1], $resources[1]]
        ]));

        $step->expects($this->exactly(2))->method('applyPrivilegeToResource')->will($this->returnValueMap([
            [$resources[0], $events[0], $privilegeValidations[0]],
            [$resources[1], $events[1], $privilegeValidations[1]]
        ]));

        $step->expects($this->exactly(2))->method('storeResource')->withConsecutive(
            [$resources[0], $newEvents], [$resources[1], $newEvents]
        )->willReturnOnConsecutiveCalls($storedValidations[0], $storedValidations[1]);

        $resources[0]->expects($this->once())->method('validate')->willReturn($resourceValidations[0]);
        $resources[1]->expects($this->once())->method('validate')->willReturn($resourceValidations[1]);

        $validation->expects($this->exactly(6))->method('add')->withConsecutive(
            [$privilegeValidations[0], "event 'a': "], [$resourceValidations[0], "event 'a': "], [$storedValidations[0], "event 'a': "], 
            [$privilegeValidations[1], "event 'b': "], [$resourceValidations[1], "event 'b': "], [$storedValidations[1], "event 'b': "]
        );
        
        $pipeline = Pipeline::with($events);               
        $ret = i\function_call($step, $pipeline, $validation, $newEvents);

        $this->assertSame($pipeline, $ret);

        $result = $pipeline->toArray();
        $this->assertEquals($events, $result);
    }    

    /**
     * Test '__invoke' method, if there're a validation error before saving resource
     */
    public function testInvokeBeforeStoreFails()
    {
        $step = $this->getStep(['applyPrivilegeToResource', 'storeResource']);
        list($events, $resources, $privilegeValidations, $storedValidations, $resourceValidations) = $this->getItems();

        $newEvents = new ArrayObject($events);       
        $validation = $this->createMock(ValidationResult::class);

        $validation->expects($this->exactly(7))->method('failed')->willReturnOnConsecutiveCalls(
            false, false, false, false, false, true, true
        );
        $this->resourceFactory->expects($this->exactly(3))->method('extractFrom')->will($this->returnValueMap([
            [$events[0], $resources[0]],
            [$events[1], $resources[1]],
            [$events[2], $resources[2]]
        ]));

        $step->expects($this->exactly(3))->method('applyPrivilegeToResource')->will($this->returnValueMap([
            [$resources[0], $events[0], $privilegeValidations[0]],
            [$resources[1], $events[1], $privilegeValidations[1]],
            [$resources[2], $events[2], $privilegeValidations[2]]
        ]));

        $step->expects($this->exactly(2))->method('storeResource')->withConsecutive(
            [$resources[0], $newEvents], [$resources[1], $newEvents]
        )->willReturnOnConsecutiveCalls($storedValidations[0], $storedValidations[1]);

        $resources[0]->expects($this->once())->method('validate')->willReturn($resourceValidations[0]);
        $resources[1]->expects($this->once())->method('validate')->willReturn($resourceValidations[1]);
        $resources[2]->expects($this->once())->method('validate')->willReturn($resourceValidations[2]);

        $validation->expects($this->exactly(8))->method('add')->withConsecutive(
            [$privilegeValidations[0], "event 'a': "], [$resourceValidations[0], "event 'a': "], [$storedValidations[0], "event 'a': "], 
            [$privilegeValidations[1], "event 'b': "], [$resourceValidations[1], "event 'b': "], [$storedValidations[1], "event 'b': "],
            [$privilegeValidations[2], "event 'c': "], [$resourceValidations[2], "event 'c': "]
        );
        
        $pipeline = Pipeline::with($events);               
        $ret = i\function_call($step, $pipeline, $validation, $newEvents);

        $this->assertSame($pipeline, $ret);

        $result = $pipeline->toArray();
        $this->assertEquals($events, $result);
    }    

    /**
     * Test '__invoke' method, if there're a validation error on the start of very first event
     */
    public function testInvokeFailOnStart()
    {
        $step = $this->getStep(['applyPrivilegeToResource', 'storeResource']);

        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events[0]->hash = 'a';
        $events[1]->hash = 'b';

        $newEvents = new ArrayObject($events);       

        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->exactly(2))->method('failed')->willReturn(true);

        $this->resourceFactory->expects($this->never())->method('extractFrom');
        $step->expects($this->never())->method('applyPrivilegeToResource');
        $step->expects($this->never())->method('storeResource');
        $validation->expects($this->never())->method('add');
        
        $pipeline = Pipeline::with($events);               
        $ret = i\function_call($step, $pipeline, $validation, $newEvents);

        $this->assertSame($pipeline, $ret);

        $result = $pipeline->toArray();
        $this->assertEquals($events, $result);
    }    

    /**
     * Provide data for testing 'applyPrivilegeToResource' method, if chain is empty
     *
     * @return array
     */
    public function applyPrivilegeToResourceProvider()
    {
        return [
            [$this->createMock(Identity::class), null],
            [$this->createMock(ResourceInterface::class), "initial resource must be an identity"]   
        ];
    }

    /**
     * Test 'applyPrivilegeToResource' method, if chain is empty
     *
     * @dataProvider applyPrivilegeToResourceProvider
     */
    public function testApplyPrivilegeToResourceEmptyChain(ResourceInterface $resource, ?string $error)
    {
        $step = $this->getStep();
        $event = $this->createMock(Event::class);

        $this->chain->expects($this->once())->method('hasEvents')->willReturn(false);

        $result = $step->applyPrivilegeToResource($resource, $event);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $error ? 
            $this->assertEquals([$error], $result->getErrors()) :
            $this->assertTrue($result->succeeded());
    }    

    /**
     * Test 'applyPrivilegeToResource' method, if privileges are empty
     */
    public function testApplyPrivilegeToResourceNoPrivileges()
    {
        $privileges = [];

        $step = $this->getStep();
        $event = $this->createMock(Event::class);
        $event->signkey = 'a';

        $resource = $this->createMock(ResourceInterface::class);

        $this->chain->identities = $this->createMock(IdentitySet::class);
        $identitiesFiltered = $this->createMock(IdentitySet::class);

        $this->chain->expects($this->once())->method('hasEvents')->willReturn(true);
        $this->chain->identities->expects($this->once())->method('filterOnSignkey')->with($event->signkey)->willReturn($identitiesFiltered);
        $identitiesFiltered->expects($this->once())->method('getPrivileges')->with($resource)->willReturn($privileges);

        $result = $step->applyPrivilegeToResource($resource, $event);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertEquals(["no privileges for event"], $result->getErrors());
    }    

    /**
     * Test 'applyPrivilegeToResource' method, if there're no errors
     */
    public function testApplyPrivilegeToResourceSuccess()
    {
        $privileges = [$this->createMock(Privilege::class)];
        $consolidatedPrivilege = $this->createMock(Privilege::class);

        $step = $this->getStep(['consolidatedPrivilege']);
        $event = $this->createMock(Event::class);
        $event->signkey = 'a';

        $resource = $this->createMock(ResourceInterface::class);

        $identity1 = $this->createMock(Identity::class);
        $identity2 = $this->createMock(Identity::class);
        $identitiesFiltered = $this->createPartialMock(IdentitySet::class, ['getPrivileges']);
        $this->setPrivateProperty($identitiesFiltered, 'entities', [$identity1, $identity2]);

        $this->chain->identities = $this->createMock(IdentitySet::class);;

        $this->chain->expects($this->once())->method('hasEvents')->willReturn(true);
        $this->chain->identities->expects($this->once())->method('filterOnSignkey')->with($event->signkey)->willReturn($identitiesFiltered);
        $identitiesFiltered->expects($this->once())->method('getPrivileges')->with($resource)->willReturn($privileges);
        $step->expects($this->once())->method('consolidatedPrivilege')->with($resource, $privileges)->willReturn($consolidatedPrivilege);
        $resource->expects($this->once())->method('applyPrivilege')->with($consolidatedPrivilege);

        $result = $step->applyPrivilegeToResource($resource, $event);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->succeeded());
    }

    /**
     * Provide data for testing 'storeResource' method, if error is produced
     *
     * @return array
     */
    public function storeResourceProvider()
    {
        $resource1 = new class() implements ResourceInterface, Identifiable {
            use ResourceBase;

            public function getId() {
                return 'foo_id';
            }
        };

        $resource2 = $this->createMock(ResourceInterface::class);
        $exception1 = $this->createMock(ClientException::class);
        $exception2 = $this->createMock(RequestException::class);

        $this->setPrivateProperty($exception1, 'message', 'Some client error');

        return [
            [$resource1, $exception1, 'Failed to store ResourceInterface foo_id: Some client error'],
            [$resource1, $exception2, 'Failed to store ResourceInterface foo_id: Server error'],
            [$resource2, $exception1, 'Failed to store ResourceInterface: Some client error'],
            [$resource2, $exception2, 'Failed to store ResourceInterface: Server error'],
        ];
    }

    /**
     * Test 'storeResource' method, if error is produced
     *
     * @dataProvider storeResourceProvider
     */
    public function testStoreResourceFail($resource, $exception, $expectedError)
    {
        $step = $this->getStep();

        $this->resourceStorage->expects($this->once())->method('store')
            ->with($resource)->will($this->returnCallback(function() use ($exception) {
                throw $exception;
            }));

        $newEvents = new ArrayObject([]);
        $result = $this->callPrivateMethod($step, 'storeResource', [$resource, $newEvents]);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertEquals([$expectedError], $result->getErrors());
    }    

    /**
     * Test 'storeResource' method, if error is not produced
     */
    public function testStoreResourceSuccess()
    {
        $step = $this->getStep();
        $newEvents = new ArrayObject([]);
        $addedEvents = $this->createMock(EventChain::class);

        $resource = $this->createMock(ResourceInterface::class);
        $this->resourceStorage->expects($this->once())->method('store')->with($resource, $this->chain)
            ->willReturn($addedEvents);
        $this->chain->expects($this->once())->method('registerResource')->with($resource);

        $result = $this->callPrivateMethod($step, 'storeResource', [$resource, $newEvents]);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->succeeded());
    }

    /**
     * Test '__invoke' method, if there was error while extracting resource from event
     */
    public function testInvokeExtractError()
    {
        $step = $this->getStep(['applyPrivilegeToResource', 'storeResource']);
        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];
        $events[1]->hash = 'a';

        $newEvents = new ArrayObject($events);

        $pipe = Pipeline::with($events);
        $resource = $this->createMock(\ExternalResource::class);

        $this->resourceFactory->expects($this->exactly(2))->method('extractFrom')
            ->withConsecutive([$events[0]], [$events[1]])
            ->will($this->returnCallback(function($event) use ($resource) {
                static $count = 0;
                $count++;

                if ($count < 2) {
                    return $resource;
                }

                throw new \UnexpectedValueException("error on event $count");
            }));

        $step->expects($this->once())->method('applyPrivilegeToResource')
            ->with($this->identicalTo($resource), $this->identicalTo($events[0]))
            ->willReturn(ValidationResult::success());

        $resource->expects($this->once())->method('validate')->willReturn(ValidationResult::success());
        $step->expects($this->once())->method('storeResource')
            ->with($this->identicalTo($resource), $this->identicalTo($newEvents))
            ->willReturn(ValidationResult::success());

        $validation = new ValidationResult();

        $result = $step($pipe, $validation, $newEvents);
        $result->walk();

        $errors = $validation->getErrors();
        $expected = ["event 'a': failed to extract resource: error on event 2"];

        $this->assertEquals($expected, $errors);
    }

    /**
     * Get step mock for testing
     *
     * @param array $methods
     * @return StoreResource
     */
    protected function getStep(array $methods = []): StoreResource
    {
        $step = $this->createPartialMock(StoreResource::class, $methods);

        $this->setPrivateProperty($step, 'chain', $this->chain);
        $this->setPrivateProperty($step, 'resourceFactory', $this->resourceFactory);
        $this->setPrivateProperty($step, 'resourceStorage', $this->resourceStorage);

        return $step;
    }

    /**
     * Get events for tests
     *
     * @return array
     */
    protected function getItems(): array
    {        
        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events[0]->hash = 'a';
        $events[1]->hash = 'b';
        $events[2]->hash = 'c';
        $events[3]->hash = 'd';

        $resources = [
            $this->createMock(ResourceInterface::class),
            $this->createMock(ResourceInterface::class),
            $this->createMock(ResourceInterface::class),
            $this->createMock(ResourceInterface::class)
        ];

        $privilegeValidations = [
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class)
        ];

        $storedValidations = [
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class)
        ];

        $resourceValidations = [
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class)
        ];

        return [$events, $resources, $privilegeValidations, $storedValidations, $resourceValidations];
    }
}
