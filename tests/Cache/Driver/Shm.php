<?php
namespace FMUPTests\Cache\Driver;

use FMUP\Cache\Driver;

/**
 * Created by PhpStorm.
 * User: jmoulin
 * Date: 19/10/2015
 * Time: 10:17
 */
class ShmTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $cache = $this->getMockBuilder(Driver\Shm::class)->setMethods(array('isAvailable'))->getMock();
        $this->assertInstanceOf(\FMUP\Cache\CacheInterface::class, $cache, 'Instance of ' . \FMUP\Cache\CacheInterface::class);
        $this->assertInstanceOf(\FMUP\Cache\Driver\Shm::class, $cache, 'Instance of ' . \FMUP\Cache\Driver\Shm::class);
        $cache2 = new \FMUP\Cache\Driver\Shm(array(''));
        $this->assertNotSame($cache2, $cache, 'New cache instance must not be same');
        $this->assertNotEquals($cache2, $cache, 'New cache instance must not be equal');
        $cache->method('isAvailable')->willReturn(true);
        return $cache;
    }

    public function testSet()
    {
        $mockShm = new \stdClass;
        $cache = $this->getMockBuilder(Driver\Shm::class)
            ->setMethods(array('isAvailable', 'shmPutVar', 'shmAttach'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('shmAttach')->willReturn($mockShm);
        $cache->method('shmPutVar')->willReturn(true);
        $test = array(
            array('test', 'test'),
            array('test', 'bob'),
            array('bob', 'bob'),
            array('bob', 'test'),
            array('bob', 1),
            array('bob', '1'),
            array('1', '1'),
            array('1', '2'),
            array('1', new \stdClass()),
            array('1', $this->getMockBuilder('\stdClass')->getMock()),
        );
        foreach ($test as $case) {
            $cache->set($case[0], $case[1]);
        }
        return $cache;
    }

    public function testGet()
    {
        $mockShm = new \stdClass;
        $cache = $this->getMockBuilder(Driver\Shm::class)
            ->setMethods(array('isAvailable', 'shmGetVar', 'shmAttach', 'shmHasVar'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('shmAttach')->willReturn($mockShm);
        $cache->method('shmHasVar')->willReturnOnConsecutiveCalls(true, true, true, true, true, true, true, true, false);
        $cache->method('shmGetVar')->willReturnOnConsecutiveCalls('test', 'bob', 'bob', 'test', 1, '1', '1', '2', null);
        $test = array(
            array('test', 'test'),
            array('test', 'bob'),
            array('bob', 'bob'),
            array('bob', 'test'),
            array('bob', 1),
            array('bob', '1'),
            array('1', '1'),
            array('1', '2'),
        );
        foreach ($test as $case) {
            $this->assertEquals($cache->get($case[0]), $case[1]);
        }
        return $cache;
    }

    public function testHas()
    {
        $mockShm = new \stdClass;
        $cache = $this->getMockBuilder(Driver\Shm::class)
            ->setMethods(array('isAvailable', 'shmAttach', 'shmHasVar'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('shmAttach')->willReturn($mockShm);
        $cache->method('shmHasVar')->willReturnOnConsecutiveCalls(true, true, true, true, true, true, true, true, false, false);
        $test = array(
            array('test', true),
            array('bob', true),
            array('1', true),
            array(1, true),
            array('notexists', false),
        );
        foreach ($test as $case) {
            $this->assertSame($case[1], $cache->has($case[0]), 'Test existence seems wrong');
            $this->assertTrue(is_bool($cache->has($case[0])), 'Return should be boolean');
        }
    }

    public function testRemove()
    {
        $mockShm = new \stdClass;
        $cache = $this->getMockBuilder(Driver\Shm::class)
            ->setMethods(array('isAvailable', 'shmRemoveVar', 'shmAttach', 'shmHasVar'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('shmAttach')->willReturn($mockShm);
        $cache->expects($this->once())->method('shmRemoveVar')->willReturn($cache);
        $cache->method('shmHasVar')->willReturnOnConsecutiveCalls(true, true, false);

        /* @var Driver\Shm $cache */
        $this->assertTrue($cache->has('test'), 'Test should exist');
        $return = $cache->remove('test');
        $this->assertSame($cache, $return, 'Set settings must return its instance');
        $this->assertFalse($cache->has('test'), 'Test should\'nt exist');
    }

    public function testHasWhenShmNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Shm::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('SHM is not available');
        /** @var $cache Driver\Shm */
        $cache->has('bob');
    }

    public function testGetWhenShmNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Shm::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('SHM is not available');
        /** @var $cache Driver\Shm */
        $cache->get('bob');
    }

    public function testSetWhenShmNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Shm::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('SHM is not available');
        /** @var $cache Driver\Shm */
        $cache->set('bob', 'bob');
    }

    public function testRemoveWhenShmNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Shm::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('SHM is not available');
        /** @var $cache Driver\Shm */
        $cache->remove('bob');
    }

    /**
     * @depends testConstruct
     * @param Driver\Shm $cache
     */
    public function testSetGetSettings(Driver\Shm $cache)
    {
        $testValue = 'testValue';
        $testKey = 'testKey';
        $this->assertSame($cache, $cache->setSetting($testKey, $testValue));
        $this->assertSame($testValue, $cache->getSetting($testKey));
        $this->assertNull($cache->getSetting('nonExistingKey'));
    }

    public function testGetShmWhenShmNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Shm::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('SHM is not available');
        /** @var $cache Driver\Shm */
        $reflectionMethod = new \ReflectionMethod(Driver\Shm::class, 'getShm');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($cache);
    }

    public function testRemoveWhenShmRemoveFails()
    {
        $cache = $this->getMockBuilder(Driver\Shm::class)
            ->setMethods(array('isAvailable', 'shmRemoveVar', 'shmHasVar', 'shmAttach'))
            ->getMock();
        $cache->method('shmAttach')->willReturn(new \stdClass());
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('shmHasVar')->willReturn(true);
        $cache->method('shmRemoveVar')->willReturn(false);
        /** @var $cache Driver\Shm */
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Unable to delete key from cache Shm');
        $cache->remove('test');
    }

    public function testSetWhenShmPutFails()
    {
        $cache = $this->getMockBuilder(Driver\Shm::class)
            ->setMethods(array('isAvailable', 'shmPutVar', 'shmAttach'))
            ->getMock();
        $cache->method('shmAttach')->willReturn(new \stdClass());
        $cache->method('isAvailable')->willReturn(true);
        /** @var $cache Driver\Shm */
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Unable to define key into cache Shm');
        $cache->set('test', 'test');
    }

    public function testIsAvailable()
    {
        $cache = new Driver\Shm;
        $this->assertTrue(is_bool($cache->isAvailable()));
    }

    public function testSetWhenShmHasTtl()
    {
        $cache = $this->getMockBuilder(Driver\Shm::class)
            ->setMethods(array('shmPutVar', 'isAvailable', 'shmAttach'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('shmPutVar')
            ->with($this->anything(), $this->equalTo(10), $this->equalTo('testValue'))
            ->willReturn(true);
        $cache->method('shmAttach')
            ->with($this->equalTo(1), $this->equalTo(20))
            ->willReturn(true);
        /** @var $cache Driver\Shm */
        $cache->setSetting(Driver\Shm::SETTING_SIZE, 20)->set(10, 'testValue');
    }
}
