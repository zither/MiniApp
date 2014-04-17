<?php

require_once 'Bootstrap.php';
\MiniApp\MiniApp::autoloadRegister();

class RouteTest extends PHPUnit_Framework_TestCase
{
    public function testDispatch()
    {
        $this->expectOutputString('Hello,MiniApp!');
        $app = new \MiniApp\MiniApp(array('controller_path' => __DIR__ . '/data/'));
        $app->route('/testcontroller/(MiniApp)', array('testcontroller', 'home'));
        $app->mock(array('PATH_INFO' => '/testcontroller/MiniApp'));
        $app->route->dispatch();
    }

    public function testDispatchWithNoAction()
    {
        $this->expectOutputString('Hello,MiniApp!');
        $app = new \MiniApp\MiniApp(array('controller_path' => __DIR__ . '/data/'));
        $app->mock(array('PATH_INFO' => '/testcontroller'));
        $app->route->dispatch();
    }

    /**
     * @runInSeparateProcess 
     */
    public function testNotFound()
    {
        $this->expectOutputString('404 Not Found');
        $route = new \MiniApp\Route();
        $route->notFound();
    }

    /**
     * @runInSeparateProcess 
     */
    public function testResponseWithWrongController()
    {
        $this->expectOutputString('404 Not Found');
        $app = new \MiniApp\MiniApp();
        $app->mock(array('PATH_INFO' => '/foo/bar'));
        $app->run();
    }


    public function testResponseWithStopException()
    {
        $app = new \MiniApp\MiniApp(array('controller_path' => __DIR__ . '/data/'));
        $app->mock(array('PATH_INFO' => '/testcontroller/stop'));
        $this->assertFalse($app->route->dispatch());    
    }
}
