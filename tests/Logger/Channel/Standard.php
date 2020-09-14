<?php
/**
 * Standard.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Logger\Channel;

class SapiMockChannelStandard extends \FMUP\Sapi
{
    public function __construct()
    {
    }
}

class EnvironmentMockChannelStandard extends \FMUP\Environment
{
    public function __construct()
    {
    }
}

class StandardTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $environment = $this->getMockBuilder(EnvironmentMockChannelStandard::class)->setMethods(array('get'))->getMock();
        $environment->method('get')->willReturnOnConsecutiveCalls(EnvironmentMockChannelStandard::DEV, EnvironmentMockChannelStandard::PROD);
        $sapi = $this->getMockBuilder(SapiMockChannelStandard::class)->setMethods(array('get'))->getMock();
        $sapi->method('get')->willReturnOnConsecutiveCalls(SapiMockChannelStandard::CLI, SapiMockChannelStandard::CGI);
        $monologChannel = $this->getMockBuilder(\Monolog\Logger::class)
            ->setMethods(array('pushHandler'))
            ->setConstructorArgs(array('Mock'))
            ->getMock();
        $monologChannel->method('pushHandler')->willReturn($monologChannel);
        $channel = $this->getMockBuilder(\FMUP\Logger\Channel\Standard::class)
            ->setMethods(array('getLogger', 'headerSent', 'getSapi', 'getEnvironment'))
            ->getMock();
        $channel->method('getLogger')->willReturn($monologChannel);
        $channel->method('headerSent')->willReturn(false);
        $channel->method('getSapi')->willReturn($sapi);
        $channel->method('getEnvironment')->willReturn($environment);
        /** @var $channel \FMUP\Logger\Channel\Standard */
        $this->assertInstanceOf(\FMUP\Logger\Channel::class, $channel);
        $this->assertSame($channel, $channel->configure());
        $_SERVER['HTTP_USER_AGENT'] = 'Castelis';
        $this->assertSame($channel, $channel->configure());
    }
}
