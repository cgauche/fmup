<?php
/**
 * Cli.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Routing\Route;

class SapiMock extends \FMUP\Sapi
{
    public function __construct()
    {
    }
}

class NotValidController
{
}

class ValidController extends \FMUP\Controller
{
}

class CliTest extends \PHPUnit_Framework_TestCase
{
    public function testCanHandle()
    {
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('has', 'get'))->getMock();
        $request->method('has')->will($this->onConsecutiveCalls(false, true, false, true, false, true, false, true));
        $request->method('get')->will($this->onConsecutiveCalls('test', 'test/test'));
        $sapi = $this->getMockBuilder(SapiMock::class)->setMethods(array('get'))->getMock();
        $sapi->method('get')->will(
            $this->onConsecutiveCalls(
                SapiMock::CGI,
                SapiMock::CLI,
                SapiMock::CGI,
                SapiMock::CLI,
                SapiMock::CGI,
                SapiMock::CLI,
                SapiMock::CGI,
                SapiMock::CLI
            )
        );
        $cliPlugin = $this->getMockBuilder(\FMUP\Routing\Route\Cli::class)
            ->setMethods(array('getSapi', 'getRequest'))
            ->getMock();
        $cliPlugin->method('getSapi')->willReturn($sapi);
        $cliPlugin->method('getRequest')->willReturn($request);
        /** @var $cliPlugin \FMUP\Routing\Route\Cli */
        $this->assertFalse($cliPlugin->canHandle());
        $this->assertFalse($cliPlugin->canHandle());
        $this->assertFalse($cliPlugin->canHandle());
        $this->assertFalse($cliPlugin->canHandle());
        $this->assertFalse($cliPlugin->canHandle());
        $this->assertFalse($cliPlugin->canHandle());
        $this->assertFalse($cliPlugin->canHandle());
        $this->assertTrue($cliPlugin->canHandle());
    }

    public function testHandleGetAction()
    {
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)
            ->setMethods(array('has', 'get'))
            ->getMock();
        $request->method('get')->will($this->onConsecutiveCalls('/test', 'test/test2'));
        $cliPlugin = $this->getMockBuilder(\FMUP\Routing\Route\Cli::class)->setMethods(array('getRequest'))->getMock();
        $cliPlugin->method('getRequest')->willReturn($request);
        /** @var $cliPlugin \FMUP\Routing\Route\Cli */
        $this->assertNull($cliPlugin->getAction());
        $cliPlugin->handle();
        $this->assertSame('test', $cliPlugin->getAction());
        $cliPlugin->handle();
        $this->assertSame('test2', $cliPlugin->getAction());
    }

    public function testHandleGetControllerNameFailOnNonExisting()
    {
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('has', 'get'))->getMock();
        $request->method('get')->willReturn('controller/test');
        $cliPlugin = $this->getMockBuilder(\FMUP\Routing\Route\Cli::class)->setMethods(array('getRequest'))->getMock();
        $cliPlugin->method('getRequest')->willReturn($request);
        /** @var $cliPlugin \FMUP\Routing\Route\Cli */
        $this->expectException(\FMUP\Exception\Status\NotFound::class);
        $this->expectExceptionMessage('Controller controller does not exist');
        $this->expectExceptionCode(\FMUP\Routing\Route\Cli::ERROR_NOT_FOUND);
        $cliPlugin->handle();
        $cliPlugin->getControllerName();
    }

    public function testHandleGetControllerNameFailOnLogicError()
    {
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('has', 'get'))->getMock();
        $request->method('get')->willReturn('\FMUPTests\Routing\Route\NotValidController/test');
        $cliPlugin = $this->getMockBuilder(\FMUP\Routing\Route\Cli::class)->setMethods(array('getRequest'))->getMock();
        $cliPlugin->method('getRequest')->willReturn($request);
        /** @var $cliPlugin \FMUP\Routing\Route\Cli */
        $this->expectException(\FMUP\Exception\Status\NotFound::class);
        $this->expectExceptionMessage('Controller \FMUPTests\Routing\Route\NotValidController does not exist');
        $this->expectExceptionCode(\FMUP\Routing\Route\Cli::ERROR_LOGIC);
        $cliPlugin->handle();
        $cliPlugin->getControllerName();
    }

    public function testHandleGetControllerNameSuccess()
    {
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('has', 'get'))->getMock();
        $request->method('get')->willReturn('\FMUPTests\Routing\Route\ValidController/test');
        $cliPlugin = $this->getMockBuilder(\FMUP\Routing\Route\Cli::class)->setMethods(array('getRequest'))->getMock();
        $cliPlugin->method('getRequest')->willReturn($request);
        /** @var $cliPlugin \FMUP\Routing\Route\Cli */
        $cliPlugin->handle();
        $this->assertSame('\FMUPTests\Routing\Route\ValidController', $cliPlugin->getControllerName());
    }
}
