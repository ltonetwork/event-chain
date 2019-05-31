<?php

namespace Meta;

use Jasny\DB\EntitySet;

/**
 * @covers Meta\AnnotationsFactory
 */
class AnnotationsFactoryTest extends \Codeception\Test\Unit
{
    use \Jasny\TestHelper;

    /**
     * Provide data for testing 'normalizeVar' method
     *
     * @return array
     */
    public function normalizeVarProvider()
    {
        return [
            ['DateTime', 'DateTime'],
            ['\\DateTime', 'DateTime'],
            ['DateTime some var info', 'DateTime'],
            ['DateTime|Foo', 'DateTime|foo\\bar\\Foo'],
            ['int|DateTime|Foo', 'int|DateTime|foo\\bar\\Foo'],
        ];
    }

    /**
     * Test 'normalizeVar' method
     *
     * @dataProvider normalizeVarProvider
     */
    public function testNormalizeVar($var, $expected)
    {
        $reflClass = $this->createMock(\ReflectionClass::class);
        $reflProp = $this->createMock(\ReflectionProperty::class);

        $reflProp->expects($this->any())->method('getDeclaringClass')->willReturn($reflClass);
        $reflClass->expects($this->any())->method('getNamespaceName')->willReturn('foo\\bar');

        $factory = $this->createPartialMock(AnnotationsFactory::class, []);

        $result = $this->callPrivateMethod($factory, 'normalizeVar', [$reflProp, $var]);

        $this->assertSame($expected, $result);
    }

    /**
     * Test 'normalizeVar' method, if wrong reflection is provided
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /Unsupported Reflector class: Mock_ReflectionClass_\w+/
     */
    public function testNormalizeVarException()
    {
        $refl = $this->createMock(\ReflectionClass::class);

        $factory = $this->createPartialMock(AnnotationsFactory::class, []);

        $this->callPrivateMethod($factory, 'normalizeVar', [$refl, 'foo']);
    }
}
