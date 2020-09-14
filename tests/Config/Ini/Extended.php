<?php
/**
 * Extended.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Config\Ini;

class ExtendedTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigFailsWhenFileDontExist()
    {
        $file = '/not/existing/path';
        $this->expectException(\FMUP\Config\Exception::class);
        $this->expectExceptionMessage('File does not exist');
        $config = new \FMUP\Config\Ini\Extended($file);
        $config->getConfig();
    }

    public function testSetGetConfig()
    {
        $file = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '.files', 'testphp.ini'));
        $config = new \FMUP\Config\Ini\Extended($file);
        $oldConfig = $config->getConfig();
        $this->assertInstanceOf(\FMUP\Config\Ini\Extended\ZendConfig\Ini::class, $oldConfig);

        $zendConfig = $this->getMockBuilder(\FMUP\Config\Ini\Extended\ZendConfig\Ini::class)
            ->setMethods(null)
            ->setConstructorArgs(array($file))
            ->getMock();
        /** @var $zendConfig \FMUP\Config\Ini\Extended\ZendConfig\Ini */
        $this->assertSame($config, $config->setConfig($zendConfig));
        $this->assertSame($zendConfig, $config->getConfig());
        $this->assertNotSame($oldConfig, $config->getConfig());
    }

    public function testHasSetGet()
    {
        $file = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '.files', 'testphp.ini'));
        $config = new \FMUP\Config\Ini\Extended($file);
        $this->assertFalse($config->has('test'));
        $this->assertNull($config->get('test'));
        $this->assertFalse($config->has('engine'));

        $config = new \FMUP\Config\Ini\Extended($file, 'PHP');
        $this->assertFalse($config->has('test'));
        $this->assertNull($config->get('test'));
        $this->assertTrue(is_array($config->get()));
        $this->assertTrue($config->has('engine'));
        $this->assertNotNull($config->get('engine'));
        $this->assertSame($config, $config->set('test', 1));
        $this->assertTrue($config->has('test'));
        $this->assertSame(1, $config->get('test'));
    }
}
